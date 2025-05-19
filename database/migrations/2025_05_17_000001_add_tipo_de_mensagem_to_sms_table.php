<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTipoDeMensagemToSmsTable extends Migration
{
    public function up()
    {
        Schema::table('sms', function (Blueprint $table) {
            $table->string('tipo_de_mensagem', 20)->nullable()->after('mensagem')->comment('Tipo de mensagem: SMS ou WhatsApp');
        });
    }

    public function down()
    {
        Schema::table('sms', function (Blueprint $table) {
            $table->dropColumn('tipo_de_mensagem');
        });
    }
}
