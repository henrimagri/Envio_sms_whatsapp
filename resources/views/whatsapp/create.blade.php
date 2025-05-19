<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar WhatsApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>Enviar Mensagem WhatsApp</h1>
    <form action="{{ route('whatsapp.send') }}" method="POST" class="card p-4 shadow-sm mb-4">
        @csrf
        <div class="mb-3">
            <label for="numero" class="form-label">Número de Telefone:</label>
            <input type="text" id="numero" name="numero" class="form-control" required maxlength="11" placeholder="Digite o número sem o código do país">
        </div>
        <div class="mb-3">
            <label for="mensagem" class="form-label">Mensagem:</label>
            <textarea id="mensagem" name="mensagem" class="form-control" required maxlength="160" placeholder="Digite sua mensagem"></textarea>
            <div id="charCount" class="text-end mt-1">0/160 caracteres</div>
        </div>
        <button type="submit" class="btn btn-success me-2"><i class="bi bi-whatsapp"></i> Enviar WhatsApp</button>
        <a href="{{ route('whatsapp.index') }}" class="btn btn-secondary me-2"><i class="bi bi-list"></i> Visualizar Envios</a>
        <a href="{{ route('sms.index') }}" class="btn btn-outline-primary"><i class="bi bi-arrow-left"></i> Voltar para SMS</a>
    </form>
</div>
<script>
    // Contador de caracteres
    const mensagemInput = document.getElementById('mensagem');
    const charCount = document.getElementById('charCount');
    mensagemInput.addEventListener('input', () => {
        const length = mensagemInput.value.length;
        charCount.textContent = `${length}/160 caracteres`;
    });
    charCount.textContent = `${mensagemInput.value.length}/160 caracteres`;
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
