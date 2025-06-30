<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items');
            $table->foreignId('address_id')->constrained('addresses');
            $table->enum('payment_method', ['credit_card', 'convenience_store']); 
            $table->integer('price');
            $table->string('shipping_postal_code');
            $table->string('shipping_address');
            $table->string('shipping_building');
            $table->string('stripe_session_id')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'])->default('pending'); 
            
            $table->timestamps();
            $table->unique(['user_id', 'item_id']); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['item_id']);    // item_id の外部キーを削除
            $table->dropForeign(['address_id']); // address_id の外部キーを削除
            $table->dropForeign(['user_id']);    // user_id の外部キーを削除
        });

        Schema::dropIfExists('purchases');
    }
}
