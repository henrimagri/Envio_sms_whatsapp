<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensagens do WhatsApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>Mensagens do WhatsApp</h1>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Número</th>
                <th>Mensagem</th>
                <th>Tipo</th>
                <th>Status</th>
                <th>Data de Envio</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($mensagens as $mensagem)
            <tr>
                <td>{{ $mensagem->id }}</td>
                <td>{{ $mensagem->numero }}</td>
                <td>{{ $mensagem->mensagem }}</td>
                <td>{{ $mensagem->tipo_de_mensagem ?? '-' }}</td>
                <td>{{ $mensagem->status ?? $mensagem->status_sms }}</td>
                <td>{{ $mensagem->created_at }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="d-flex justify-content-center">
        {{ $mensagens->links() }}
    </div>
    <a href="{{ route('sms.index') }}" class="btn btn-secondary mt-3"><i class="bi bi-arrow-left"></i> Voltar para SMS</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>