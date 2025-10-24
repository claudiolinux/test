<p align="center">
  <img src="public/logo.svg" alt="Slenix Logo" width="250">
</p>

<h1 align="center">🌌 Slenix Framework demo</h1>

<p align="center">Um micro framework PHP leve, elegante e poderoso baseado no padrão MVC.</p>

<p align="center">
  <a href="https://github.com/claudiovictors/slenix"><img src="https://img.shields.io/github/stars/claudiovictors/slenix?style=social" alt="GitHub Stars"></a>
  <a href="https://packagist.org/packages/slenix/slenix"><img src="https://img.shields.io/packagist/v/slenix/slenix.svg" alt="Packagist Version"></a>
  <a href="https://github.com/claudiovictors/slenix/blob/main/LICENSE"><img src="https://img.shields.io/github/license/claudiovictors/slenix" alt="License"></a>
  <img src="https://img.shields.io/badge/PHP-8.0%2B-blue" alt="PHP Version">
  <img src="https://img.shields.io/badge/Dependências-Zero-green" alt="Zero Dependencies">
</p>

## 📖 Sobre o Slenix

O **Slenix Framework** é um micro framework PHP projetado para desenvolvedores que buscam **simplicidade**, **desempenho** e **flexibilidade**. Baseado na arquitetura **MVC (Model-View-Controller)**, ele oferece ferramentas essenciais para construir aplicações web e APIs de forma rápida e eficiente, sem dependências externas.

### ✨ Recursos Principais

- 🚀 **Roteamento Simples**: Defina rotas dinâmicas com parâmetros e grupos.
- 🗃️ **ORM Integrado**: Gerencie bancos de dados com modelos intuitivos e relacionamentos.
- 🎨 **Template Luna**: Crie views dinâmicas com sintaxe limpa e poderosa.
- 🛠️ **Celestial CLI**: Automatize a criação de Models, Controllers e inicialização do servidor.
- 📤 **Upload de Arquivos**: Suporte robusto para upload de arquivos com validações.
- 🌐 **Requisições HTTP**: Integração fluida com a classe `HttpClient` para consumir APIs.
- 🔒 **Middlewares**: Suporte a autenticação, CSRF, JWT, AUTH, GUEST e mais.
- 🗄️ **Bancos Relacionais**: Suporte nativo a MySQL e PostgreSQL.

> **Dica**: O Slenix é ideal para projetos que precisam de um backend leve e rápido, como APIs para aplicativos móveis ou sistemas de gerenciamento de conteúdo.

## 📋 Pré-requisitos

- 🐘 **PHP**: 8.0 ou superior
- 🗄️ **Extensão PDO**: Habilitada (necessária para o ORM)
- 🔌 **Extensões Recomendadas**:
  - `curl`: Para requisições HTTP com `HttpClient`.
  - `openssl`: Para conexões seguras.
  - `fileinfo`: Para validação de tipos MIME em uploads.
- 📦 **Composer**: Recomendado para autoload.
- 🌍 **Servidor Web**: Apache, Nginx ou servidor embutido do PHP (`celestial serve`).

> **Nota**: Certifique-se de que as extensões listadas estão habilitadas no seu `php.ini`.

## ⚙️ Instalação

### 1️⃣ Instalar via Composer
```bash
composer require slenix/slenix
```

### 2️⃣ Clonar o Repositório
```bash
git clone https://github.com/claudiovictors/slenix.git
```

### 3️⃣ Criar um Projeto com Composer
```bash
composer create-project slenix/slenix meu-projeto
```

### 4️⃣ Iniciar o Servidor Embutido
```bash
php celestial serve
```
Acesse `http://127.0.0.1:8080` no navegador para ver a página de boas-vindas.

