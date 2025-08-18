<?php

declare(strict_types=1);

namespace Slenix\Commands;

class ServeCommand extends Command
{
    private array $args;
    private const DEFAULT_PORT = 8080;

    public function __construct(array $args)
    {
        $this->args = $args;
    }

    public function execute(): void
    {
        $port = $this->args[2] ?? self::DEFAULT_PORT;

        if (!is_numeric($port) || $port < 1 || $port > 65535) {
            self::error('Porta inválida. Use um número entre 1 e 65535.');
            exit(1);
        }

        $host = '127.0.0.1';
        $publicDir = __DIR__ . '/../../public';

        if (!is_dir($publicDir)) {
            self::warning('Diretório público não encontrado. Criando...');
            if (!mkdir($publicDir, 0755, true)) {
                self::error('Não foi possível criar o diretório público.');
                exit(1);
            }
        }

        echo self::ASCII_ART . PHP_EOL;
        self::success('Servidor Slenix iniciado!');
        self::info("URL: http://{$host}:{$port}");
        self::info("Diretório: {$publicDir}");
        self::info('Pressione Ctrl+C para parar.');

        passthru("php -S {$host}:{$port} -t {$publicDir}");
    }
}