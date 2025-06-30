<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('user_id')->constrained('users'); 
            $table->string('profile_image')->default('default.png'); 
            $table->timestamps(); 
        });
    }

   

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropForeign(['user_id']); // 外部キー制約を削除
            $table->dropColumn('profile_image'); // profile_image カラムを削除
        });
        Schema::dropIfExists('profiles'); // profiles テーブルを削除
    }
}
