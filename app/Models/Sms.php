<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sms extends Model
{
    use HasFactory;

    protected $table = 'sms';

    protected $fillable = [
        'numero',
        'mensagem',
        'tipo_de_mensagem', // adicionado para permitir gravação
        'status_sms',
        'processado',
        'data_envio',
        'codigo_api',
        'valor_pago',
        'resposta_api',
        'api_description',
        'api_description_name',
        'api_group_id',
    ];
}