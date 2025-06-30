<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('user_id')->constrained('users'); 
            $table->string('item_name')->nullable();
            $table->string('brand_name')->nullable(); 
            $table->text('description'); 
            $table->unsignedInteger('price'); 
            $table->enum('status', ['良好', '目立った傷や汚れなし', 'やや傷や汚れあり', '状態が悪い']); 
            $table->boolean('sold_flag')->default(0); 
            $table->json('categories')->nullable(); // 複数カテゴリを格納するJSONカラム
            $table->timestamps(); 
        });
    }

    public function down()
    {
        Schema::dropIfExists('items');
    }
}
