<?php
/*
|--------------------------------------------------------------------------
| Classe ScaffoldCommand
|--------------------------------------------------------------------------
|
| Esta classe implementa o comando 'make:scaffold' para a CLI do Slenix.
| Ele permite configurar o projeto como 'api' (para APIs REST) ou 'full'
| (para aplicações web completas), ajustando arquivos e configurações
| conforme necessário.
|
*/

declare(strict_types=1);

namespace Slenix\Commands;

use Slenix\Http\Message\Request;
use Slenix\Http\Message\Response;

/**
 * Comando para configurar o scaffold do projeto Slenix.
 */
class ScaffoldCommand extends Command
{
    /**
     * @var array Argumentos passados para o comando.
     */
    private array $args;

    /**
     * Construtor da classe ScaffoldCommand.
     *
     * @param array $args Argumentos da linha de comando.
     */
    public function __construct(array $args)
    {
        $this->args = $args;
    }

    /**
     * Executa o comando de configuração do scaffold.
     *
     * Pergunta ao usuário o tipo de projeto (api ou full) e configura
     * o projeto adequadamente, removendo arquivos desnecessários no modo API.
     *
     * @return void
     */
    public function execute(): void
    {
        $type = env('APP_MODE', false) ?: ($this->args[2] ?? self::console()->ask('Project type? (api/full) ', 'full'));

        if (!in_array($type, ['api', 'full'])) {
            self::error('Invalid type. Use "api" or "full".');
            exit(1);
        }

        self::info("Setting up project as: $type");

        if ($type === 'api') {
            $this->setupApiScaffold();
        } else {
            $this->setupFullScaffold();
        }

        self::success("Project configured as '$type' successfully!");
    }

    /**
     * Configura o projeto no modo API.
     *
     * Remove arquivos e pastas desnecessários (views, templates, sessão, CSRF, upload)
     * e cria um arquivo de rotas otimizado para APIs.
     *
     * @return void
     */
    private function setupApiScaffold(): void
    {
        // Definir caminhos absolutos
        $rootPath = dirname(__DIR__, 2); // Raiz do projeto (/var/www/tests/api-rest)
        $viewsPath = $rootPath . '/views';
        $srcPath = $rootPath . '/src';
        $routesPath = $rootPath . '/routes';

        // Remove unnecessary directories/files
        $this->removeDir($viewsPath);
        $this->removeFile($srcPath . '/Libraries/Template.php');
        $this->removeFile($srcPath . '/Libraries/Session.php');
        $this->removeFile($srcPath . '/Http/Auth/Csrf.php');
        $this->removeFile($srcPath . '/Http/Message/Upload.php');
        $this->removeFile($routesPath . '/web.php');

        // Create routes/api.php
        $apiRoutesPath = $routesPath . '/api.php';
        if (!file_exists($apiRoutesPath)) {
            if (!is_dir($routesPath)) {
                mkdir($routesPath, 0755, true);
            }
            $template = <<<EOT
<?php
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| This file defines the routes for REST APIs in the Slenix framework.
| Routes are optimized for JSON responses and JWT authentication.
|
*/

use Slenix\Http\Message\Router;
use Slenix\Http\Message\Request;
use Slenix\Http\Message\Response;

Router::get('/welcome', function(Request \$request, Response \$response) {
    return \$response->json(['message' => 'Welcome to Slenix API!']);
});
EOT;
            if (file_put_contents($apiRoutesPath, $template) === false) {
                self::error("Failed to create routes/api.php");
                exit(1);
            }
            self::info("Created routes/api.php");
        }

        // Update .env
        $this->updateEnvFile('APP_MODE', 'api');
    }

    /**
     * Configura o projeto no modo full-stack.
     *
     * Garante que o modo full esteja definido no .env.
     *
     * @return void
     */
    private function setupFullScaffold(): void
    {
        $this->updateEnvFile('APP_MODE', 'full');
    }

    /**
     * Remove um diretório e seu conteúdo recursivamente.
     *
     * @param string $dir Caminho do diretório.
     * @return void
     */
    private function removeDir(string $dir): void
    {
        if (is_dir($dir)) {
            $this->rrmdir($dir);
            self::info("Removed directory: $dir");
        } else {
            self::warning("Directory not found: $dir");
        }
    }

    /**
     * Remove um arquivo.
     *
     * @param string $file Caminho do arquivo.
     * @return void
     */
    private function removeFile(string $file): void
    {
        if (file_exists($file)) {
            unlink($file);
            self::info("Removed file: $file");
        } else {
            self::warning("File not found: $file");
        }
    }

    /**
     * Remove um diretório recursivamente.
     *
     * @param string $dir Caminho do diretório.
     * @return void
     */
    private function rrmdir(string $dir): void
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    $path = "$dir/$object";
                    is_dir($path) ? $this->rrmdir($path) : unlink($path);
                }
            }
            rmdir($dir);
        }
    }

    /**
     * Atualiza ou adiciona uma variável no arquivo .env.
     *
     * @param string $key Nome da variável.
     * @param string $value Valor da variável.
     * @return void
     */
    private function updateEnvFile(string $key, string $value): void
    {
        $rootPath = dirname(__DIR__, 2);
        $envFile = $rootPath . '/.env';
        $lines = file_exists($envFile) ? file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
        $newLines = [];
        $found = false;

        foreach ($lines as $line) {
            if (strpos($line, "$key=") === 0) {
                $newLines[] = "$key=$value";
                $found = true;
            } else {
                $newLines[] = $line;
            }
        }

        if (!$found) {
            $newLines[] = "$key=$value";
        }

        if (file_put_contents($envFile, implode(PHP_EOL, $newLines) . PHP_EOL) === false) {
            self::error("Failed to update .env file");
            exit(1);
        }
        self::info("Updated .env: $key=$value");
    }
}