<p align="center">
  <img src="public/logo.svg" alt="Slenix Logo" width="250">
</p>

<h1 align="center">🌌 Slenix Framework</h1>

<p align="center">Um micro framework PHP leve, elegante e poderoso baseado no padrão MVC</p>

<p align="center">
  <a href="https://github.com/claudiovictors/slenix"><img src="https://img.shields.io/github/stars/claudiovictors/slenix?style=social" alt="GitHub Stars"></a>
  <a href="https://packagist.org/packages/slenix/slenix"><img src="https://img.shields.io/packagist/v/slenix/slenix.svg" alt="Packagist Version"></a>
  <a href="https://github.com/claudiovictors/slenix/blob/main/LICENSE"><img src="https://img.shields.io/github/license/claudiovictors/slenix" alt="License"></a>
  <img src="https://img.shields.io/badge/PHP-8.0%2B-blue" alt="PHP Version">
  <img src="https://img.shields.io/badge/Dependências-Zero-green" alt="Zero Dependencies">
</p>

---

## 📖 Sobre o Slenix

O **Slenix Framework** é um micro framework PHP projetado para desenvolvedores que buscam **simplicidade**, **desempenho** e **flexibilidade**. Baseado na arquitetura **MVC (Model-View-Controller)**, ele oferece ferramentas essenciais para construir aplicações web e APIs de forma rápida e eficiente.

Diferente de frameworks mais pesados como Laravel, o Slenix é ideal para projetos que exigem alta performance com uma curva de aprendizado reduzida. Comparado ao Slim, ele oferece um ORM integrado e um motor de templates robusto, mantendo a leveza de um micro framework. É perfeito para aplicações como APIs RESTful, sistemas CMS simples ou projetos que requerem configuração mínima.

### ✨ Recursos Principais

- 🚀 **Roteamento Simples**: Defina rotas dinâmicas com parâmetros e grupos.
- 🗃️ **ORM Integrado**: Gerencie bancos de dados com modelos intuitivos e relacionamentos.
- 🎨 **Template Luna**: Crie views dinâmicas com sintaxe limpa e poderosa.
- 🛠️ **Celestial CLI**: Automatize a criação de Models, Controllers e inicialização do servidor.
- 📤 **Upload de Arquivos**: Suporte simplificado para upload de arquivos.
- 🌐 **Requisições HTTP**: Integração robusta com a classe `HttpClient` para consumir APIs.
- 🔒 **Middlewares**: Controle de autenticação, CSRF, JWT e muito mais.
- 🗄️ **Bancos Relacionais**: Suporte a MySQL e PostgreSQL.

> **Dica**: O Slenix é ideal para projetos que precisam de um backend leve e rápido, como APIs para aplicativos móveis ou sistemas de gerenciamento de conteúdo.

---

## 📋 Pré-requisitos

- 🐘 **PHP**: 8.0 ou superior
- 🗄️ **Extensão PDO**: Habilitada (necessária para o ORM)
- 🔌 **Extensões Recomendadas**:
  - `mbstring`: Para manipulação de strings multibyte.
  - `curl`: Para requisições HTTP com `HttpClient`.
  - `openssl`: Para conexões seguras.
- 📦 **Composer**: Recomendado para autoload
- 🌍 **Servidor Web**: Apache, Nginx ou servidor embutido do PHP (`celestial serve`)
- 💻 **Sistemas Operacionais**: Compatível com Linux, macOS e Windows.

> **Nota**: Certifique-se de que as extensões listadas estão habilitadas no seu `php.ini`.

---

## ⚙️ Instalação

Siga os passos abaixo para configurar o **Slenix Framework** em seu ambiente.

### 1️⃣ Instalar via Composer
```bash
composer require slenix/slenix
```

### 2️⃣ Clonar o Repositório
```bash
git clone https://github.com/claudiovictors/slenix.git
cd slenix
composer install
```

### 3️⃣ Criar um Projeto com Composer
```bash
composer create-project slenix/slenix meu-projeto
cd meu-projeto
```

### 6️⃣ Iniciar o Servidor Embutido
```bash
php celestial serve
```
Acesse `http://127.0.0.1:8080` no navegador para ver a página de boas-vindas.

