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
 * Classe que implementa o middleware JSON
 *
 * @category Middleware
 * @package  Amichi/Middleware
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class JsonMiddleware implements MiddlewareInterface
{
    /**
     * Adiciona informações no cabeçalho da resposta para definir o conteúdo da resposta como JSON
     *
     * @param Request        $request Requisição
     * @param RequestHandler $handler Manipulador da requisição
     *
     * @return Response
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        return $handler->handle($request)
            ->withHeader("Content-type", "application/json")
            ->withHeader("Expires", gmdate("D, d M Y H:i:s \G\M\T", time() + (60 * 60)));
    }
}
