<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGatewaysTable extends Migration
{
    public function up()
    {
        Schema::create('gateways', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->boolean('available')->default(true);
            $table->string('api_key')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('gateways');
    }
}