> **Dica**: Configure o arquivo `.env` com as credenciais do banco de dados antes de iniciar o servidor. Veja a seção [Configuração do Banco de Dados](#configuração-do-banco-de-dados).

---

## 🚀 Primeiros Passos

### 🛣️ Rotas Básicas
As rotas são definidas no arquivo `routes/web.php`. O Slenix suporta métodos HTTP como `GET`, `POST`, `PUT`, etc.

```php
use Slenix\Http\Message\Router;

Router::get('/', function(Request $request, Response $response) {
    return view('welcome');
})->name('home');
```
> **Dica**: Use nomes de rotas com `->name()` para facilitar redirecionamentos e geração de URLs.

### 🛣️ Rotas com Parâmetros
Você pode definir parâmetros obrigatórios ou opcionais nas rotas.

```php
// Rota com parâmetro obrigatório
Router::get('/user/{id}', function(Request $request, Response $response, array $params) {
    // Acessa o parâmetro 'id' da URL
    return view('user.profile', ['user_id' => $params['id']]);
})->name('user.profile');

// Rota com parâmetro opcional
Router::get('/posts/{category?}', function(Request $request, Response $response, array $params) {
    // Usa 'all' como padrão se a categoria não for fornecida
    $category = $params['category'] ?? 'all';
    return view('posts.index', ['category' => $category]);
})->name('posts.index');
```

### 🛣️ Testando Rotas
Para testar uma rota, use o servidor embutido:
```bash
php celestial serve
```
Acesse `http://127.0.0.1:8080/user/1` para ver o resultado da rota `/user/{id}`.

### 🛣️ Rotas com Controllers
```php
Router::get('/users', [UserController::class, 'index'])->name('users.index');

// Rota POST com middleware
Router::post('/users', [UserController::class, 'store'])
    ->middleware('auth')
    ->name('users.store');
```

### 🛣️ Grupos de Rotas
Organize rotas relacionadas com prefixos ou middlewares:

```php
Router::group(['prefix' => '/api/v1'], function() {
    Router::get('/users', [UserController::class, 'apiIndex']);
    Router::post('/users', [UserController::class, 'apiStore']);
});

Router::group(['middleware' => 'auth'], function() {
    Router::get('/profile', [UserController::class, 'profile']);
});
```

### 🛣️ Rotas com Middlewares
Proteja suas rotas com middlewares personalizados:

```php
Router::post('/login', [UserController::class, 'login'])
    ->middleware('csrf');

Router::get('/admin/dashboard', [UserController::class, 'dashboard'])
    ->middleware(['auth', 'admin'])
    ->name('admin.dashboard');
```

---

## 🎨 Usando o Luna Templates

O **Luna Templates** é o motor de templates do Slenix, inspirado no Blade, com sintaxe limpa e poderosa.

### Exemplo de Rota com View
```php
Router::get('/users/{user_id}', function ($req, $res, $args) {
    $user = User::find($args['user_id']);
    if (!$user) {
        $res->status(404)->json(['message' => 'Usuário não encontrado!']);
    }
    return view('pages.user', compact('user'));
});
```

### Exemplo de View (`views/pages/user.luna.php`)
```php
<h1>Perfil do Usuário</h1>

@if ($user)
    <h2>{{ $user->name }}</h2>
    <p>Email: {{ $user->email }}</p>
@else
    <p>Usuário não encontrado.</p>
@endif

@foreach ($user->posts as $post)
    <div>
        <h3>{{ $post->title }}</h3>
        <p>{{ $post->content }}</p>
    </div>
@endforeach
```

> **Dica**: Sempre valide os dados antes de passá-los para o template para evitar erros de renderização.

---

## 🗃️ ORM

O **Slenix ORM** é uma ferramenta poderosa para interagir com bancos de dados relacionais como MySQL e PostgreSQL. Ele suporta operações CRUD, relacionamentos e consultas avançadas.

### Configurando Tabelas
Antes de usar o ORM, certifique-se de que suas tabelas estão configuradas. Exemplo de estrutura para a tabela `users`:

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Criando Registros
```php
// Usando create()
$user = User::create([
    'name' => 'João Silva',
    'email' => 'joao@email.com',
    'password' => 'senha123',
    'active' => true
]);

// Usando new e save()
$user = new User();
$user->name = 'Maria Santos';
$user->email = 'maria@email.com';
$user->save();
```

### Lendo Registros
```php
// Buscar todos
$users = User::all();

// Buscar por ID
$user = User::find(1);

// Buscar com condições
$activeUsers = User::where('active', true)->get();
```

### Atualizando Registros
```php
$user = User::find(1);
$user->name = 'Novo Nome';
$user->save();
```

### Deletando Registros
```php
$user = User::find(1);
$user->delete();
```

### Consultas Básicas
```php
// WHERE simples
$users = User::where('name', 'LIKE', '%João%')->get();

// WHERE múltiplos
$users = User::where('active', true)
            ->where('age', '>', 18)
            ->get();
```

### Consultas Avançadas
```php
// WHERE aninhados
$users = User::where('active', true)
            ->where(function($query) {
                $query->where('age', '>', 18)
                      ->orWhere('verified', true);
            })->get();
```

### Relacionamentos no ORM
O Slenix ORM suporta relacionamentos como `HasMany`, `HasOne` e `BelongsTo`.

#### Configurando Relacionamentos
**Modelo User**:
```php
namespace App\Models;

use Slenix\Database\Model;

class User extends Model
{
    protected $table = 'users';
    public function posts() {
        return $this->hasMany(Post::class, 'user_id', 'id');
    }
}
```

**Modelo Post**:
```php
namespace App\Models;

use Slenix\Database\Model;

class Post extends Model
{
    protected $table = 'posts';
    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
```

#### Eager Loading
```php
$users = User::with('posts:title,content')->get();
foreach ($users as $user) {
    echo "Usuário: {$user->name}\n";
    foreach ($user->posts as $post) {
        echo "- {$post->title}\n";
    }
}
```

### Agregações
```php
$total = User::count();
$avgAge = User::avg('age');
$maxSalary = User::max('salary');
```

> **Boas Práticas**: Sempre valide os dados de entrada antes de usá-los em consultas ao banco para evitar injeção de SQL. Use o método `sanitize()` do Slenix.

---

## 📧 Sistema de E-mail Avançado

O Slenix inclui um sistema completo de e-mail que suporta tanto SMTP quanto a função `mail()` nativa do PHP, sem necessidade de dependências externas.

### ⚙️ Configuração no .env

Adicione as configurações de e-mail ao seu arquivo `.env`:

```env

# Método de envio: 'mail' (função nativa) ou 'smtp'
EMAIL_METHOD=smtp

# Remetente padrão
MAIL_FROM_ADDRESS=noreply@seusite.com
MAIL_FROM_NAME="Seu Site"

# Configurações SMTP
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=seu_email@gmail.com
SMTP_PASSWORD=sua_senha_app
SMTP_ENCRYPTION=tls
SMTP_AUTH=true
SMTP_TIMEOUT=30
```

### 📨 Uso Básico

#### Envio Simples
```php
use Slenix\Libraries\Email;

// E-mail texto simples
$email = new Email();
$success = $email
    ->method('smtp') // ou 'mail'
    ->from('noreply@seusite.com', 'Seu Site')
    ->to('usuario@exemplo.com')
    ->subject('Bem-vindo!')
    ->message('Olá! Bem-vindo ao nosso sistema.')
    ->send();

if ($success) {
    echo "E-mail enviado com sucesso!";
} else {
    echo "Erro ao enviar e-mail.";
}
```

#### E-mail HTML
```php
$htmlMessage = '
<html>
<head>
    <style>
        .container { font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; }
        .header { background: #007bff; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .footer { background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bem-vindo ao Slenix!</h1>
        </div>
        <div class="content">
            <p>Olá, <strong>{{nome}}</strong>!</p>
            <p>Sua conta foi criada com sucesso. Você já pode começar a usar nossos serviços.</p>
            <p><a href="{{link_login}}" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Fazer Login</a></p>
        </div>
        <div class="footer">
            <p>Este é um e-mail automático, não responda.</p>
        </div>
    </div>
</body>
</html>';

$email = new Email();
$success = $email
    ->method('smtp')
    ->from('noreply@seusite.com', 'Equipe Slenix')
    ->to('usuario@exemplo.com')
    ->subject('🎉 Conta criada com sucesso!')
    ->message($htmlMessage, true) // true = HTML
    ->send();
```

#### E-mail com Múltiplos Destinatários
```php
$email = new Email();
$success = $email
    ->method('smtp')
    ->from('newsletter@seusite.com', 'Newsletter')
    ->to('user1@exemplo.com')
    ->to('user2@exemplo.com')
    ->to('user3@exemplo.com')
    ->subject('Newsletter Semanal')
    ->message('<h1>Novidades da Semana</h1><p>Confira as últimas atualizações!</p>', true)
    ->send();
```

#### E-mail com Anexos
```php
$email = new Email();
$success = $email
    ->method('smtp')
    ->from('vendas@seusite.com', 'Equipe de Vendas')
    ->to('cliente@exemplo.com')
    ->subject('Sua Fatura - Mês de Janeiro')
    ->message('
        <h2>Fatura em Anexo</h2>
        <p>Caro cliente,</p>
        <p>Segue em anexo sua fatura referente ao mês de janeiro.</p>
        <p>Qualquer dúvida, entre em contato conosco.</p>
        <p>Atenciosamente,<br><strong>Equipe de Vendas</strong></p>
    ', true)
    ->attach('/path/to/invoice.pdf')
    ->attach('/path/to/terms.pdf')
    ->send();
```

### 🎨 Templates de E-mail

#### Criando uma Classe de Templates
```php
<?php

namespace App\Mail;

use Slenix\Libraries\Email;

class EmailTemplates
{
    public static function welcome(string $userEmail, string $userName): Email
    {
        $template = self::loadTemplate('welcome', [
            'user_name' => $userName,
            'login_url' => env('APP_URL') . '/login',
            'app_name' => env('APP_NAME', 'Slenix')
        ]);

        return (new Email())
            ->method('smtp')
            ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
            ->to($userEmail)
            ->subject('🎉 Bem-vindo ao ' . env('APP_NAME', 'Slenix'))
            ->message($template, true);
    }

    public static function resetPassword(string $userEmail, string $resetToken): Email
    {
        $resetUrl = env('APP_URL') . '/reset-password?token=' . $resetToken;
        
        $template = self::loadTemplate('password-reset', [
            'reset_url' => $resetUrl,
            'app_name' => env('APP_NAME', 'Slenix'),
            'expires_in' => '1 hora'
        ]);

        return (new Email())
            ->method('smtp')
            ->from('security@' . parse_url(env('APP_URL'), PHP_URL_HOST), 'Segurança')
            ->to($userEmail)
            ->subject('🔒 Redefinição de Senha')
            ->message($template, true);
    }

    public static function orderConfirmation(string $userEmail, array $orderData): Email
    {
        $template = self::loadTemplate('order-confirmation', $orderData);

        return (new Email())
            ->method('smtp')
            ->from('pedidos@' . parse_url(env('APP_URL'), PHP_URL_HOST), 'Pedidos')
            ->to($userEmail)
            ->subject('✅ Pedido Confirmado #' . $orderData['order_id'])
            ->message($template, true);
    }

    protected static function loadTemplate(string $templateName, array $vars = []): string
    {
        $templatePath = __DIR__ . "/templates/{$templateName}.html";
        
        if (!file_exists($templatePath)) {
            throw new \Exception("Template de e-mail não encontrado: {$templateName}");
        }
        
        $template = file_get_contents($templatePath);
        
        // Substituição simples de variáveis
        foreach ($vars as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        
        return $template;
    }
}
```

#### Template Welcome (`app/Mail/templates/welcome.html`)
```html
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-vindo</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 20px; text-align: center; }
        .content { padding: 30px 20px; }
        .button { display: inline-block; background: #28a745; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; color: #6c757d; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Bem-vindo ao {{app_name}}!</h1>
        </div>
        
        <div class="content">
            <h2>Olá, {{user_name}}! 👋</h2>
            
            <p>É com grande prazer que damos as boas-vindas a você em nossa plataforma!</p>
            
            <p>Sua conta foi criada com sucesso e você já pode começar a explorar todos os recursos disponíveis.</p>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{login_url}}" class="button">Começar Agora</a>
            </div>
            
            <p>Se você tiver alguma dúvida, não hesite em entrar em contato conosco. Estamos aqui para ajudar!</p>
            
            <p>Atenciosamente,<br><strong>Equipe {{app_name}}</strong></p>
        </div>
        
        <div class="footer">
            <p>Este é um e-mail automático, não responda.</p>
            <p>© 2024 {{app_name}}. Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>
```

### 🛡️ Sistema de E-mail com Fallback

#### Implementando Fallback Automático
```php
<?php

namespace App\Services;

use Slenix\Libraries\Email;
use Exception;

class EmailService
{
    protected Email $email;
    protected array $config;

    public function __construct()
    {
        $this->email = new Email();
        $this->config = [
            'max_retries' => 3,
            'retry_delay' => 1000, // milliseconds
            'fallback_to_mail' => true
        ];
    }

    public function send(string $to, string $subject, string $message, array $options = []): bool
    {
        $attempts = 0;
        $maxAttempts = $this->config['max_retries'];
        
        while ($attempts < $maxAttempts) {
            try {
                $email = new Email();
                
                $success = $email
                    ->method($options['method'] ?? 'smtp')
                    ->from($options['from'] ?? env('MAIL_FROM_ADDRESS'), $options['from_name'] ?? env('MAIL_FROM_NAME'))
                    ->to($to)
                    ->subject($subject)
                    ->message($message, $options['is_html'] ?? true);

                // Adiciona anexos se existirem
                if (isset($options['attachments'])) {
                    foreach ($options['attachments'] as $attachment) {
                        $email->attach($attachment);
                    }
                }

                if ($email->send()) {
                    $this->logSuccess($to, $subject, $attempts + 1);
                    return true;
                }
                
            } catch (Exception $e) {
                $this->logError($to, $subject, $e->getMessage(), $attempts + 1);
            }
            
            $attempts++;
            
            if ($attempts < $maxAttempts) {
                usleep($this->config['retry_delay'] * 1000);
            }
        }
        
        // Fallback para função mail() se SMTP falhar
        if ($this->config['fallback_to_mail'] && ($options['method'] ?? 'smtp') === 'smtp') {
            return $this->sendFallback($to, $subject, $message, $options);
        }
        
        return false;
    }

    protected function sendFallback(string $to, string $subject, string $message, array $options = []): bool
    {
        try {
            $email = new Email();
            
            $success = $email
                ->method('mail')
                ->from($options['from'] ?? env('MAIL_FROM_ADDRESS'), $options['from_name'] ?? env('MAIL_FROM_NAME'))
                ->to($to)
                ->subject($subject)
                ->message($message, $options['is_html'] ?? true)
                ->send();
                
            if ($success) {
                $this->logSuccess($to, $subject, 'fallback');
            } else {
                $this->logError($to, $subject, 'Fallback também falhou', 'fallback');
            }
            
            return $success;
            
        } catch (Exception $e) {
            $this->logError($to, $subject, $e->getMessage(), 'fallback');
            return false;
        }
    }

    protected function logSuccess(string $to, string $subject, $attempt): void
    {
        error_log("Email enviado com sucesso para {$to} (tentativa: {$attempt}) - Assunto: {$subject}");
    }

    protected function logError(string $to, string $subject, string $error, $attempt): void
    {
        error_log("Falha ao enviar email para {$to} (tentativa: {$attempt}) - Erro: {$error} - Assunto: {$subject}");
    }

    // Método para enviar emails em lote
    public function sendBatch(array $recipients, string $subject, string $message, array $options = []): array
    {
        $results = [];
        
        foreach ($recipients as $recipient) {
            $email = is_array($recipient) ? $recipient['email'] : $recipient;
            $name = is_array($recipient) ? ($recipient['name'] ?? '') : '';
            
            // Personaliza mensagem se necessário
            $personalizedMessage = $message;
            if ($name) {
                $personalizedMessage = str_replace('{{name}}', $name, $personalizedMessage);
            }
            
            $results[$email] = $this->send($email, $subject, $personalizedMessage, $options);
            
            // Pequena pausa entre envios para evitar spam
            usleep(100000); // 0.1 segundos
        }
        
        return $results;
    }
}
```

#### Usando o EmailService
```php
use App\Services\EmailService;
use App\Mail\EmailTemplates;

// Envio simples
$emailService = new EmailService();
$success = $emailService->send(
    'usuario@exemplo.com',
    'Bem-vindo!',
    '<h1>Olá!</h1><p>Bem-vindo ao nosso site!</p>',
    ['is_html' => true]
);

// Usando templates
$welcomeEmail = EmailTemplates::welcome('usuario@exemplo.com', 'João Silva');
$success = $welcomeEmail->send();

// Envio em lote
$recipients = [
    ['email' => 'user1@exemplo.com', 'name' => 'João'],
    ['email' => 'user2@exemplo.com', 'name' => 'Maria'],
    ['email' => 'user3@exemplo.com', 'name' => 'Pedro']
];

$results = $emailService->sendBatch(
    $recipients,
    'Newsletter Semanal',
    '<h1>Olá, {{name}}!</h1><p>Confira as novidades desta semana!</p>'
);
```

---

## 🌐 Usando a Classe HttpClient

A classe `HttpClient` permite realizar requisições HTTP de forma fluida e robusta.

### Exemplo 1: Consumindo uma API JSON
```php
use Slenix\Http\Message\HttpClient;

$client = HttpClient::make()
    ->baseUrl('https://api.example.com')
    ->withHeader('Accept', 'application/json')
    ->withRetries(2, 1000);

$response = $client->get('/users', ['page' => 1, 'limit' => 10]);

if ($response->getStatusCode() === 200) {
    $users = $response->getJson();
    print_r($users);
} else {
    echo "Erro: " . $response->getBody();
}
```

### Exemplo 2: Tratando Erros
```php
try {
    $client = HttpClient::make()
        ->baseUrl('https://api.example.com')
        ->withHeader('Accept', 'application/json');

    $response = $client->get('/users');
    $users = $response->getJson();
    print_r($users);
} catch (\Exception $e) {
    echo "Erro na requisição: " . $e->getMessage();
}
```

### Exemplo 3: Consumindo a API do GitHub
```php
$client = HttpClient::make()
    ->baseUrl('https://api.github.com')
    ->withHeader('Accept', 'application/vnd.github.v3+json');

$response = $client->get('/users/claudiovictors');
if ($response->getStatusCode() === 200) {
    $user = $response->getJson();
    echo "Nome: {$user['name']}\nRepositórios: {$user['public_repos']}";
}
```

---

## 🧭 Session e Flash

O Slenix suporta mensagens `flash` e sessões para interagir entre páginas.

| Métodos | Descrição |
|---------|-----------|
| Session::set() | Define um valor na sessão. |
| Session::get() | Obtém um valor da sessão. |
| Session::flash() | Armazena dados como flash data. |
| Session::getFlash() | Obtém e remove flash data. |

### Exemplo de Formulário
```html
<form action="/submit" method="post">
    <label>E-mail</label>
    <input type="text" name="email" value="@old('email')"><br/>
    <label>Senha</label>
    <input type="password" name="password" value="@old('password')"><br/>
    <button type="submit">Entrar</button>
</form>
```

### Exemplo de Rota
```php
Router::get('/login', function(Request $req, Response $res) {
    return view('login');
})->middleware('csrf');

Router::post('/submit', function(Request $req, Response $res) {
    $email = sanitize($req->input('email'));
    $password = sanitize($req->input('password'));

    Session::flashOldInput($req->all());

    if (empty($email) || empty($password)) {
        Session::flash('error', 'Por favor, preencha todos os campos!');
    } else {
        Session::flash('success', 'Boas-vindas!');
    }

    return redirect('/profile');
});
```

---

## 🛠️ Usando a Celestial CLI

A **Celestial CLI** é uma ferramenta para agilizar o desenvolvimento.

### Comandos Principais
```bash
php celestial serve          # Inicia o servidor embutido
php celestial make:controller UserController  # Cria um controller
php celestial make:model User  # Cria um modelo
php celestial make:middleware Auth  # Cria um middleware
php celestial list          # Lista todos os comandos
```

---

## 🗃️ Configuração do Banco de Dados

Configure o acesso ao banco de dados no arquivo `.env`:

```env
APP_DEBUG=true
APP_URL=http://localhost:8080

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_NAME=slenix_db
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha

# Método de envio: 'mail' (função nativa) ou 'smtp'
EMAIL_METHOD=smtp

# Remetente padrão
MAIL_FROM_ADDRESS=noreply@seusite.com
MAIL_FROM_NAME="Seu Site"

# Configurações SMTP
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=seu_email@gmail.com
SMTP_PASSWORD=sua_senha_app
SMTP_ENCRYPTION=tls
SMTP_AUTH=true
SMTP_TIMEOUT=30
```

---

## 📜 Licença

O **Slenix Framework** é licenciado sob a [MIT License](https://github.com/claudiovictors/slenix/blob/main/LICENSE).

<p align="center">Feito com 🖤 por <a href="https://github.com/claudiovictors">Cláudio Victor</a></p>