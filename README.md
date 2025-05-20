# Sistema de Envio de SMS e WhatsApp

Este projeto é um sistema web desenvolvido em Laravel para envio de mensagens SMS e WhatsApp, com painel de relatórios, tentativas automáticas de reenvio e feedback visual ao usuário.

## Funcionalidades
- Envio de SMS e WhatsApp para números nacionais (Brasil)
- Até 3 tentativas automáticas em caso de falha (REJECTED)
- Feedback visual em tempo real para cada tentativa de envio
- Relatório de envios com status, tipo, descrição da API e data
- Interface  responsiva (Bootstrap)

## Como usar

### 1. Instalação
1. Clone o repositório:
   ```sh
   git clone https://github.com/SEU_USUARIO/SEU_REPOSITORIO.git
   cd SEU_REPOSITORIO
   ```
2. Instale as dependências:
   ```sh
   composer install
   ```
3. Copie o arquivo `.env.example` para `.env` e configure o banco de dados e as chaves da API Infobip.
   ```sh
   cp .env.example .env
   php artisan key:generate
   ```
4. Execute as migrations:
   ```sh
   php artisan migrate
   ```
5. Inicie o servidor:
   ```sh
   php artisan serve
   ```

### 2. Uso do sistema
- Acesse `http://localhost:8000` no navegador.
- Preencha o número e a mensagem.
- Clique em "Enviar SMS" ou "Enviar WhatsApp".
- O sistema tentará enviar até 3 vezes em caso de erro e mostrará popups informando o status de cada tentativa.
- Consulte o relatório de envios para ver o histórico.

### 3. Configuração da API Infobip
- Configure as credenciais da API Infobip no arquivo `.env` ou diretamente nos controllers (`SmsController` e `WhatsAppController`).
- Certifique-se de que os números de origem e destino estejam autorizados na sua conta Infobip.

### 4. Observações
- O sistema está preparado para lidar com erros como REJECTED, REJECTED_PREFIX_MISSING, etc.
- O frontend utiliza AJAX para feedback em tempo real.
- O relatório mostra apenas as informações essenciais (número, tipo, descrição da API e data).

### 5. Banco de Dados
- O sistema utiliza um banco de dados relacional (MySQL) configurado no arquivo `.env`.
- Para ambiente de desenvolvimento, você pode usar MySQL conforme sua necessidade.

- Exemplo de configuração para MySQL no `.env`:
  ```env
  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=nome_do_banco
  DB_USERNAME=usuario
  DB_PASSWORD=senha
  ```
- Após configurar, execute as migrations para criar as tabelas necessárias:
  ```sh
  php artisan migrate
  ```

### 6. Exemplo de Migration para MySQL

Se você quiser criar a tabela de SMS manualmente em um banco MySQL, utilize a migration abaixo:

```php
Schema::create('sms', function (Blueprint $table) {
    $table->id();
    $table->string('numero');
    $table->text('mensagem');
    $table->string('tipo_de_mensagem')->nullable();
    $table->string('status')->nullable();
    $table->text('api_description')->nullable();
    $table->text('resposta_api')->nullable();
    $table->timestamp('data_envio')->nullable();
    $table->timestamps();
});
```

Salve este código em um arquivo de migration (ex: `database/migrations/2025_05_16_000000_create_sms_table.php`) e execute:
```sh
php artisan migrate
```

---


