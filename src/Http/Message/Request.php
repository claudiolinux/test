<?php

/*
|--------------------------------------------------------------------------
| Classe Request
|--------------------------------------------------------------------------
|
| Esta classe representa um pedido HTTP, encapsulando informações como
| parâmetros da rota, método HTTP, URI, dados de entrada (POST, GET),
| arquivos, cookies, cabeçalhos, IP do cliente e user agent.
|
*/
declare(strict_types=1);

namespace Slenix\Http\Message;

use InvalidArgumentException;

/**
 * Classe que representa um pedido HTTP.
 */
class Request
{
    /**
     * Parâmetros da rota extraídos da URI.
     *
     * @var array<string, string>
     */
    private array $params = [];
    private array $server = [];
    private array $headers = [];
    private array $attributes = [];
    private array $parsedBody = [];
    private array $queryParams = [];
    private ?array $uploadedFiles = null;

    /**
     * Construtor da classe Request.
     *
     * @param array<string, string> $params Array associativo contendo os parâmetros da rota.
     */
    public function __construct(array $params = [])
    {
        $this->params = $params;
        $this->server = $_SERVER;
        $this->queryParams = $_GET;
        $this->parsedBody();
        $this->parseHeaders();
        $this->parseUploadedFiles();
    }

    /**
     * Retorna o valor de um parâmetro da rota.
     *
     * @param string $key A chave do parâmetro.
     * @param mixed $default O valor padrão se a chave não existir.
     * @return mixed
     */
    public function param(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }

    /**
     * Retorna todos os parâmetros da rota.
     *
     * @return array
     */
    public function params(): array
    {
        return $this->params;
    }