> **Dica**: Configure o arquivo `.env` com as credenciais do banco de dados antes de iniciar o servidor. Veja a seção [Configuração do Banco de Dados](#configuração-do-banco-de-dados).

## 🧱 Estrutura do Projeto

```bash
Slenix
├── app
│   ├── Controllers # Gerencia os Controllers
│   ├── Middlewares # Gerencia os Middlewares
│   └── Models # Gerencia os Models
├── public
│   ├── index.php
│   └── logo.svg
├── routes
│   └── web.php # Gerencia as rotas
├── src
├── views # Gerencia os templates .luna.php
│   └── welcome.luna.php
├── vendor
│   ├── autoload.php
│   └── composer
├── celestial # CLI para automação
├── CHANGELOG.md
├── composer.json
├── LICENSE
└── README.md
```

> **Dica**: A pasta `src` contém classes bem documentadas com PHPDoc. Consulte-a para explorar os recursos do framework.

## 🚀 Primeiros Passos

### 🛣️ Rotas Básicas
As rotas são definidas em `routes/web.php` e suportam métodos HTTP como `GET`, `POST`, `PUT`, etc.

```php
use Slenix\Http\Message\Router;

Router::get('/', function(Request $request, Response $response) {
    return $response->write('Hello, World!');
});

Router::get('/home', function(Request $request, Response $response) {
    return view('welcome');
})->name('home');
```

### 🛣️ Rotas com Parâmetros
Defina rotas com parâmetros obrigatórios ou opcionais.

```php
// Rota com parâmetro obrigatório
Router::get('/user/{id}', function(Request $request, Response $response, array $params) {
    return view('user.profile', ['user_id' => $params['id']]);
})->name('user.profile');

// Rota com parâmetro opcional
Router::get('/posts/{category?}', function(Request $request, Response $response, array $params) {
    $category = $params['category'] ?? 'all';
    return view('posts.index', ['category' => $category]);
});
```

### 🛣️ Rotas com Controllers
Crie controllers com o comando `celestial`:

```bash
php celestial make:controller UserController
```

```php
Router::get('/users', [UserController::class, 'index'])->name('users.index');
Router::post('/users', [UserController::class, 'store'])->middleware('csrf');
```

### 🛣️ Grupos de Rotas
Organize rotas com prefixos ou middlewares:

```php
Router::group(['prefix' => 'api/v1'], function() {
    Router::get('/users', [UserController::class, 'apiIndex']);
    Router::post('/users', [UserController::class, 'apiStore'])->middleware('csrf');
});

Router::group(['middleware' => 'auth'], function() {
    Router::get('/profile', [UserController::class, 'profile']);
});
```

### 🛣️ Testando Rotas
Use o servidor embutido para testar:

```bash
php celestial serve
```
Acesse `http://127.0.0.1:8080/user/1` para testar a rota `/user/{id}`.

## 🛠️ Celestial CLI

A **Celestial** é a ferramenta de linha de comando do Slenix que agiliza o desenvolvimento.

### Comandos Principais
```bash
# Iniciar servidor na porta padrão (8080) ou personalizada
php celestial serve
php celestial serve 3000

# Criar controller
php celestial make:controller UserController

# Criar model
php celestial make:model User

# Criar middleware
php celestial make:middleware Auth

# Listar todos os comandos
php celestial list

# Listar todas as rotas
php route:list
```

## 🎨 Luna Templates

O **Luna** é o motor de templates do Slenix, inspirado no Blade, com sintaxe limpa e poderosa. Os arquivos têm extensão `.luna.php`.

### Exemplo de Layout (`views/layouts/main.luna.php`)

```html
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Slenix App')</title>
</head>
<body>
    <nav>Menu de Navegação</nav>
    <main>@yield('content')</main>
    <footer>Rodapé</footer>
</body>
</html>
```

### Exemplo de View (`views/pages/home.luna.php`)

```html
@extends('layouts.main')

@section('title', 'Página Inicial')

@section('content')
    <h1>Bem-vindo ao Slenix!</h1>
    <p>{{ $message ?? 'Hello, World!' }}</p>
@endsection
```

### Rota para a View (`routes/web.php`)

```php
use Slenix\Http\Router;

Router::get('/helloworld', function(Request $request, Response $response) {
    return view('pages.home', ['message' => 'Bem-vindo ao Slenix!']);
})->name('home.page');
```

### Diretivas do Luna
- **Escaping Seguro**:
  ```php
  {{ $variable }} <!-- Escapado -->
  {!! $variable !!} <!-- Não escapado -->
  ```
- **Condicionais**:
  ```php
  @if($condition) ... @elseif($otherCondition) ... @else ... @endif
  @isset($variable) ... @endisset
  @empty($variable) ... @endempty
  @unless($condition) ... @endunless
  ```
- **Laços**:
  ```php
  @foreach($items as $item) ... @endforeach
  @for($i = 0; $i < 5; $i++) ... @endfor
  @while($condition) ... @endwhile
  ```
- **Controle de Fluxo**:
  ```php
  @continue
  @break
  ```
- **Templates**:
  ```php
  @include('partial')
  @extends('layout')
  @section('section') ... @endsection
  @yield('section')
  ```
- **Scripts e Estilos**:
  ```php
  @script ... @endscript
  @style ... @endstyle
  ```
- **Outras**:
  ```php
  @php ... @endphp
  @csrf
  @old('field')
  ```

> **Dica**: Valide os dados antes de passá-los aos templates para evitar erros de renderização.

## 🗃️ ORM

O **Slenix ORM** é uma ferramenta poderosa para interagir com bancos de dados relacionais (MySQL e PostgreSQL), suportando operações CRUD, relacionamentos e consultas avançadas.

### Configuração do Banco de Dados
Configure o arquivo `.env`:

```env
DB_CONNECTION=mysql
DB_HOSTNAME=localhost
DB_PORT=3306
DB_NAME=slenix_db
DB_USERNAME=root
DB_PASSWORD=
DB_CHARSET=utf8mb4
```

### Estrutura da Tabela `users`
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

### Exemplos de Uso
#### Criando Registros
```php
// Usando create()
$user = User::create([
    'name' => 'Cláudio Victor',
    'email' => 'claudio@example.com',
    'password' => password_hash('senha123', PASSWORD_DEFAULT),
    'active' => true
]);

// Usando new e save()
$user = new User();
$user->name = 'Maria Santos';
$user->email = 'maria@example.com';
$user->password = password_hash('senha123', PASSWORD_DEFAULT);
$user->save();

// Usando fill()
$user = new User();
$user->fill([
    'name' => 'Gustavo Guanabara',
    'email' => 'gustavo@example.com',
    'password' => password_hash('senha123', PASSWORD_DEFAULT),
    'active' => true
])->save();
```

#### Lendo Registros
```php
// Todos os registros
$users = User::all();

// Por ID
$user = User::find(1);

// Com condições
$activeUsers = User::where('active', true)->get();
```

#### Atualizando Registros
```php
$user = User::find(1);
$user->name = 'Novo Nome';
$user->save();
```

#### Deletando Registros
```php
$user = User::find(1);
$user->delete();
```

#### Consultas Avançadas
```php
// WHERE múltiplos
$users = User::where('active', true)
    ->where('age', '>', 18)
    ->get();

// WHERE aninhados
$users = User::where('active', true)
    ->where(function($query) {
        $query->where('age', '>', 18)
              ->orWhere('verified', true);
    })->get();
```

#### Relacionamentos
**Modelo User**:
```php
namespace App\Models;

use Slenix\Database\Model;

class User extends Model
{
    protected $table = 'users';
    
    public function posts()
    {
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
    
    public function user()
    {
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

## 📤 Upload de Arquivos

A classe `Upload` gerencia uploads com validações robustas, integrada à classe `Request` para facilitar o uso.


### Exemplo de Uso
```php
use Slenix\Http\Message\Request;
use Slenix\Http\Message\Response;

Router::get('/upload', fn(Request $request, Response $response) => 
    view('upload', []));

Router::post('/upload', function(Request $request, Response $response) {
    try {
        if (!$request->hasFile('arquivo')) {
            return view('upload', ['error' => 'Nenhum arquivo enviado.']);
        }

        $upload = $request->file('arquivo');
        $upload->setAllowedMimeTypes(['image/jpeg', 'image/png', 'image/gif'])
               ->setMaxSize(5 * 1024 * 1024) // 5MB
               ->setAllowedExtensions(['jpg', 'jpeg', 'png', 'gif']);

        if (!$upload->isValid()) {
            return view('upload', ['error' => implode('; ', $upload->getErrors())]);
        }

        $uploadDir = storage_path('uploads');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $path = $upload->store($uploadDir, true);

        return view('upload-result', [
            'sucesso' => true,
            'nome_arquivo' => $upload->getOriginalName(),
            'tamanho' => $upload->getHumanSize(),
            'tipo' => $upload->getMimeType(),
            'caminho' => basename($path),
            'e_imagem' => $upload->isImage()
        ]);
    } catch (\RuntimeException $e) {
        return view('upload', ['error' => $e->getMessage()]);
    }
})->middleware('csrf');
```

### Propriedades e Métodos
- **Propriedades**:
 - `$file`: Dados do arquivo (de $_FILES).
 - `$allowedMimeTypes`: Tipos MIME permitidos.
 - `$allowedExtensions`: Extensões permitidas.
 - `$maxSize`: Tamanho máximo (bytes).
 - `$errors`: Lista de erros de validação.
- **Métodos**:
 - `setAllowedMimeTypes(array $types)`: Define tipos MIME permitidos.
 - `setAllowedExtensions(array $exts)`: Define extensões permitidas.
 - `setMaxSize(int $size)`: Define tamanho máximo.
 - `isValid()`: bool: Verifica se o arquivo é válido.
 - `getErrors()`: array: Retorna erros de validação.
 - `getOriginalName()`: string: Nome original do arquivo.
 - `getOriginalExtension()`: string: Extensão do arquivo.
 - `getMimeType()`: string: Tipo MIME.
 - `getSize()`: int: Tamanho em bytes.
 - `getHumanSize()`: string: Tamanho formatado (ex: "2.5 MB").
 - `isImage()`: bool: Verifica se é uma imagem.
 - `store(string $directory, bool $unique = false)`: string: Salva o arquivo.


## 🌐 Requisições HTTP com HttpClient

A classe `HttpClient` fornece uma interface fluida para requisições HTTP com suporte a autenticação, retries, timeouts e callbacks.

### Exemplo 1: Requisição GET
```php
use Slenix\Http\Message\HttpClient;

$client = HttpClient::make()
    ->baseUrl('https://api.example.com')
    ->withHeader('Accept', 'application/json')
    ->withRetries(3, 2000)
    ->timeout(10);

$response = $client->get('/users', ['page' => 1, 'limit' => 10]);

if ($response->getStatusCode() === 200) {
    $users = $response->getJson();
    print_r($users);
} else {
    echo "Erro: " . $response->getBody();
}
```

### Exemplo 2: POST com Autenticação JWT
```php
use Slenix\Http\Message\HttpClient;

$client = HttpClient::make()
    ->baseUrl('https://api.example.com')
    ->withHeader('Accept', 'application/json')
    ->withAuth('bearer', 'seu_token_jwt');

$response = $client->post('/users', [
    'name' => 'Cláudio Victor',
    'email' => 'claudio@example.com'
]);

if ($response->getStatusCode() === 201) {
    echo "Usuário criado com sucesso!";
} else {
    echo "Erro: " . $response->getBody();
}
```

### Exemplo 3: Callbacks para Eventos
```php
$client = HttpClient::make()
    ->baseUrl('https://api.github.com')
    ->withHeader('Accept', 'application/vnd.github.v3+json')
    ->on('before', function($method, $url, $body) {
        error_log("Iniciando requisição: $method $url");
    })
    ->on('after', function($response) {
        error_log("Resposta recebida: {$response->getStatusCode()}");
    })
    ->on('error', function($exception) {
        error_log("Erro na requisição: {$exception->getMessage()}");
    });

$response = $client->get('/users/claudiovictors');
if ($response->getStatusCode() === 200) {
    $user = $response->getJson();
    echo "Nome: {$user['name']}\nRepositórios: {$user['public_repos']}";
}
```

### Métodos Principais
- `baseUrl($url)`: Define a URL base.
- `withHeader($name, $value)`: Adiciona um cabeçalho.
- `withAuth($type, $credentials)`: Configura autenticação (basic, bearer, digest).
- `asJson($data)`: Define corpo como JSON.
- `withRetries($retries, $delay)`: Configura tentativas de repetição.
- `on($event, $callback)`: Registra callbacks para eventos (`before`, `after`, `error`).
- `get()`, `post()`, `put()`, `patch()`, `delete()`, `head()`, `options()`: Métodos HTTP.

## 🔒 Autenticação JWT

A classe `Jwt` fornece suporte para geração e validação de tokens JWT.

### Exemplo de Uso
```php
use Slenix\Http\Auth\Jwt;

Router::post('/login', function(Request $request, Response $response) {
    $jwt = new Jwt(env('JWT_SECRET_TOKEN'));
    $data = $request->postData();
    $email = sanitize($data['email'] ?? '');
    $password = sanitize($data['password'] ?? '');

    if (empty($email) || empty($password)) {
        return $response->json(['message' => 'E-mail e senha são obrigatórios'], 400);
    }

    // Simulação de autenticação
    $user = User::where('email', $email)->first();
    if ($user && password_verify($password, $user->password)) {
        $token = $jwt->generate([
            'user_id' => $user->id,
            'email' => $user->email
        ], 3600); // 1 hora
        return $response->json(['token' => $token]);
    }

    return $response->json(['message' => 'Credenciais inválidas'], 401);
})->middleware('csrf');

Router::get('/profile', function(Request $request, Response $response) {
    $payload = $request->getAttribute('jwt_payload');
    return $response->json(['user' => $payload]);
})->middleware('jwt');
```

### Métodos
- `generate($payload, $expiresIn)`: Gera um token JWT.
- `validate($token)`: Valida um token JWT e retorna o payload ou `null` se inválido.

## 📧 Sistema de E-mail

O Slenix suporta envio de e-mails via `SMTP` ou função `mail()` nativa do PHP.

### Configuração no `.env`
```env
EMAIL_METHOD=smtp
MAIL_FROM_ADDRESS=noreply@seusite.com
MAIL_FROM_NAME="Seu Site"
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=seu_email@gmail.com
SMTP_PASSWORD=sua_senha_app
SMTP_ENCRYPTION=tls
SMTP_AUTH=true
SMTP_TIMEOUT=30
```

### Exemplo de E-mail HTML
```php
use Slenix\Libraries\Email;

Router::get('/send-email', function(Request $request, Response $response) {
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
                <p>Sua conta foi criada com sucesso.</p>
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
        ->to('usuario@example.com')
        ->subject('🎉 Conta Criada com Sucesso!')
        ->message(str_replace(['{{nome}}', '{{link_login}}'], ['Cláudio', 'https://seusite.com/login'], $htmlMessage), true)
        ->send();

    return $response->json(['success' => $success ? 'E-mail enviado!' : 'Erro ao enviar e-mail']);
});
```

## 🧭 Sessões e Flash Messages

O Slenix suporta sessões e mensagens flash para interações entre páginas.

### Exemplo
```php
use Slenix\Http\Session;

Router::post('/form', function(Request $request, Response $response) {
    $data = $request->postData();
    Session::flash('success', 'Formulário enviado com sucesso!');
    Session::flashOldInput($data);
    return $response->redirect('/form');
});

Router::get('/form', function(Request $request, Response $response) {
    $success = Session::getFlash('success');
    $oldInput = Session::getFlash('old');
    return view('form', ['success' => $success, 'old' => $oldInput]);
});
```

## 📜 Licença

O **Slenix Framework** é licenciado sob a [MIT License](https://github.com/claudiovictors/slenix/blob/main/LICENSE).

<p align="center">Feito com 🖤 por <a href="https://github.com/claudiovictors">Cláudio Victor</a></p>