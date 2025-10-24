<?php
/*
|--------------------------------------------------------------------------
| Classe Kernel
|--------------------------------------------------------------------------
|
| O Kernel da aplicação é responsável por gerenciar o ciclo de vida da
| aplicação, desde a inicialização até o despacho da requisição e
| o tratamento de erros. Ele suporta modos 'api' e 'full' para configurar
| scaffolds otimizados para APIs REST ou aplicações web completas.
|
*/

declare(strict_types=1);

namespace Slenix\Core;

use Slenix\Libraries\EnvLoad;
use Slenix\Http\Message\Router;
use Slenix\Libraries\Session;
use Slenix\Exceptions\ErrorHandler;

/**
 * Classe principal que gerencia o ciclo de vida da aplicação Slenix.
 */
class Kernel
{
    /**
     * @var float Armazena o timestamp de quando a aplicação foi iniciada.
     */
    private float $startTime;

    /**
     * @var string Modo da aplicação ('api' ou 'full').
     */
    private string $appMode;

    /**
     * Construtor da classe Kernel.
     *
     * @param float $startTime O timestamp de quando a aplicação foi iniciada.
     */
    public function __construct(float $startTime)
    {
        $this->startTime = $startTime;
        $this->appMode = env('APP_MODE', 'full'); // Define modo padrão como 'full'
    }

    /**
     * Inicia e executa a aplicação.
     *
     * Configura manipuladores de erro, carrega variáveis de ambiente,
     * inicia a sessão (se necessário) e despacha a requisição.
     *
     * @return void
     */
    public function run(): void
    {
        $errorHandler = new ErrorHandler();

        // Configura manipuladores de erro globais
        set_error_handler([$errorHandler, 'handleError']);
        set_exception_handler([$errorHandler, 'handleException']);

        // Carrega sessão apenas no modo 'full'
        if ($this->appMode === 'full') {
            Session::start();
            class_alias(Session::class, 'Session');
        }

        // Carrega variáveis de ambiente
        try {
            EnvLoad::load(__DIR__ . '/../../.env');
        } catch (\Exception $e) {
            $errorHandler->handleEnvError($e);
        }

        // Carrega rotas com base no modo
        $routesFile = $this->appMode === 'api' ? ROUTES_PATH . '/api.php' : ROUTES_PATH . '/web.php';
        if (file_exists($routesFile)) {
            require_once $routesFile;
        } else {
            $errorHandler->handleException(new \Exception("Arquivo de rotas não encontrado: $routesFile"));
        }

        // Despacha a requisição
        Router::dispatch();
    }
}