    /**
     * Define um parâmetro da rota.
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function setParam(string $key, mixed $value): self
    {
        $this->params[$key] = $value;
        return $this;
    }

    /**
     * Retorna o método HTTP da requisição.
     *
     * @return string O método HTTP em maiúsculas.
     */
    public function method(): string
    {
        // Verifica método override via header ou campo oculto
        $overrideMethod = $this->getHeader('X-HTTP-Method-Override') 
            ?? $this->input('_method') 
            ?? null;

        if ($overrideMethod && $this->isMethod('POST')) {
            return strtoupper($overrideMethod);
        }

        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Retorna o caminho da URI da requisição.
     *
     * @return string O caminho da URI.
     */
    public function uri(): string
    {
        return parse_url($this->server['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    }

    /**
     * Retorna a URI completa da requisição.
     *
     * @return string
     */
    public function fullUri(): string
    {
        return $this->server['REQUEST_URI'] ?? '/';
    }

    /**
     * Retorna a URL completa da requisição.
     *
     * @return string
     */
    public function url(): string
    {
        $scheme = $this->isSecure() ? 'https' : 'http';
        $host = $this->getHost();
        $uri = $this->fullUri();
        
        return "{$scheme}://{$host}{$uri}";
    }

    /**
     * Retorna a query string da requisição.
     *
     * @return ?string A query string ou null se não existir.
     */
    public function queryString(): ?string
    {
        return parse_url($this->server['REQUEST_URI'] ?? '/', PHP_URL_QUERY);
    }

    /**
     * Retorna um valor de entrada (POST, GET ou corpo parseado).
     *
     * @param string $key A chave do valor de entrada.
     * @param mixed $default Valor padrão
     * @return mixed
     */
    public function input(string $key, mixed $default = null): mixed
    {
        // Busca primeiro no corpo parseado, depois POST, depois GET
        return $this->parsedBody[$key] 
            ?? $_POST[$key] 
            ?? $this->queryParams[$key] 
            ?? $default;
    }

    /**
     * Retorna todos os dados de entrada.
     *
     * @return array
     */
    public function all(): array
    {
        return array_merge($this->queryParams, $_POST, $this->parsedBody);
    }

    /**
     * Retorna apenas os campos especificados.
     *
     * @param array $keys
     * @return array
     */
    public function only(array $keys): array
    {
        $data = $this->all();
        return array_intersect_key($data, array_flip($keys));
    }

    /**
     * Retorna todos os campos exceto os especificados.
     *
     * @param array $keys
     * @return array
     */
    public function except(array $keys): array
    {
        $data = $this->all();
        return array_diff_key($data, array_flip($keys));
    }

    /**
     * Verifica se um campo existe nos dados de entrada.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->input($key) !== null;
    }

    /**
     * Verifica se um campo existe e não está vazio.
     *
     * @param string $key
     * @return bool
     */
    public function filled(string $key): bool
    {
        $value = $this->input($key);
        return $value !== null && $value !== '' && $value !== [];
    }

    /**
     * Retorna um valor POST.
     *
     * @param string $key A chave do valor de entrada.
     * @param mixed $default Valor padrão
     * @return mixed
     */
    public function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Retorna todos os dados POST.
     *
     * @return array
     */
    public function postData(): array
    {
        return $_POST;
    }

    /**
     * Retorna um valor GET.
     *
     * @param string $key A chave do valor de entrada.
     * @param mixed $default Valor padrão
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->queryParams[$key] ?? $default;
    }

    /**
     * Retorna um parâmetro de query.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function query(string $key, mixed $default = null): mixed
    {
        return $this->get($key, $default);
    }

    /**
     * Retorna todos os parâmetros de query.
     *
     * @return array
     */
    public function queryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * Retorna informações sobre um arquivo enviado.
     *
     * @param string $key A chave do arquivo.
     * @return ?array Informações do arquivo ou null.
     */
    public function file(string $key): ?array
    {
        return $this->uploadedFiles[$key] ?? null;
    }

    /**
     * Retorna todos os arquivos enviados.
     *
     * @return array
     */
    public function files(): array
    {
        return $this->uploadedFiles ?? [];
    }

    /**
     * Verifica se um arquivo foi enviado.
     *
     * @param string $key
     * @return bool
     */
    public function hasFile(string $key): bool
    {
        $file = $this->file($key);
        return $file && $file['error'] === UPLOAD_ERR_OK && $file['size'] > 0;
    }

    /**
     * Retorna o valor de um cookie.
     *
     * @param string $key A chave do cookie.
     * @param mixed $default O valor padrão se a chave não existir.
     * @return mixed
     */
    public function cookie(string $key, mixed $default = null): mixed
    {
        return $_COOKIE[$key] ?? $default;
    }

    /**
     * Retorna todos os cookies.
     *
     * @return array
     */
    public function cookies(): array
    {
        return $_COOKIE;
    }

    /**
     * Retorna o endereço IP do cliente.
     *
     * @return ?string O IP do cliente ou null.
     */
    public function ip(): ?string
    {
        $headers = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!empty($this->server[$header])) {
                $ips = explode(',', $this->server[$header]);
                $ip = trim($ips[0]);
                
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $this->server['REMOTE_ADDR'] ?? null;
    }

    /**
     * Retorna o user agent da requisição.
     *
     * @return ?string O user agent ou null.
     */
    public function userAgent(): ?string
    {
        return $this->server['HTTP_USER_AGENT'] ?? null;
    }

    /**
     * Retorna o host da requisição.
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->server['HTTP_HOST'] ?? $this->server['SERVER_NAME'] ?? 'localhost';
    }

    /**
     * Retorna a porta da requisição.
     *
     * @return int
     */
    public function getPort(): int
    {
        return (int) ($this->server['SERVER_PORT'] ?? 80);
    }

    /**
     * Retorna o esquema da requisição (http ou https).
     *
     * @return string
     */
    public function getScheme(): string
    {
        return $this->isSecure() ? 'https' : 'http';
    }

    /**
     * Verifica se a conexão é segura (HTTPS).
     *
     * @return bool
     */
    public function isSecure(): bool
    {
        return (
            (!empty($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off') ||
            (!empty($this->server['HTTP_X_FORWARDED_PROTO']) && $this->server['HTTP_X_FORWARDED_PROTO'] === 'https') ||
            (!empty($this->server['HTTP_X_FORWARDED_SSL']) && $this->server['HTTP_X_FORWARDED_SSL'] === 'on') ||
            $this->getPort() === 443
        );
    }

    /**
     * Verifica se o método da requisição corresponde ao fornecido.
     *
     * @param string $method O método HTTP a comparar.
     * @return bool
     */
    public function isMethod(string $method): bool
    {
        return $this->method() === strtoupper($method);
    }

    /**
     * Verifica se a requisição é AJAX.
     *
     * @return bool
     */
    public function isAjax(): bool
    {
        return strtolower($this->getHeader('X-Requested-With', '')) === 'xmlhttprequest';
    }

    /**
     * Verifica se a requisição é JSON.
     *
     * @return bool
     */
    public function isJson(): bool
    {
        $contentType = $this->getHeader('Content-Type', '');
        return str_contains(strtolower($contentType), 'application/json');
    }

    /**
     * Verifica se a requisição espera uma resposta JSON.
     *
     * @return bool
     */
    public function expectsJson(): bool
    {
        return $this->isAjax() || $this->wantsJson();
    }

    /**
     * Verifica se a requisição quer uma resposta JSON.
     *
     * @return bool
     */
    public function wantsJson(): bool
    {
        $acceptable = $this->getHeader('Accept', '');
        return str_contains(strtolower($acceptable), 'application/json');
    }

    /**
     * Obtém um cabeçalho HTTP específico.
     *
     * @param string $name Nome do cabeçalho
     * @param mixed $default Valor padrão se o cabeçalho não existir
     * @return mixed
     */
    public function getHeader(string $name, mixed $default = null): mixed
    {
        $name = str_replace('_', '-', strtoupper($name));
        return $this->headers[$name] ?? $default;
    }

    /**
     * Retorna todos os cabeçalhos.
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Verifica se um cabeçalho existe.
     *
     * @param string $name
     * @return bool
     */
    public function hasHeader(string $name): bool
    {
        $name = str_replace('_', '-', strtoupper($name));
        return isset($this->headers[$name]);
    }

    /**
     * Define um atributo na requisição.
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function setAttribute(string $key, mixed $value): self
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Obtém um atributo da requisição.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * Retorna todos os atributos.
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Remove um atributo.
     *
     * @param string $key
     * @return self
     */
    public function removeAttribute(string $key): self
    {
        unset($this->attributes[$key]);
        return $this;
    }

    /**
     * Parse dos cabeçalhos HTTP.
     *
     * @return void
     */
    private function parseHeaders(): void
    {
        foreach ($this->server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headerName = str_replace('_', '-', substr($key, 5));
                $this->headers[$headerName] = $value;
            }
        }

        // Adiciona cabeçalhos especiais que não começam com HTTP_
        $specialHeaders = [
            'CONTENT_TYPE' => 'CONTENT-TYPE',
            'CONTENT_LENGTH' => 'CONTENT-LENGTH',
            'CONTENT_MD5' => 'CONTENT-MD5'
        ];

        foreach ($specialHeaders as $serverKey => $headerName) {
            if (isset($this->server[$serverKey])) {
                $this->headers[$headerName] = $this->server[$serverKey];
            }
        }
    }

    /**
     * Parse do corpo da requisição baseado no Content-Type.
     *
     * @return void
     */
    private function parsedBody(): void
    {
        $input = file_get_contents('php://input');
        
        if (empty($input)) {
            $this->parsedBody = [];
            return;
        }

        $contentType = $this->getHeader('Content-Type', '');

        if (str_contains($contentType, 'application/json')) {
            $decoded = json_decode($input, true);
            $this->parsedBody = $decoded ?? [];
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidArgumentException('JSON inválido: ' . json_last_error_msg());
            }
        } elseif (str_contains($contentType, 'application/x-www-form-urlencoded')) {
            parse_str($input, $this->parsedBody);
        } elseif (str_contains($contentType, 'application/xml') || str_contains($contentType, 'text/xml')) {
            $this->parseXmlBody($input);
        } else {
            // Para outros tipos, armazena o conteúdo bruto
            $this->parsedBody = ['_raw' => $input];
        }
    }

    /**
     * Parse do corpo XML.
     *
     * @param string $input
     * @return void
     */
    private function parseXmlBody(string $input): void
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($input, 'SimpleXMLElement', LIBXML_NOCDATA);
        
        if ($xml !== false) {
            $this->parsedBody = json_decode(json_encode($xml), true);
        } else {
            $this->parsedBody = ['_raw' => $input];
        }
    }

    /**
     * Parse dos arquivos enviados.
     *
     * @return void
     */
    private function parseUploadedFiles(): void
    {
        $this->uploadedFiles = [];
        
        if (empty($_FILES)) {
            return;
        }

        foreach ($_FILES as $key => $file) {
            if (is_array($file['name'])) {
                $this->uploadedFiles[$key] = $this->normalizeNestedFiles($file);
            } else {
                $this->uploadedFiles[$key] = $this->normalizeFile($file);
            }
        }
    }

    /**
     * Normaliza um arquivo enviado.
     *
     * @param array $file
     * @return array
     */
    private function normalizeFile(array $file): array
    {
        return [
            'name' => $file['name'],
            'type' => $file['type'] ?? null,
            'size' => $file['size'],
            'tmp_name' => $file['tmp_name'],
            'error' => $file['error'],
            'extension' => pathinfo($file['name'], PATHINFO_EXTENSION),
            'is_valid' => $file['error'] === UPLOAD_ERR_OK,
            'mime_type' => $this->getFileMimeType($file['tmp_name']),
        ];
    }

    /**
     * Normaliza arquivos aninhados (múltiplos arquivos).
     *
     * @param array $files
     * @return array
     */
    private function normalizeNestedFiles(array $files): array
    {
        $normalized = [];
        
        foreach ($files['name'] as $index => $name) {
            $normalized[$index] = $this->normalizeFile([
                'name' => $name,
                'type' => $files['type'][$index] ?? null,
                'size' => $files['size'][$index],
                'tmp_name' => $files['tmp_name'][$index],
                'error' => $files['error'][$index],
            ]);
        }
        
        return $normalized;
    }

    /**
     * Obtém o MIME type de um arquivo.
     *
     * @param string $filePath
     * @return string|null
     */
    private function getFileMimeType(string $filePath): ?string
    {
        if (!file_exists($filePath)) {
            return null;
        }
        
        if (function_exists('finfo_file')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $filePath);
            finfo_close($finfo);
            return $mimeType ?: null;
        }
        
        return mime_content_type($filePath) ?: null;
    }

    /**
     * Obtém o corpo da requisição parseado.
     *
     * @return array
     */
    public function getParsedBody(): array
    {
        return $this->parsedBody;
    }

    /**
     * Obtém o conteúdo bruto da requisição.
     *
     * @return string
     */
    public function getRawBody(): string
    {
        return file_get_contents('php://input');
    }

    /**
     * Obtém dados do servidor.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function server(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->server;
        }
        
        return $this->server[$key] ?? $default;
    }

    /**
     * Valida se os campos obrigatórios estão presentes.
     *
     * @param array $required
     * @return array Lista de campos faltando
     */
    public function validate(array $required): array
    {
        $missing = [];
        
        foreach ($required as $field) {
            if (!$this->filled($field)) {
                $missing[] = $field;
            }
        }
        
        return $missing;
    }

    /**
     * Sanitiza um valor de entrada.
     *
     * @param string $key
     * @param string $filter Tipo de filtro ('string', 'email', 'url', 'int', 'float')
     * @param mixed $default
     * @return mixed
     */
    public function sanitize(string $key, string $filter = 'string', mixed $default = null): mixed
    {
        $value = $this->input($key, $default);
        
        if ($value === null) {
            return $default;
        }
        
        return match ($filter) {
            'string' => filter_var($value, FILTER_SANITIZE_STRING),
            'email' => filter_var($value, FILTER_SANITIZE_EMAIL),
            'url' => filter_var($value, FILTER_SANITIZE_URL),
            'int' => filter_var($value, FILTER_SANITIZE_NUMBER_INT),
            'float' => filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
            'html' => htmlspecialchars($value, ENT_QUOTES, 'UTF-8'),
            default => $value,
        };
    }

    /**
     * Obtém o referer da requisição.
     *
     * @param string|null $default
     * @return string|null
     */
    public function referer(?string $default = null): ?string
    {
        return $this->getHeader('Referer') ?? $default;
    }

    /**
     * Verifica se a requisição veio de uma origem específica.
     *
     * @param string|array $origins
     * @return bool
     */
    public function isFromOrigin(string|array $origins): bool
    {
        $referer = $this->referer();
        
        if (!$referer) {
            return false;
        }
        
        $origins = is_array($origins) ? $origins : [$origins];
        
        foreach ($origins as $origin) {
            if (str_starts_with($referer, $origin)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Verifica se a requisição é de um bot/crawler.
     *
     * @return bool
     */
    public function isBot(): bool
    {
        $userAgent = strtolower($this->userAgent() ?? '');
        $botSignatures = [
            'bot', 'crawl', 'spider', 'slurp', 'yahoo', 'google', 'bing',
            'facebook', 'twitter', 'whatsapp', 'telegram', 'baidu'
        ];
        
        foreach ($botSignatures as $signature) {
            if (str_contains($userAgent, $signature)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Obtém informações do dispositivo baseado no User-Agent.
     *
     * @return array
     */
    public function getDeviceInfo(): array
    {
        $userAgent = $this->userAgent() ?? '';
        
        $isMobile = preg_match('/Mobile|Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $userAgent);
        $isTablet = preg_match('/iPad|Android.*Tablet/i', $userAgent);
        $isDesktop = !$isMobile && !$isTablet;
        
        // Detecta o sistema operacional
        $os = 'Unknown';
        if (preg_match('/Windows NT/i', $userAgent)) $os = 'Windows';
        elseif (preg_match('/Mac OS X/i', $userAgent)) $os = 'macOS';
        elseif (preg_match('/Linux/i', $userAgent)) $os = 'Linux';
        elseif (preg_match('/Android/i', $userAgent)) $os = 'Android';
        elseif (preg_match('/iPhone|iPad|iPod/i', $userAgent)) $os = 'iOS';
        
        // Detecta o navegador
        $browser = 'Unknown';
        if (preg_match('/Chrome/i', $userAgent)) $browser = 'Chrome';
        elseif (preg_match('/Firefox/i', $userAgent)) $browser = 'Firefox';
        elseif (preg_match('/Safari/i', $userAgent)) $browser = 'Safari';
        elseif (preg_match('/Edge/i', $userAgent)) $browser = 'Edge';
        elseif (preg_match('/Opera/i', $userAgent)) $browser = 'Opera';
        
        return [
            'is_mobile' => (bool) $isMobile,
            'is_tablet' => (bool) $isTablet,
            'is_desktop' => $isDesktop,
            'os' => $os,
            'browser' => $browser,
            'user_agent' => $userAgent,
        ];
    }

    /**
     * Verifica se a requisição é de um dispositivo móvel.
     *
     * @return bool
     */
    public function isMobile(): bool
    {
        return $this->getDeviceInfo()['is_mobile'];
    }

    /**
     * Verifica se a requisição é de um tablet.
     *
     * @return bool
     */
    public function isTablet(): bool
    {
        return $this->getDeviceInfo()['is_tablet'];
    }

    /**
     * Verifica se a requisição é de um desktop.
     *
     * @return bool
     */
    public function isDesktop(): bool
    {
        return $this->getDeviceInfo()['is_desktop'];
    }

    /**
     * Obtém as linguagens aceitas pelo cliente.
     *
     * @return array
     */
    public function getAcceptableLanguages(): array
    {
        $languages = [];
        $acceptLanguage = $this->getHeader('Accept-Language', '');
        
        if (!$acceptLanguage) {
            return $languages;
        }
        
        $parts = explode(',', $acceptLanguage);
        
        foreach ($parts as $part) {
            $part = trim($part);
            if (preg_match('/([a-z-]+)(?:;q=([0-9.]+))?/i', $part, $matches)) {
                $language = strtolower($matches[1]);
                $quality = isset($matches[2]) ? (float) $matches[2] : 1.0;
                $languages[$language] = $quality;
            }
        }
        
        arsort($languages);
        return array_keys($languages);
    }

    /**
     * Obtém a linguagem preferida do cliente.
     *
     * @param array $available Linguagens disponíveis
     * @return string|null
     */
    public function getPreferredLanguage(array $available = []): ?string
    {
        $acceptable = $this->getAcceptableLanguages();
        
        if (empty($available)) {
            return $acceptable[0] ?? null;
        }
        
        foreach ($acceptable as $language) {
            if (in_array($language, $available)) {
                return $language;
            }
            
            // Verifica idioma base (ex: 'pt' para 'pt-BR')
            $baseLang = substr($language, 0, 2);
            if (in_array($baseLang, $available)) {
                return $baseLang;
            }
        }
        
        return null;
    }

    /**
     * Cria uma nova instância de Request a partir dos dados globais atuais.
     *
     * @param array $params
     * @return self
     */
    public static function createFromGlobals(array $params = []): self
    {
        return new self($params);
    }

    /**
     * Cria uma nova instância de Request para testes.
     *
     * @param string $method
     * @param string $uri
     * @param array $data
     * @param array $headers
     * @return self
     */
    public static function create(
        string $method = 'GET',
        string $uri = '/',
        array $data = [],
        array $headers = []
    ): self {
        $request = new self();
        
        // Simula dados do servidor para teste
        $request->server = array_merge($_SERVER, [
            'REQUEST_METHOD' => strtoupper($method),
            'REQUEST_URI' => $uri,
            'PATH_INFO' => parse_url($uri, PHP_URL_PATH),
        ]);
        
        // Define headers
        foreach ($headers as $name => $value) {
            $headerKey = 'HTTP_' . str_replace('-', '_', strtoupper($name));
            $request->server[$headerKey] = $value;
        }
        
        // Define dados baseado no método
        if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'])) {
            $request->parsedBody = $data;
        } else {
            $request->queryParams = $data;
        }
        
        $request->parseHeaders();
        
        return $request;
    }

    /**
     * Converte a requisição para array (útil para debugging).
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'method' => $this->method(),
            'uri' => $this->uri(),
            'full_uri' => $this->fullUri(),
            'url' => $this->url(),
            'query_string' => $this->queryString(),
            'is_secure' => $this->isSecure(),
            'is_ajax' => $this->isAjax(),
            'is_json' => $this->isJson(),
            'ip' => $this->ip(),
            'user_agent' => $this->userAgent(),
            'host' => $this->getHost(),
            'params' => $this->params,
            'query_params' => $this->queryParams,
            'post_data' => $_POST,
            'parsed_body' => $this->parsedBody,
            'headers' => $this->headers,
            'cookies' => $_COOKIE,
            'files' => $this->uploadedFiles,
            'attributes' => $this->attributes,
            'device_info' => $this->getDeviceInfo(),
        ];
    }

    /**
     * Debug da requisição (retorna informações formatadas).
     *
     * @return string
     */
    public function debug(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}