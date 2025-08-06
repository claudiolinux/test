<?php
/*
|--------------------------------------------------------------------------
| Classe HttpClient
|--------------------------------------------------------------------------
|
| Esta classe fornece uma interface fluida e robusta para realizar requisições HTTP,
| com suporte a métodos HTTP, autenticação, cabeçalhos personalizados, corpo da requisição,
| retries, timeouts, e integração com o framework Slenix.
|
*/
declare(strict_types=1);

namespace Slenix\Http\Message;

use Exception;
use RuntimeException;
use Slenix\Http\Message\Request;
use Slenix\Http\Message\Response;

class HttpClient
{
    /**
     * @var array Configurações padrão para a requisição
     */
    protected array $options = [
        'timeout' => 30,
        'connect_timeout' => 5,
        'verify' => true,
        'http_errors' => true,
        'retries' => 0,
        'retry_delay' => 1000, // em milissegundos
    ];

    /**
     * @var array Cabeçalhos da requisição
     */
    protected array $headers = [];

    /**
     * @var array Dados do corpo da requisição
     */
    protected array $body = [];

    /**
     * @var string|null URL base para as requisições
     */
    protected ?string $baseUrl = null;

    /**
     * @var string|null Método HTTP
     */
    protected ?string $method = null;

    /**
     * @var string|null URL da requisição
     */
    protected ?string $url = null;

    /**
     * @var array|null Dados de autenticação
     */
    protected ?array $auth = null;

    /**
     * @var array Eventos de callback (before, after, error)
     */
    protected array $events = [];

