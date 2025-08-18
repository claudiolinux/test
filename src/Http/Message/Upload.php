<?php
/*
 |--------------------------------------------------------------------------
 | Classe Upload
 |--------------------------------------------------------------------------
 |
 | Gerencia o upload de arquivos, fornecendo métodos para acessar
 | as informações do arquivo e movê-lo para um diretório de destino,
 | com validações e tratamento de erros aprimorados.
 |
 */

declare(strict_types=1);

namespace Slenix\Http\Message;

use Slenix\Exceptions\UploadException;

class Upload
{
    /**
     * @var array<string, mixed> Armazena os dados do arquivo de upload.
     */
    protected array $file = [];

    /**
     * @var int O tamanho máximo permitido para o arquivo em bytes.
     */
    protected int $maxSize = 10485760; // 10MB por padrão

    /**
     * @var array<string> Tipos MIME permitidos.
     */
    protected array $allowedMimeTypes = [];

    /**
     * Construtor da classe Upload.
     *
     * @param array<string, mixed> $file O array `$_FILES` contendo os dados do arquivo.
     * @param array<string> $allowedMimeTypes Tipos MIME permitidos.
     * @param int|null $maxSize Tamanho máximo permitido em bytes.
     * @throws UploadException Se ocorrer um erro durante o upload.
     */
    public function __construct(array $file, array $allowedMimeTypes = [], ?int $maxSize = null)
    {
        // Lança uma exceção se houver um erro de upload do PHP
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new UploadException($file['error']);
        }

        $this->file = $file;
        $this->allowedMimeTypes = $allowedMimeTypes;
        $this->maxSize = $maxSize ?? $this->maxSize;

        $this->validate();
    }

    /**
     * Valida o arquivo contra as regras de tamanho e tipo MIME.
     *
     * @return void
     * @throws \RuntimeException Se a validação falhar.
     */
    public function validate(): void
    {
        // Validação de tamanho
        if ($this->getSize() > $this->maxSize) {
            throw new \RuntimeException("O arquivo excede o tamanho máximo permitido de " . ($this->maxSize / 1048576) . "MB.");
        }

        // Validação de tipo MIME
        if (!empty($this->allowedMimeTypes) && !in_array($this->getMimeType(), $this->allowedMimeTypes)) {
            throw new \RuntimeException("Tipo de arquivo não permitido. Apenas " . implode(', ', $this->allowedMimeTypes) . " são aceitos.");
        }
    }

    /**
     * Retorna o nome original do arquivo.
     *
     * @return string O nome original do arquivo, como enviado pelo cliente.
     */
    public function getOriginalName(): string
    {
        return $this->file['name'];
    }

    /**
     * Retorna a extensão original do arquivo.
     *
     * @return string A extensão do arquivo (e.g., 'jpg', 'pdf').
     */
    public function getOriginalExtension(): string
    {
        return pathinfo($this->file['name'], PATHINFO_EXTENSION);
    }

    /**
     * Retorna o caminho temporário do arquivo no servidor.
     *
     * @return string O caminho completo para o arquivo temporário.
     */
    public function getRealPath(): string
    {
        return $this->file['tmp_name'];
    }

    /**
     * Retorna o tamanho do arquivo em bytes.
     *
     * @return int O tamanho do arquivo.
     */
    public function getSize(): int
    {
        return $this->file['size'];
    }

    /**
     * Retorna o tipo MIME do arquivo.
     *
     * @return string O tipo MIME do arquivo (e.g., 'image/jpeg', 'application/pdf').
     */
    public function getMimeType(): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
             throw new \RuntimeException("Não foi possível abrir o banco de dados fileinfo.");
        }
        $mime = finfo_file($finfo, $this->getRealPath());
        finfo_close($finfo);
        return $mime;
    }

    /**
     * Move o arquivo de upload para um novo diretório.
     *
     * @param string $directory O caminho completo para o diretório de destino.
     * @param string|null $name Opcionalmente, um novo nome para o arquivo. Se nulo,
     * um nome único será gerado.
     * @return string O caminho completo para o arquivo movido.
     * @throws \RuntimeException Se a movimentação do arquivo falhar.
     */
    public function move(string $directory, ?string $name = null): string
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $extension = $this->getOriginalExtension();
        $filename = $name ?? uniqid('', true) . '.' . $extension;
        $destination = rtrim($directory, '/') . '/' . $filename;

        if (!move_uploaded_file($this->getRealPath(), $destination)) {
            throw new \RuntimeException('Falha ao mover o arquivo de upload.');
        }

        return $destination;
    }
}