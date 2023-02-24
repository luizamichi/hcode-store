<?php

/**
 * PHP version 8.1.2
 *
 * @category View
 * @package  Amichi/View
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */

namespace Amichi\View;

use Amichi\Controller;
use Amichi\Model\User;
use Amichi\PageAdmin;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Classe que controla as views da entidade USUÁRIO
 *
 * @category View
 * @package  Amichi/View
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class UserView extends Controller
{
    /**
     * Retorna o template da lista de todos os usuários do banco de dados
     *
     * @param Request  $request  Requisição
     * @param Response $response Resposta
     * @param array    $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function getAll(Request $request, Response $response, array $args): Response
    {
        $params = $request->getQueryParams();

        $page = new PageAdmin();
        $page->setTpl(
            "users",
            [
                "users" => array_map(
                    fn (User $user): array => $user->array(),
                    User::listAll(
                        self::int($params["_limit"]),
                        self::int($params["_offset"]),
                        self::string($params["_sortBy"])
                    )
                )
            ]
        );

        $response->getBody()->write($page->getTpl());
        return $response;
    }


    /**
     * Retorna o template para cadastro de usuário no banco de dados
     *
     * @param Request  $request  Requisição
     * @param Response $response Resposta
     * @param array    $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function create(Request $request, Response $response, array $args): Response
    {
        $page = new PageAdmin();
        $page->setTpl("users-create");

        $response->getBody()->write($page->getTpl());
        return $response;
    }


    /**
     * Retorna o template para alteração de usuário no banco de dados
     *
     * @param Request  $request  Requisição
     * @param Response $response Resposta
     * @param array    $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function update(Request $request, Response $response, array $args): Response
    {
        $user = User::loadFromId(self::int($args["idUser"]));

        if (!$user) {
            return $response->withHeader("Location", "/admin/users")->withStatus(302);
        }

        $page = new PageAdmin();
        $page->setTpl("users-update", ["user" => $user->array()]);

        $response->getBody()->write($page->getTpl());
        return $response;
    }


    /**
     * Retorna o template de login
     *
     * @param Request  $request  Requisição
     * @param Response $response Resposta
     * @param array    $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function login(Request $request, Response $response, array $args): Response
    {
        $user = User::loadFromSession();

        if ($user) {
            if ($user->isAdmin) {
                return $response->withHeader("Location", "/admin")->withStatus(302);
            }

            return $response->withHeader("Location", "/")->withStatus(302);
        }

        $page = new PageAdmin(
            [
                "header" => false,
                "footer" => false
            ]
        );
        $page->setTpl("login");

        $response->getBody()->write($page->getTpl());
        return $response;
    }


    /**
     * Realiza o logout e redireciona para a página de login
     *
     * @param Request  $request  Requisição
     * @param Response $response Resposta
     * @param array    $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function logout(Request $request, Response $response, array $args): Response
    {
        $user = User::loadFromSession()?->clearSession();

        if ($user?->isAdmin) {
            return $response->withHeader("Location", "/admin")->withStatus(302);
        }

        return $response->withHeader("Location", "/")->withStatus(302);
    }


    /**
     * Retorna o template de registro de novo usuário
     *
     * @param Request  $request  Requisição
     * @param Response $response Resposta
     * @param array    $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function register(Request $request, Response $response, array $args): Response
    {
        $page = new PageAdmin(
            [
                "header" => false,
                "footer" => false
            ]
        );
        $page->setTpl("register", ["email" => self::string($request->getQueryParams()["email"])]);

        $response->getBody()->write($page->getTpl());
        return $response;
    }
}