    /**
     * Construtor da classe HttpClient.
     *
     * @param array $options Configurações iniciais
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Define a URL base para as requisições.
     *
     * @param string $baseUrl
     * @return self
     */
    public function baseUrl(string $baseUrl): self
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        return $this;
    }

    /**
     * Define um cabeçalho para a requisição.
     *
     * @param string $name
     * @param string $value
     * @return self
     */
    public function withHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Define múltiplos cabeçalhos.
     *
     * @param array $headers
     * @return self
     */
    public function withHeaders(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    /**
     * Define autenticação para a requisição (Basic Auth, Bearer Token, etc.).
     *
     * @param string $type Tipo de autenticação ('basic', 'bearer')
     * @param string|array $credentials Credenciais (usuário/senha ou token)
     * @return self
     */
    public function withAuth(string $type, $credentials): self
    {
        $this->auth = ['type' => strtolower($type), 'credentials' => $credentials];
        return $this;
    }

    /**
     * Define o corpo da requisição como JSON.
     *
     * @param array $data
     * @return self
     */
    public function asJson(array $data): self
    {
        $this->body = $data;
        $this->withHeader('Content-Type', 'application/json');
        return $this;
    }

    /**
     * Define o corpo da requisição como formulário (multipart/form-data).
     *
     * @param array $data
     * @return self
     */
    public function asForm(array $data): self
    {
        $this->body = $data;
        $this->withHeader('Content-Type', 'multipart/form-data');
        return $this;
    }

    /**
     * Define o corpo da requisição como URL-encoded.
     *
     * @param array $data
     * @return self
     */
    public function asFormUrlEncoded(array $data): self
    {
        $this->body = $data;
        $this->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        return $this;
    }

    /**
     * Define o número de tentativas de repetição em caso de falha.
     *
     * @param int $retries
     * @param int $delay Delay entre tentativas (em milissegundos)
     * @return self
     */
    public function withRetries(int $retries, int $delay = 1000): self
    {
        $this->options['retries'] = $retries;
        $this->options['retry_delay'] = $delay;
        return $this;
    }

    /**
     * Define o tempo limite da requisição.
     *
     * @param int $timeout
     * @return self
     */
    public function timeout(int $timeout): self
    {
        $this->options['timeout'] = $timeout;
        return $this;
    }

    /**
     * Registra um callback para um evento (before, after, error).
     *
     * @param string $event
     * @param callable $callback
     * @return self
     */
    public function on(string $event, callable $callback): self
    {
        $this->events[$event][] = $callback;
        return $this;
    }

    /**
     * Executa uma requisição GET.
     *
     * @param string $url
     * @param array $query
     * @return Response
     */
    public function get(string $url, array $query = []): Response
    {
        $this->method = 'GET';
        $this->url = $this->buildUrl($url, $query);
        return $this->send();
    }

    /**
     * Executa uma requisição POST.
     *
     * @param string $url
     * @param array $data
     * @return Response
     */
    public function post(string $url, array $data = []): Response
    {
        $this->method = 'POST';
        $this->url = $this->buildUrl($url);
        $this->body = $data;
        return $this->send();
    }

    /**
     * Executa uma requisição PUT.
     *
     * @param string $url
     * @param array $data
     * @return Response
     */
    public function put(string $url, array $data = []): Response
    {
        $this->method = 'PUT';
        $this->url = $this->buildUrl($url);
        $this->body = $data;
        return $this->send();
    }

    /**
     * Executa uma requisição PATCH.
     *
     * @param string $url
     * @param array $data
     * @return Response
     */
    public function patch(string $url, array $data = []): Response
    {
        $this->method = 'PATCH';
        $this->url = $this->buildUrl($url);
        $this->body = $data;
        return $this->send();
    }

    /**
     * Executa uma requisição DELETE.
     *
     * @param string $url
     * @return Response
     */
    public function delete(string $url): Response
    {
        $this->method = 'DELETE';
        $this->url = $this->buildUrl($url);
        return $this->send();
    }

    /**
     * Constrói a URL completa com base na baseUrl e parâmetros de query.
     *
     * @param string $url
     * @param array $query
     * @return string
     */
    protected function buildUrl(string $url, array $query = []): string
    {
        $base = $this->baseUrl ? rtrim($this->baseUrl, '/') . '/' : '';
        $url = $base . ltrim($url, '/');
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }
        return $url;
    }

    /**
     * Executa a requisição HTTP.
     *
     * @return Response
     * @throws Exception
     */
    protected function send(): Response
    {
        $attempts = 0;
        $maxAttempts = $this->options['retries'] + 1;

        while ($attempts < $maxAttempts) {
            try {
                // Dispara evento 'before'
                $this->dispatchEvent('before', [$this->method, $this->url, $this->body]);

                $ch = curl_init();
                curl_setopt_array($ch, $this->buildCurlOptions());

                $responseContent = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                $errno = curl_errno($ch);
                curl_close($ch);

                if ($responseContent === false) {
                    throw new RuntimeException("cURL error ($errno): $error");
                }

                $response = new Response();
                $response->status($httpCode);

                $contentType = $this->headers['Content-Type'] ?? 'text/plain';
                if (stripos($contentType, 'application/json') !== false) {
                    $response->json(json_decode($responseContent, true), $httpCode);
                } else {
                    $response->write($responseContent, $httpCode);
                }

                // Dispara evento 'after'
                $this->dispatchEvent('after', [$response]);

                return $response;
            } catch (Exception $e) {
                $attempts++;
                if ($attempts >= $maxAttempts) {
                    // Dispara evento 'error'
                    $this->dispatchEvent('error', [$e]);
                    throw $e;
                }
                usleep($this->options['retry_delay'] * 1000);
            }
        }

        throw new RuntimeException('Failed to execute request after retries.');
    }

    /**
     * Constrói as opções do cURL.
     *
     * @return array
     */
    protected function buildCurlOptions(): array
    {
        $options = [
            CURLOPT_URL => $this->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_TIMEOUT => $this->options['timeout'],
            CURLOPT_CONNECTTIMEOUT => $this->options['connect_timeout'],
            CURLOPT_SSL_VERIFYPEER => $this->options['verify'],
            CURLOPT_FOLLOWLOCATION => true,
        ];

        // Define o método HTTP
        switch (strtoupper($this->method)) {
            case 'POST':
                $options[CURLOPT_POST] = true;
                break;
            case 'PUT':
            case 'PATCH':
            case 'DELETE':
                $options[CURLOPT_CUSTOMREQUEST] = strtoupper($this->method);
                break;
        }

        if (!empty($this->body)) {
            if (isset($this->headers['Content-Type']) && $this->headers['Content-Type'] === 'application/json') {
                $options[CURLOPT_POSTFIELDS] = json_encode($this->body);
            } elseif (isset($this->headers['Content-Type']) && $this->headers['Content-Type'] === 'multipart/form-data') {
                $options[CURLOPT_POSTFIELDS] = $this->body;
            } else {
                $options[CURLOPT_POSTFIELDS] = http_build_query($this->body);
            }
        }

        if ($this->auth) {
            if ($this->auth['type'] === 'basic') {
                $options[CURLOPT_USERPWD] = $this->auth['credentials'][0] . ':' . $this->auth['credentials'][1];
            } elseif ($this->auth['type'] === 'bearer') {
                $this->headers['Authorization'] = 'Bearer ' . $this->auth['credentials'];
            }
        }

        if (!empty($this->headers)) {
            $headers = [];
            foreach ($this->headers as $name => $value) {
                $headers[] = "$name: $value";
            }
            $options[CURLOPT_HTTPHEADER] = $headers;
        }

        return $options;
    }

    /**
     * Dispara os callbacks de um evento.
     *
     * @param string $event
     * @param array $params
     * @return void
     */
    protected function dispatchEvent(string $event, array $params): void
    {
        if (isset($this->events[$event])) {
            foreach ($this->events[$event] as $callback) {
                call_user_func_array($callback, $params);
            }
        }
    }

    /**
     * Método estático para criar uma nova instância do cliente.
     *
     * @param array $options
     * @return self
     */
    public static function make(array $options = []): self
    {
        return new self($options);
    }
}