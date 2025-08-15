<?php

declare(strict_types=1);

use Slenix\Libraries\Template;
use Slenix\Http\Message\Router;
use Slenix\Libraries\Session;

/*                                            
|--------------------------------------------|
|****** HELPERS GERAIS E CONSTANTES v1 ******|
|--------------------------------------------|
*/

define('BASE_PATH', __DIR__);
define('SLENIX_START', microtime(true));
define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', dirname(__DIR__) . DS);
define('PUBLIC_PATH', ROOT_PATH . '../public' . DS);
define('APP_PATH', ROOT_PATH . '../app' . DS);

/*                                            
|--------------------------------------------|
|****** FUNÇÕES PARA MANIPULAR STRINGS ******|
|--------------------------------------------|
*/

if (!function_exists('sanetize')):
    function sanetize(string $string): string {
        return trim(htmlspecialchars($string, ENT_QUOTES, 'UTF-8'));
    }
endif;

if (!function_exists('validate')):
    function validate(string $string): mixed {
        return preg_match('/^[A-Za-zÀ-ÖØ-öø-ÿ\s]+$/u', $string);
    }
endif;

if (!function_exists('camel_case')) {
    function camel_case(string $string)
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $string))));
    }
}

if (!function_exists('snake_case')) {
    function snake_case(string $string, string $delimiter = '_')
    {
        if (!ctype_lower($string)) {
            $string = preg_replace('/\s+/u', '', $string);
            $string = mb_strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $string), 'UTF-8');
        }
        return $string;
    }
}

if (!function_exists('str_default')) {
    function str_default(?string $string, string $default)
    {
        return empty($string) ? $default : $string;
    }
}

if (!function_exists('limit')):
    function limit($text, $limit): string {
        return (strlen($text) >= $limit) ? substr($text, 0, $limit).'...' : $text;
    }
endif;

/*                                            
|--------------------------------------------|
|****** FUNÇÕES PARA MANIPULAR O LUNA *******|
|--------------------------------------------|
*/

if (!function_exists('env')):
    function env(string $key, mixed $default = null): string|int|bool|null {
        return $_ENV[$key] ?? getenv($key) ?? $default;
    }
endif;

if (!function_exists('view')):
    function view(string $template, array $data = []) {
        $view_template = new Template($template, $data);
        echo $view_template->render();
    }
endif;

if (!function_exists('route')):
    /**
     * Gera a URL para uma rota nomeada.
     *
     * @param string $name O nome da rota.
     * @param array $params Parâmetros para substituir na URL.
     * @return string|null A URL gerada ou null se a rota não for encontrada.
     * @throws \Exception Se parâmetros obrigatórios estiverem faltando.
     */
    function route(string $name, array $params = []): ?string {
        return Router::route($name, $params);
    }
endif;

if (!function_exists('old')) {
    /**
     * Retorna o valor antigo de um campo de formulário.
     */
    function old(string $key, mixed $default = null): string {
        $value = \Slenix\Libraries\Session::getFlash('_old_input_' . $key, $default);
        return (string) ($value ?? '');
    }
}

Template::share('route', function (string $name, array $params = []): ?string {
    return Router::route($name, $params);
});


Template::share('Session', [
    'has' => function (string $key): bool {
        return Session::has($key);
    },
    'get' => function (string $key, mixed $default = null): mixed {
        return Session::get($key, $default);
    },
    'set' => function (string $key, mixed $value): void {
        Session::set($key, $value);
    },
    'flash' => function (string $key, mixed $value): void {
        Session::flash($key, $value);
    },
    'getFlash' => function (string $key, mixed $default = null): mixed {
        return Session::getFlash($key, $default);
    },
    'hasFlash' => function (string $key): bool {
        return Session::hasFlash($key);
    },
    'remove' => function (string $key): void {
        Session::remove($key);
    },
    'destroy' => function (): void {
        Session::destroy();
    }
]);