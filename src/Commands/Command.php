<?php
/*
|--------------------------------------------------------------------------
| Classe Command
|--------------------------------------------------------------------------
|
| Classe base para todos os comandos da CLI do Slenix.
| Fornece métodos utilitários para exibir mensagens no terminal e acessar
| funcionalidades do console.
|
*/

declare(strict_types=1);

namespace Slenix\Commands;

use Slenix\Helpers\Console;

/**
 * Classe base para comandos da CLI do Slenix.
 */
abstract class Command
{
    /**
     * Instância do Console para interação com o terminal.
     *
     * @var Console
     */
    protected static Console $console;

    /**
     * Exibe uma mensagem de informação no terminal.
     *
     * @param string $message Mensagem a ser exibida.
     * @return void
     */
    public static function info(string $message): void
    {
        echo self::console()->colorize("[ℹ] $message", 'green') . PHP_EOL;
    }

    /**
     * Exibe uma mensagem de erro no terminal.
     *
     * @param string $message Mensagem a ser exibida.
     * @return void
     */
    public static function error(string $message): void
    {
        echo self::console()->colorize("[✗] $message", 'red') . PHP_EOL;
    }

    /**
     * Exibe uma mensagem de sucesso no terminal.
     *
     * @param string $message Mensagem a ser exibida.
     * @return void
     */
    public static function success(string $message): void
    {
        echo self::console()->colorize("[✔] $message", 'green') . PHP_EOL;
    }

    /**
     * Exibe uma mensagem de aviso no terminal.
     *
     * @param string $message Mensagem a ser exibida.
     * @return void
     */
    public static function warning(string $message): void
    {
        echo self::console()->colorize("[⚠] $message", 'yellow') . PHP_EOL;
    }

    /**
     * Retorna a instância do Console.
     *
     * @return Console
     */
    public static function console(): Console
    {
        if (!isset(self::$console)) {
            self::$console = new Console();
        }
        return self::$console;
    }

    /**
     * Executa o comando.
     *
     * @return void
     */
    abstract public function execute(): void;

    /**
     * Exibe a ajuda da CLI.
     *
     * @return void
     */
    public static function help(): void
    {
        $console = new Console();
        echo $console->colorize("╔═╗╦  ╔═╗╦═╗╦╔═╗╦╔═╗╦═╗\n", 'cyan');
        echo $console->colorize("╚═╗║  ║  ╠╦╝║╚═╗║╠═╣╠╦╝\n", 'cyan');
        echo $console->colorize("╚═╝╩═╝╚═╝╩╚═╩╚═╝╩╩ ╩╩╚═\n", 'cyan');
        echo $console->colorize("Slenix CLI v1.0\n", 'green');
        echo "Ferramenta de desenvolvimento para o framework Slenix\n\n";
        echo "Uso:\n  php celestial <comando> [opções]\n\n";
        echo "Comandos disponíveis:\n";
        echo "  make:scaffold <api|full> Configura o projeto como API ou full-stack\n";
        echo "  make:model <nome>      Cria um novo model\n";
        echo "  make:controller <nome> Cria um novo controller\n";
        echo "  make:middleware <nome> Cria um novo middleware\n";
        echo "  serve [porta]         Inicia o servidor de desenvolvimento\n";
        echo "  route:list            Lista todas as rotas registradas\n";
        echo "  help                 Exibe esta ajuda\n";
        echo "  version              Exibe a versão da CLI\n";
        echo "\nExemplos:\n";
        echo "  php celestial make:scaffold api\n";
        echo "  php celestial make:model User\n";
        echo "  php celestial make:controller Home\n";
        echo "  php celestial serve 8000\n";
    }

    /**
     * Exibe a versão da CLI.
     *
     * @return void
     */
    public static function version(): void
    {
        echo "Slenix CLI v1.0\n";
    }
}