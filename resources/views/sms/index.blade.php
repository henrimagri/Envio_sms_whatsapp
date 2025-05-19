<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar SMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        #charCount {
            font-size: 0.9em;
            color: gray;
        }
        .btn {
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .btn:hover {
            background-color: #87ceeb; /* Azul mais claro */
            color: #fff; /* Texto branco */
        }
        .btn-primary:hover {
            background-color: #0056b3; /* Azul mais escuro */
            color: #fff; /* Texto branco */
        }
        /* Fix for pagination overlap */
        .pagination {
            margin-top: 20px;
            clear: both;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        @if (session('status'))
            @php
                $isError = str_contains(session('status'), 'não entregue') || str_contains(session('status'), 'Erro ao enviar');
            @endphp
            <div class="alert alert-{{ $isError ? 'danger' : 'info' }} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3 d-flex align-items-center" role="alert" style="z-index: 9999; min-width: 300px; max-width: 90vw;">
                @if($isError)
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                @endif
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">Enviar Mensagem</h1>
            <div>
                <button id="btn-relatorio" class="btn btn-secondary"><i class="bi bi-list"></i> Visualizar Envios</button>
            </div>
        </div>

        <!-- Formulário de envio de SMS (visível por padrão) -->
        <form id="form-sms" action="{{ route('sms.send') }}" method="POST" class="card p-3 shadow-sm mb-4" style="max-width: 420px; margin: 0 auto;">
            @csrf
            <h2 class="mb-3 fs-5">Enviar SMS/WhatsApp</h2>
            <div class="mb-2">
                <label for="numero" class="form-label">Número de Telefone:</label>
                <div class="input-group">
                    <span class="input-group-text">+55</span>
                    <input type="text" id="numero" name="numero" class="form-control form-control-sm" required maxlength="12" pattern="\d{10,12}" placeholder="DDD + número (ex: 11999999999)">
                </div>
            </div>
            <div class="mb-2">
                <label for="mensagem" class="form-label">Mensagem:</label>
                <textarea id="mensagem" name="mensagem" class="form-control form-control-sm" required maxlength="160" placeholder="Digite sua mensagem" rows="5"></textarea>
                <div id="charCount" class="text-end mt-1 small">0/160 caracteres</div>
            </div>
            <div class="d-flex gap-2 mb-3">
                <button type="submit" class="btn btn-primary btn-sm flex-fill d-flex align-items-center justify-content-center">
                    <i class="bi bi-chat-dots me-1"></i> Enviar SMS
                </button>
                <button type="button" class="btn btn-success btn-sm flex-fill d-flex align-items-center justify-content-center" id="btn-whatsapp">
                    <i class="bi bi-whatsapp me-1"></i> Enviar WhatsApp
                </button>
            </div>
        </form>
        <form id="form-whatsapp" action="{{ route('whatsapp.send') }}" method="POST" style="display:none;">
            @csrf
            <input type="hidden" name="numero" id="whatsapp_numero">
            <input type="hidden" name="mensagem" id="whatsapp_mensagem">
        </form>

        <!-- Relatório de Envios (oculto por padrão) -->
        <div id="relatorio-envios" class="card p-4 shadow-sm mb-4 d-none">
            <h2 class="mb-3">Relatório de Envios</h2>
            <div class="d-flex justify-content-end mb-3">
                <a href="{{ route('sms.index') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center">
                    <i class="bi bi-house me-1"></i> Tela Principal
                </a>
            </div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Tipo</th>
                        <th>Descrição API</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($envios as $envio)
                        <tr>
                            <td>{{ $envio->numero }}</td>
                            <td>{{ $envio->tipo_de_mensagem }}</td>
                            <td>{{ $envio->api_description }}</td>
                            <td>{{ $envio->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <!-- Paginação -->
            <div class="d-flex flex-column align-items-center gap-1 mt-4 mb-5">
                <div class="mb-1 w-100 d-flex justify-content-center">
                    {{ $envios->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var alertBox = document.querySelector('.alert-dismissible');
            if (alertBox) {
                alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                setTimeout(function() {
                    var alert = bootstrap.Alert.getOrCreateInstance(alertBox);
                    alert.close();
                }, 4000);
            }
        });
    </script>
    <script>
        // Alternar entre formulário e relatório
        document.getElementById('btn-relatorio').addEventListener('click', function() {
            document.getElementById('form-sms').classList.add('d-none');
            document.getElementById('relatorio-envios').classList.remove('d-none');
        });
        // Corrige paginação: mantém relatório visível ao clicar nos links de paginação
        function bindRelatorioPagination() {
            document.querySelectorAll('#relatorio-envios .pagination a').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    fetch(this.href)
                        .then(response => response.text())
                        .then(html => {
                            // Cria um DOM temporário para extrair apenas o relatório
                            const tempDiv = document.createElement('div');
                            tempDiv.innerHTML = html;
                            const novoRelatorio = tempDiv.querySelector('#relatorio-envios');
                            document.getElementById('relatorio-envios').innerHTML = novoRelatorio.innerHTML;
                            // Reaplica o script de paginação
                            bindRelatorioPagination();
                            setTimeout(() => { window.scrollTo(0, document.getElementById('relatorio-envios').offsetTop); }, 100);
                        });
                });
            });
        }
        bindRelatorioPagination();
        // Contador de caracteres
        const mensagemInput = document.getElementById('mensagem');
        const charCount = document.getElementById('charCount');
        mensagemInput.addEventListener('input', () => {
            const length = mensagemInput.value.length;
            charCount.textContent = `${length}/160 caracteres`;
        });
        // Inicializa contador
        charCount.textContent = `${mensagemInput.value.length}/160 caracteres`;
        // Botão WhatsApp
        document.getElementById('btn-whatsapp').onclick = function() {
            const numero = document.getElementById('numero').value;
            const mensagem = document.getElementById('mensagem').value;
            if (!numero || !mensagem) {
                alert('Preencha o número e a mensagem!');
                return;
            }
            // Remove alertas antigos
            document.querySelectorAll('.alert-dismissible').forEach(e => e.remove());
            // Mostra indicador de processamento
            let processingDiv = document.createElement('div');
            processingDiv.className = 'alert alert-info alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3 d-flex align-items-center';
            processingDiv.style.zIndex = 9999;
            processingDiv.style.minWidth = '300px';
            processingDiv.style.maxWidth = '90vw';
            processingDiv.innerHTML = `<span class='spinner-border spinner-border-sm me-2'></span>Processando envio do WhatsApp...`;
            document.body.appendChild(processingDiv);
            // Envia via AJAX
            fetch('/whatsapp/send-ajax', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({ numero, mensagem })
            })
            .then(response => response.json())
            .then(data => {
                processingDiv.remove();
                data.tentativas.forEach(t => {
                    if (!t.mensagem) return;
                    const alertDiv = document.createElement('div');
                    alertDiv.className = `alert alert-${t.status === 'success' ? 'info' : 'danger'} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3 d-flex align-items-center`;
                    alertDiv.style.zIndex = 9999;
                    alertDiv.style.minWidth = '300px';
                    alertDiv.style.maxWidth = '90vw';
                    if (t.status === 'error') {
                        alertDiv.innerHTML = `<i class='bi bi-exclamation-triangle-fill me-2'></i> ${t.tentativa ? t.tentativa + ': ' : ''}${t.mensagem}`;
                    } else {
                        alertDiv.innerHTML = `${t.mensagem}`;
                    }
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'btn-close';
                    btn.setAttribute('data-bs-dismiss', 'alert');
                    btn.setAttribute('aria-label', 'Close');
                    alertDiv.appendChild(btn);
                    document.body.appendChild(alertDiv);
                    setTimeout(() => {
                        if (alertDiv) alertDiv.remove();
                    }, 2500);
                });
                // Mostra resultado final
                setTimeout(() => {
                    window.location.reload();
                }, 2600);
            })
            .catch(() => {
                processingDiv.remove();
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
                alertDiv.style.zIndex = 9999;
                alertDiv.style.minWidth = '300px';
                alertDiv.style.maxWidth = '90vw';
                alertDiv.innerHTML = `<i class='bi bi-exclamation-triangle-fill me-2'></i> Erro inesperado ao enviar WhatsApp!`;
                document.body.appendChild(alertDiv);
                setTimeout(() => { alertDiv.remove(); }, 3000);
            });
        };
        // Botão SMS via AJAX
        document.querySelector('form#form-sms button[type="submit"]').onclick = function(e) {
            e.preventDefault();
            const numero = document.getElementById('numero').value;
            const mensagem = document.getElementById('mensagem').value;
            if (!numero || !mensagem) {
                alert('Preencha o número e a mensagem!');
                return;
            }
            // Remove alertas antigos
            document.querySelectorAll('.alert-dismissible').forEach(e => e.remove());
            // Mostra indicador de processamento
            let processingDiv = document.createElement('div');
            processingDiv.className = 'alert alert-info alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3 d-flex align-items-center';
            processingDiv.style.zIndex = 9999;
            processingDiv.style.minWidth = '300px';
            processingDiv.style.maxWidth = '90vw';
            processingDiv.innerHTML = `<span class='spinner-border spinner-border-sm me-2'></span>Processando envio do SMS...`;
            document.body.appendChild(processingDiv);
            // Envia via AJAX
            fetch('/sms/send-ajax', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({ numero, mensagem })
            })
            .then(response => response.json())
            .then(data => {
                processingDiv.remove();
                data.tentativas.forEach(t => {
                    if (!t.mensagem) return;
                    const alertDiv = document.createElement('div');
                    alertDiv.className = `alert alert-${t.status === 'success' ? 'info' : 'danger'} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3 d-flex align-items-center`;
                    alertDiv.style.zIndex = 9999;
                    alertDiv.style.minWidth = '300px';
                    alertDiv.style.maxWidth = '90vw';
                    if (t.status === 'error') {
                        alertDiv.innerHTML = `<i class='bi bi-exclamation-triangle-fill me-2'></i> ${t.tentativa ? t.tentativa + ': ' : ''}${t.mensagem}`;
                    } else {
                        alertDiv.innerHTML = `${t.mensagem}`;
                    }
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'btn-close';
                    btn.setAttribute('data-bs-dismiss', 'alert');
                    btn.setAttribute('aria-label', 'Close');
                    alertDiv.appendChild(btn);
                    document.body.appendChild(alertDiv);
                    setTimeout(() => {
                        if (alertDiv) alertDiv.remove();
                    }, 2500);
                });
                // Mostra resultado final
                setTimeout(() => {
                    window.location.reload();
                }, 2600);
            })
            .catch(() => {
                processingDiv.remove();
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
                alertDiv.style.zIndex = 9999;
                alertDiv.style.minWidth = '300px';
                alertDiv.style.maxWidth = '90vw';
                alertDiv.innerHTML = `<i class='bi bi-exclamation-triangle-fill me-2'></i> Erro inesperado ao enviar SMS!`;
                document.body.appendChild(alertDiv);
                setTimeout(() => { alertDiv.remove(); }, 3000);
            });
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>