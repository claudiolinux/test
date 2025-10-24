<?php
/*
|--------------------------------------------------------------------------
| Classe Command
|--------------------------------------------------------------------------
|
| Esta classe abstrata fornece funcionalidades básicas para a CLI do Slenix,
| incluindo métodos para exibir mensagens coloridas no terminal e comandos
| de ajuda e versão. Serve como base para outros comandos específicos.
|
*/

declare(strict_types=1);

namespace Slenix\Commands;

use Slenix\Helpers\Console;

abstract class Command {
    
    /**
     * @var string Versão da CLI.
     */
    protected static string $version = '1.0';

    /**
     * @var string Arte ASCII para exibição no terminal.
     */
    protected const ASCII_ART = <<<EOT
    ╔═╗╦  ╔═╗╦═╗╦╔═╗╦╔═╗╦═╗
    ╚═╗║  ║  ╠╦╝║╚═╗║╠═╣╠╦╝
    ╚═╝╩═╝╚═╝╩╚═╩╚═╝╩╩ ╩╩╚═
    EOT;

    /**
     * Cria uma instância do Console para formatação de saída.
     *
     * @return Console
     */
    protected static function console(): Console
    {
        return new Console();
    }

    /**
     * Exibe uma mensagem de erro no terminal.
     *
     * @param string $message
     * @return void
     */
    public static function error(string $message): void
    {
        $console = self::console();
        echo $console->colorize('[✗] ', 'red') . $console->colorize($message, 'red') . PHP_EOL;
    }

    /**
     * Exibe uma mensagem de aviso no terminal.
     *
     * @param string $message
     * @return void
     */
    public static function warning(string $message): void
    {
        $console = self::console();
        echo $console->colorize('[!] ', 'yellow') . $console->colorize($message, 'yellow') . PHP_EOL;
    }

    /**
     * Exibe uma mensagem de sucesso no terminal.
     *
     * @param string $message
     * @return void
     */
    public static function success(string $message): void
    {
        $console = self::console();
        echo $console->colorize('[✔] ', 'green') . $console->colorize($message, 'green') . PHP_EOL;
    }

    /**
     * Exibe uma mensagem informativa no terminal.
     *
     * @param string $message
     * @return void
     */
    public static function info(string $message): void
    {
        $console = self::console();
        echo $console->colorize('[ℹ] ', 'cyan') . $console->colorize($message, 'cyan') . PHP_EOL;
    }

    /**
     * Exibe a versão da CLI no terminal.
     *
     * @return void
     */
    public static function version(): void
    {
        $console = self::console();
        echo self::ASCII_ART . PHP_EOL;
        echo $console->colorize("Slenix CLI v" . self::$version, 'purple') . PHP_EOL;
        echo $console->colorize('Desenvolvido com ♥ para o ecossistema Slenix', 'white') . PHP_EOL;
    }

    /**
     * Exibe a ajuda com a lista de comandos disponíveis.
     *
     * @return void
     */
    public static function help(): void
    {
        $console = self::console();
        echo self::ASCII_ART . PHP_EOL;
        echo $console->colorize("Slenix CLI v" . self::$version, 'purple') . PHP_EOL;
        echo $console->colorize('Ferramenta de desenvolvimento para o framework Slenix', 'white') . PHP_EOL . PHP_EOL;

        echo $console->colorize('Uso:', 'white', true) . PHP_EOL;
        echo "  php celestial <comando> [opções]" . PHP_EOL . PHP_EOL;

        echo $console->colorize('Comandos disponíveis:', 'white', true) . PHP_EOL;
        echo "  " . $console->colorize('make:scaffold <api|full>', 'green') . " Configura o projeto como API ou full-stack" . PHP_EOL;
        echo "  " . $console->colorize('make:model <nome>', 'green') . "      Cria um novo model" . PHP_EOL;
        echo "  " . $console->colorize('make:controller <nome>', 'green') . " Cria um novo controller" . PHP_EOL;
        echo "  " . $console->colorize('make:middleware <nome>', 'green') . " Cria um novo middleware" . PHP_EOL;
        echo "  " . $console->colorize('serve [porta]', 'green') . "         Inicia o servidor de desenvolvimento" . PHP_EOL;
        echo "  " . $console->colorize('route:list', 'green') . "            Lista todas as rotas registradas" . PHP_EOL;
        echo "  " . $console->colorize('help', 'green') . "                 Exibe esta ajuda" . PHP_EOL;
        echo "  " . $console->colorize('version', 'green') . "              Exibe a versão da CLI" . PHP_EOL . PHP_EOL;

        echo $console->colorize('Exemplos:', 'white', true) . PHP_EOL;
        echo "  " . $console->colorize('php celestial make:scaffold api', 'cyan') . PHP_EOL;
        echo "  " . $console->colorize('php celestial make:model User', 'cyan') . PHP_EOL;
        echo "  " . $console->colorize('php celestial make:controller Home', 'cyan') . PHP_EOL;
        echo "  " . $console->colorize('php celestial serve 8000', 'cyan') . PHP_EOL;
    }
}