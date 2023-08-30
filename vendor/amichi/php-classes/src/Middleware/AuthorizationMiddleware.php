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

use Amichi\Model\User;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Classe que implementa o middleware AUTORIZAÇÃO
 *
 * @category Middleware
 * @package  Amichi/Middleware
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class AuthorizationMiddleware
{
    /**
     * Valida se o usuário é um cliente
     *
     * @param Request        $request Requisição
     * @param RequestHandler $handler Manipulador da requisição
     *
     * @return Response
     */
    public static function isUser(Request $request, RequestHandler $handler): Response
    {
        $user = User::loadFromSession();

        if ($user) {
            return $handler->handle($request);
        }

        return $handler->handle($request)->withHeader("Location", "/admin/login")->withStatus(302);
    }


    /**
     * Valida se o usuário é um administrador
     *
     * @param Request        $request Requisição
     * @param RequestHandler $handler Manipulador da requisição
     *
     * @return Response
     */
    public static function isAdmin(Request $request, RequestHandler $handler): Response
    {
        $user = User::loadFromSession();

        if ($user && $user->isAdmin) {
            return $handler->handle($request);
        }

        return $handler->handle($request)->withHeader("Location", "/admin/login")->withStatus(302);
    }
}
