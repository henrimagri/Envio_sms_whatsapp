<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\Sms;
use Carbon\Carbon;

class SmsController extends Controller
{
    public function index()
    {
        $envios = Sms::orderByRaw('COALESCE(data_envio, created_at) DESC')->paginate(5); // Ordena por data real de envio
        return view('sms.index', compact('envios'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'numero' => 'required|string|max:45',
            'mensagem' => 'required|string|max:160',
        ]);

        $sms = new Sms();
        $sms->numero = $request->numero;
        $sms->mensagem = $request->mensagem;

        // Garante o timezone correto e formata a data/hora
        $dataEnvio = Carbon::now('America/Sao_Paulo');
        $sms->data_envio = $dataEnvio->format('Y-m-d H:i:s');
        $sms->tipo_de_mensagem = 'SMS';

        $numeroFormatado = '+55' . ltrim($request->numero, '0'); // Adiciona o código do país (Brasil)

        $client = new Client();

        try {
            $response = $client->post('https://38lz41.api.infobip.com/sms/2/text/advanced', [
                'headers' => [
                    'Authorization' => 'App 3105b3efd62472f82951e93b8fa9b20c-21ba3fa4-30e5-4933-97cb-d29c9697b39f',
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'messages' => [
                        [
                            'from' => 'InfoSMS', // Verifique se este remetente é válido
                            'destinations' => [['to' => $numeroFormatado]],
                            'text' => $request->mensagem
                        ]
                    ]
                ]
            ]);

            $sms->status_sms = 1; // Sucesso
            $sms->resposta_api = $response->getBody()->getContents();
            $responseBody = json_decode($sms->resposta_api, true);
            // Verifica se houve rejeição na resposta da API
            $isRejected = false;
            $rejectionDescription = '';
            if (isset($responseBody['messages'][0]['status']['groupName']) && $responseBody['messages'][0]['status']['groupName'] === 'REJECTED') {
                $isRejected = true;
                $rejectionDescription = $responseBody['messages'][0]['status']['description'] ?? 'Destino não registrado';
            }
            if ($isRejected) {
                $sms->status_sms = 0;
                $sms->api_description = 'Mensagem não entregue: ' . $rejectionDescription;
                $sms->save();
                return redirect()->route('sms.index')->with('status', 'Mensagem não entregue: ' . $rejectionDescription);
            }
            // Se o retorno da API for exatamente 'OK', grave o retorno real na api_description
            if (trim($sms->resposta_api) === 'OK') {
                $sms->api_description = $sms->resposta_api;
            } else {
                $sms->api_description = 'Mensagem enviada com sucesso';
            }
            $sms->save();

            return redirect()->route('sms.index')->with('status', 'SMS enviado com sucesso!');
        } catch (\Exception $e) {
            $sms->status_sms = 0; // Falha
            $sms->resposta_api = $e->getCode();
            $sms->api_description = $e->getMessage();
            $sms->save();

            return redirect()->route('sms.index')->with('status', 'Erro ao enviar SMS: ' . $e->getMessage());
        }
    }

    public function sendSmsAjax(Request $request)
    {
        $request->validate([
            'numero' => 'required|string|max:45',
            'mensagem' => 'required|string|max:160',
        ]);

        $sms = new Sms();
        $sms->numero = $request->numero;
        $sms->mensagem = $request->mensagem;
        $dataEnvio = Carbon::now('America/Sao_Paulo');
        $sms->data_envio = $dataEnvio->format('Y-m-d H:i:s');
        $sms->tipo_de_mensagem = 'SMS';

        $numeroFormatado = '+55' . ltrim($request->numero, '0');
        $client = new Client();
        $maxAttempts = 3;
        $attempt = 0;
        $entregue = false;
        $apiDescription = '';
        $responseBody = null;
        $tentativas = [];
        while ($attempt < $maxAttempts && !$entregue) {
            $tentativaMsg = 'Tentativa ' . ($attempt + 1) . ' de ' . $maxAttempts;
            try {
                $response = $client->post('https://38lz41.api.infobip.com/sms/2/text/advanced', [
                    'headers' => [
                        'Authorization' => 'App 3105b3efd62472f82951e93b8fa9b20c-21ba3fa4-30e5-4933-97cb-d29c9697b39f',
                        'Content-Type' => 'application/json'
                    ],
                    'json' => [
                        'messages' => [
                            [
                                'from' => 'InfoSMS',
                                'destinations' => [['to' => $numeroFormatado]],
                                'text' => $request->mensagem
                            ]
                        ]
                    ]
                ]);
                $sms->resposta_api = $response->getBody()->getContents();
                $responseBody = json_decode($sms->resposta_api, true);
                $status = $responseBody['messages'][0]['status'] ?? [];
                if ((isset($status['groupName']) && $status['groupName'] === 'REJECTED')) {
                    $apiDescription = 'Mensagem não entregue após 3 tentativas.';
                    $tentativas[] = [
                        'tentativa' => $tentativaMsg,
                        'status' => 'error',
                        'mensagem' => $attempt === $maxAttempts - 1 ? $apiDescription : ''
                    ];
                    $attempt++;
                    if ($attempt < $maxAttempts) {
                        sleep(1);
                    }
                } elseif ((isset($status['groupName']) && $status['groupName'] === 'DELIVERED')) {
                    $entregue = true;
                    $apiDescription = 'Mensagem enviada com sucesso';
                    $tentativas[] = [
                        'tentativa' => '',
                        'status' => 'success',
                        'mensagem' => $apiDescription
                    ];
                } else {
                    $apiDescription = 'Erro inesperado ao enviar SMS.';
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
        $sms->status_sms = $entregue ? 1 : 0;
        $sms->api_description = $apiDescription;
        $sms->save();
        return response()->json(['tentativas' => $tentativas, 'final' => $entregue ? 'success' : 'error']);
    }

    public function show()
    {
        $smsList = Sms::select('numero', 'data_envio', 'status_sms', 'api_description')
            ->orderByRaw('COALESCE(data_envio, created_at) DESC')
            ->paginate(5);

        return view('sms.show', compact('smsList'));
    }
}