<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Envios de SMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Envios de SMS</h1>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Data de Envio</th>
                    <th>Status</th>
                    <th>Descrição</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($smsList as $sms)
                    <tr>
                        <td>{{ $sms->numero }}</td>
                        <td>{{ \Carbon\Carbon::parse($sms->data_envio)->format('d/m/Y H:i:s') }}</td>
                        <td>{{ $sms->status_sms ? 'Sucesso' : 'Falha' }}</td>
                        <td>{{ $sms->api_description }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">Nenhum SMS enviado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="d-flex justify-content-center">
            {{ $smsList->onEachSide(1)->links('pagination::bootstrap-4') }}
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('sms.index') }}" class="btn btn-primary">Voltar</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>