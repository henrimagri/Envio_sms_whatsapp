<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\WhatsAppController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/sms', [SmsController::class, 'index'])->name('sms.index');
Route::post('/sms/send', [SmsController::class, 'send'])->name('sms.send');
Route::get('/sms/show', [SmsController::class, 'show'])->name('sms.show');
Route::post('/whatsapp/send', [WhatsAppController::class, 'sendWhatsApp'])->name('whatsapp.send');

// Rota para envio de SMS via AJAX
Route::post('/sms/send-ajax', [App\Http\Controllers\SmsController::class, 'sendSmsAjax'])->name('sms.send.ajax');

// Rota para listar mensagens do WhatsApp com paginação
Route::get('/whatsapp', [WhatsAppController::class, 'index'])->name('whatsapp.index');

// Rota para exibir o formulário de envio de WhatsApp
Route::get('/whatsapp/create', function () {
    return view('whatsapp.create');
});

// Rota para envio WhatsApp via AJAX
Route::post('/whatsapp/send-ajax', [App\Http\Controllers\WhatsAppController::class, 'sendWhatsAppAjax'])->name('whatsapp.send.ajax');

Route::get('/test-whatsapp-api', function () {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://38lz41.api.infobip.com/whatsapp/1/message/template');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'messages' => [
            [
                'from' => '447860099299', // Certifique-se de que este número é válido e autorizado pela API Infobip
                'to' => '5511966119109', // Substitua por um número válido
                'messageId' => uniqid(),
                'content' => [
                    'templateName' => 'test_whatsapp_template_en',
                    'templateData' => [
                        'body' => [
                            'placeholders' => ['Teste']
                        ]
                    ],
                    'language' => [
                        'policy' => 'deterministic',
                        'code' => 'en'
                    ]
                ]
            ]
        ]
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: App 3105b3efd62472f82951e93b8fa9b20c-21ba3fa4-30e5-4933-97cb-d29c9697b39f',
        'Content-Type: application/json',
        'Accept: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        return response()->json(['error' => curl_error($ch)], 500);
    }

    curl_close($ch);

    return response()->json([
        'http_code' => $httpCode,
        'response' => json_decode($response, true)
    ]);
});
