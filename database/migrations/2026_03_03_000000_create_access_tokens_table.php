<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccessTokensTable extends Migration
{
    public function up()
    {
        Schema::create('access_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token')->unique();
            $table->string('origin');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('access_tokens');
    }
}
