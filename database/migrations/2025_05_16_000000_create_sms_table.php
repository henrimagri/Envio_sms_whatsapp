<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmsTable extends Migration
{
    public function up()
    {
        Schema::create('sms', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 45)->nullable()->comment('Numero');
            $table->string('mensagem', 160)->nullable()->comment('Mensagem');
            $table->integer('status_sms')->default(0)->comment('Status do envio do SMS');
            $table->dateTime('data_envio')->nullable()->comment('Data do Envio');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sms');
    }
}