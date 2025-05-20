<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Infobip\Api\Configuration\BasicAuthConfiguration;
use Infobip\Api\Client\SendWhatsAppMessage;
use App\Models\Sms;

class WhatsAppController extends Controller
{
    public function sendWhatsApp(Request $request)
    {
        \Log::info('sendWhatsApp method called', ['request_data' => $request->all()]);

        try {
            \Log::info('Request data:', ['data' => $request->all()]);

            $request->validate([
                'numero' => 'required|string|max:45',
                'mensagem' => 'required|string|max:160',
            ]);

            \Log::info('Validation passed');

            $numeroFormatado = '+55' . ltrim($request->numero, '0');
            \Log::info('Formatted number:', ['numero' => $numeroFormatado]);

            $client = new Client();

            $fromNumber = '447860099299'; //  número é válido e autorizado pela API Infobip
            if (empty($fromNumber)) {
                \Log::error('The from number is null or invalid.');
                return response()->json(['error' => 'The from number is null or invalid.'], 400);
            }

            $payload = [
                'messages' => [
                    [
                        'from' => $fromNumber,
                        'to' => $numeroFormatado,
                        'messageId' => uniqid(),
                        'content' => [
                            'templateName' => 'test_whatsapp_template_en',
                            'templateData' => [
                                'body' => [
                                    'placeholders' => [$request->mensagem]
                                ]
                            ],
                            'language' => 'en' 
                        ]
                    ]
                ]
            ];

            \Log::info('Payload prepared:', ['payload' => $payload]);

            $maxAttempts = 3;
            $attempt = 0;
            $entregue = false;
            $apiDescription = '';
            $responseBody = null;
            while ($attempt < $maxAttempts && !$entregue) {
                $tentativaMsg = $attempt > 0 ? ' (Tentativa ' . ($attempt + 1) . ' de ' . $maxAttempts . ')' : '';
                $response = $client->post('https://38lz41.api.infobip.com/whatsapp/1/message/template', [
                    'headers' => [
                        'Authorization' => 'App 3105b3efd62472f82951e93b8fa9b20c-21ba3fa4-30e5-4933-97cb-d29c9697b39f',
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json'
                    ],
                    'json' => $payload
                ]);
                $responseBody = json_decode($response->getBody(), true);
                $status = $responseBody['messages'][0]['status'] ?? [];
                if ((isset($status['groupName']) && $status['groupName'] === 'PENDING') && (isset($status['description']) && $status['description'] === 'Message sent to next instance')) {
                    $entregue = true;
                    $apiDescription = 'Mensagem enviada com sucesso';
                } elseif ((isset($status['groupName']) && $status['groupName'] === 'REJECTED')) {
                    $apiDescription = 'Mensagem não entregue: ' . ($status['description'] ?? 'Destino não registrado') . $tentativaMsg;
                    $attempt++;
                    if ($attempt < $maxAttempts) {
                        sleep(2); // Aguarda 1 segundo antes de tentar novamente
                    }
                } else {
                    $apiDescription = json_encode($responseBody);
                    break;
                }
            }
            if ($entregue) {
                Sms::create([
                    'numero' => $numeroFormatado,
                    'mensagem' => $request->mensagem,
                    'tipo_de_mensagem' => 'WhatsApp',
                    'status' => 'Enviado',
                    'api_description' => $apiDescription,
                    'resposta_api' => json_encode($responseBody),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                return redirect()->route('sms.index')->with('status', 'Mensagem WhatsApp enviada com sucesso!');
            } else {
                Sms::create([
                    'numero' => $numeroFormatado,
                    'mensagem' => $request->mensagem,
                    'tipo_de_mensagem' => 'WhatsApp',
                    'status' => 'Erro',
                    'api_description' => $apiDescription,
                    'resposta_api' => json_encode($responseBody),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                return redirect()->route('sms.index')->with('status', $apiDescription);
            }
        } catch (\Exception $e) {
            \Log::error('Error in sendWhatsApp:', ['error' => $e->getMessage()]);
            return redirect()->route('sms.index')->with('status', 'Erro ao enviar mensagem WhatsApp: ' . $e->getMessage());
        }
    }

    public function sendWhatsAppAjax(Request $request)
    {
        $request->validate([
            'numero' => 'required|string|max:45',
            'mensagem' => 'required|string|max:160',
        ]);

        $numeroFormatado = '+55' . ltrim($request->numero, '0');
        $client = new Client();
        $fromNumber = '447860099299';
        $payload = [
            'messages' => [
                [
                    'from' => $fromNumber,
                    'to' => $numeroFormatado,
                    'messageId' => uniqid(),
                    'content' => [
                        'templateName' => 'test_whatsapp_template_en',
                        'templateData' => [
                            'body' => [
                                'placeholders' => [$request->mensagem]
                            ]
                        ],
                        'language' => 'en'
                    ]
                ]
            ]

        ];
        $maxAttempts = 3;
        $attempt = 0;
        $entregue = false;
        $apiDescription = '';
        $responseBody = null;
        $tentativas = [];
        while ($attempt < $maxAttempts && !$entregue) {
            $tentativaMsg = 'Tentativa ' . ($attempt + 1) . ' de ' . $maxAttempts;
            try {
                $response = $client->post('https://38lz41.api.infobip.com/whatsapp/1/message/template', [
                    'headers' => [
                        'Authorization' => 'App 3105b3efd62472f82951e93b8fa9b20c-21ba3fa4-30e5-4933-97cb-d29c9697b39f',
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json'
                    ],
                    'json' => $payload
                ]);
                $responseBody = json_decode($response->getBody(), true);
                $status = $responseBody['messages'][0]['status'] ?? [];
                if ((isset($status['groupName']) && $status['groupName'] === 'PENDING') && (isset($status['description']) && $status['description'] === 'Message sent to next instance')) {
                    $entregue = true;
                    $apiDescription = 'Mensagem enviada com sucesso';
                    $tentativas[] = [
                        'tentativa' => '', // Não mostra tentativa em caso de sucesso
                        'status' => 'success',
                        'mensagem' => $apiDescription
                    ];
                } elseif ((isset($status['groupName']) && $status['groupName'] === 'REJECTED')) {
                    $apiDescription = 'Mensagem não entregue: ' . ($status['description'] ?? 'Destino não registrado');
                    $tentativas[] = [
                        'tentativa' => $tentativaMsg,
                        'status' => 'error',
                        'mensagem' => $apiDescription
                    ];
                    $attempt++;
                    if ($attempt < $maxAttempts) {
                        sleep(1);
                    }
                } else {
                    $apiDescription = json_encode($responseBody);
                    $tentativas[] = [
                        'tentativa' => $tentativaMsg,
                        'status' => 'error',
                        'mensagem' => $apiDescription
                    ];
                    break;
                }
            } catch (\Exception $e) {
                $tentativas[] = [
                    'tentativa' => $tentativaMsg,
                    'status' => 'error',
                    'mensagem' => 'Erro ao enviar: ' . $e->getMessage()
                ];
                break;
            }
        }
        // Salva o resultado final
        Sms::create([
            'numero' => $numeroFormatado,
            'mensagem' => $request->mensagem,
            'tipo_de_mensagem' => 'WhatsApp',
            'status' => $entregue ? 'Enviado' : 'Erro',
            'api_description' => $apiDescription,
            'resposta_api' => json_encode($responseBody),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        return response()->json(['tentativas' => $tentativas, 'final' => $entregue ? 'success' : 'error']);
    }

    public function index()
    {
        $mensagens = Sms::orderByRaw('COALESCE(data_envio, created_at) DESC')->paginate(5); // Ordena por data real de envio
        return view('whatsapp.index', compact('mensagens'));
    }
}
