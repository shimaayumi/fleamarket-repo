<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Purchase;
use Stripe\Stripe;
use App\Http\Requests\PurchaseRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;



use App\Mail\PurchaseCompleteMail;
use Illuminate\Support\Facades\Mail;


class PurchaseController extends Controller
{
    public function checkout(PurchaseRequest $request, $item_id)
    {
      
        $validated = $request->validated();
        $item = Item::findOrFail($item_id);
        $user = Auth::user();

        Stripe::setApiKey(env('STRIPE_SECRET'));

        DB::beginTransaction();

        $existing = Purchase::where('user_id', $user->id)
            ->where('item_id', $item->id)
            ->first();

        if ($existing) {
            return response()->json([
                'error' => 'すでにこの商品は購入手続き中です。',
            ], 400);
        }

        $addressId = $user->profile->address->id;
        $temporaryAddress = session('temporary_address');

        $purchase = Purchase::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'address_id' => $addressId,
            'payment_method' => $validated['payment_method'],
            'price' => $item->price,
            'status' => 'pending', // ← Stripe完了時に in_progress に更新
            'shipping_postal_code' => $temporaryAddress['postal_code'] ?? $user->profile->address->postal_code,
            'shipping_address' => $temporaryAddress['address'] ?? $user->profile->address->address,
            'shipping_building' => $temporaryAddress['building'] ?? $user->profile->address->building,
        ]);

        if ($validated['payment_method'] === 'credit_card') {
            // クレジットカード決済（Stripe Checkout）
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'jpy',
                        'product_data' => [
                            'name' => $item->item_name,
                            'description' => $item->description,
                        ],
                        'unit_amount' => $item->price,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('purchase.success') . '?session_id={CHECKOUT_SESSION_ID}&item_id=' . $item->id,
                'payment_intent_data' => [
                    'metadata' => [
                        'purchase_id' => $purchase->id,
                    ],
                ],
                'metadata' => [
                    'purchase_id' => $purchase->id,
                ],
            ]);

            $purchase->stripe_session_id = $session->id;
            $purchase->save();

            DB::commit();

            return response()->json(['url' => $session->url]);
        } elseif ($validated['payment_method'] === 'convenience_store') {
            // コンビニ決済（PaymentIntent）
            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => $item->price,
                'currency' => 'jpy',
                'payment_method_types' => ['konbini'],
                'metadata' => [
                    'purchase_id' => $purchase->id,
                ],
            ]);

            // ✅ ステータスを「in_progress」に（confirmed → in_progress）
            $purchase->status = 'in_progress';
            $purchase->save();

            // ✅ 商品をソールドにする
            $item->sold_flag = 1;
            $item->save();

            DB::commit();

            return response()->json([
                'payment_intent' => $paymentIntent,
                'payment_method' => 'convenience_store',
                'payment_intent_client_secret' => $paymentIntent->client_secret,
                'message' => 'コンビニ決済の支払い情報を作成し、商品を確保しました。',
            ]);
        }
    }




    public function success(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
        $session_id = $request->input('session_id');
        $item_id = $request->input('item_id');

        Log::info("Success method called. session_id: {$session_id}, item_id: {$item_id}");

        try {
            $session = \Stripe\Checkout\Session::retrieve($session_id);
            Log::info('Stripe Session payment_status: ' . $session->payment_status);

            if ($session->payment_status === 'paid') {
                $purchase = Purchase::where('stripe_session_id', $session_id)
                    ->where('item_id', $item_id)
                    ->first();

                if (!$purchase) {
                    Log::error('Purchase not found for session_id: ' . $session_id);
                    return redirect()->route('index')->with('error', '購入情報が見つかりません。');
                }

                DB::beginTransaction();

                // ✅ ステータスを「in_progress」に変更（取引中）
                $purchase->update(['status' => 'in_progress']);

                $item = Item::find($item_id);
                if ($item) {
                    $item->sold_flag = 1;
                    $item->save();
                }

                DB::commit();

                session()->forget('temporary_address');

                return redirect()->route('index')->with('message', '購入が完了しました。ありがとうございました！');
            } else {
                Log::warning('Payment not completed. payment_status: ' . $session->payment_status);
                return redirect()->route('index')->with('error', '決済が完了していません。');
            }
        } catch (\Exception $e) {
            Log::error('Error in success method: ' . $e->getMessage());
            return redirect()->route('index')->with('error', '購入完了処理中にエラーが発生しました。');
        }
    }



    // 購入失敗時の処理
    public function failed(Request $request)
    {

        $error = $request->session()->get('error', '購入処理中にエラーが発生しました。');
        return view('purchase', ['error' => $error]);
    }




    public function show($item_id)
    {
        try {
            $user = auth()->user();
            $item = Item::findOrFail($item_id);

            $purchase = Purchase::where('user_id', $user->id)
                ->where('item_id', $item_id)
                ->first();

            Log::debug('Item found:', ['item' => $item]);
            Log::debug('Purchase record:', ['purchase' => $purchase]);

            return view('purchase', compact('item', 'user', 'purchase'));
        } catch (\Exception $e) {
            Log::error('Error in purchase show method: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred.'], 500);
        }
    }


    public function cancel()
    {
        return view('index');
    }


    public function completePurchase($purchaseId)
    {
        $purchase = Purchase::findOrFail($purchaseId);
        $purchase->status = 'completed';
        $purchase->save();

        $seller = $purchase->item->user;
        $userEmail = $seller->email; // ★ここが必要！

        // 出品者に通知メールを送信
        Mail::to($userEmail)->send(new PurchaseCompleteMail($purchase));

        return redirect()->route('mypage')->with('success', '取引が完了し、出品者に通知メールを送信しました。');
    }
    
 }


