<?php

namespace App\Mail;

use App\Models\Purchase;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PurchaseCompleteMail extends Mailable
{
    use Queueable, SerializesModels;

    public $purchase;

    public function __construct(Purchase $purchase)
    {
        $this->purchase = $purchase;
    }

    public function build()
    {
        return $this->subject('取引が完了しました')
            ->view('emails.purchase_complete')
            ->with([
                'sellerName' => $this->purchase->item->user->name,
                'itemName' => $this->purchase->item->item_name,
                'purchaseId' => $this->purchase->id,
            ]);
    }
}
