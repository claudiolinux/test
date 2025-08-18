<?php

declare(strict_types=1);

namespace Slenix\Middlewares;

use Slenix\Http\Auth\Jwt;
use Slenix\Http\Message\Request;
use Slenix\Http\Message\Response;
use Slenix\Http\Message\Middleware;

class JwtMiddleware implements Middleware
{
    private Jwt $jwt;

    public function __construct()
    {
        $this->jwt = new Jwt();
    }

    /**
     * Executa o middleware para verificar o token JWT.
     *
     * @param Request $request
     * @param Response $response
     * @param array $params
     * @return bool Retorna true para continuar ou false para interromper.
     */
    public function handle(Request $request, Response $response, array $params): bool
    {
        // 1. Obtém o cabeçalho de autorização.
        $authHeader = $request->getHeader('Authorization');
        
        // 2. Verifica se o cabeçalho existe e tem o formato "Bearer <token>".
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            $response->status(401)->json(['error' => 'Token não fornecido ou em formato inválido.']);
            return false; // Interrompe a execução
        }
        
        // 3. Extrai o token e o valida.
        $token = substr($authHeader, 7);
        $payload = $this->jwt->validate($token);
        
        // 4. Se o payload for nulo, o token é inválido ou expirado.
        if (!$payload) {
            $response->status(401)->json(['error' => 'Token inválido ou expirado.']);
            return false; // Interrompe a execução
        }
        
        // 5. Se o token for válido, adiciona o payload ao request para uso posterior
        //    no controlador da rota.
        $request->setAttribute('jwt_payload', $payload);
        
        return true; // Prossegue para o próximo middleware ou para o controlador
    }
}
