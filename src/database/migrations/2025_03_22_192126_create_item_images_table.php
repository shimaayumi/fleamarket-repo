<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemImagesTable extends Migration
{
    public function up()
    {
        Schema::create('item_images', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade'); 
            $table->string('item_image'); 
            $table->timestamps(); 
        });
    }

    public function down()
    {
        Schema::dropIfExists('item_images');
    }
}