<?php

/**
 * PHP version 8.1.2
 *
 * @category Middleware
 * @package  Amichi/Middleware
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */

namespace Amichi\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Classe que implementa o middleware CORS
 *
 * @category Middleware
 * @package  Amichi/Middleware
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class CorsMiddleware implements MiddlewareInterface
{
    /**
     * Adiciona informações no cabeçalho da resposta para compartilhar recursos entre diferentes origens
     *
     * @param Request        $request Requisição
     * @param RequestHandler $handler Manipulador da requisição
     *
     * @return Response
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        return $handler->handle($request)
            ->withHeader("Access-Control-Allow-Origin", "*")
            ->withHeader("Access-Control-Allow-Headers", "X-Requested-With, Content-Type, Accept, Origin, Authorization")
            ->withHeader("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE, PATCH, OPTIONS");
    }
}
