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
use Amichi\Model\Address;
use Amichi\Model\StreetType;
use Amichi\Model\User;
use Amichi\PageAdmin;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Classe que controla as views da entidade ENDEREÇO
 *
 * @category View
 * @package  Amichi/View
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class AddressView extends Controller
{
    /**
     * Retorna o template da lista de todos os endereços do banco de dados
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
            "addresses",
            [
                "addresses" => array_map(
                    fn (Address $address): array => $address->array(),
                    Address::listAll(
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
     * Retorna o template para cadastro de endereço no banco de dados
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
        $page->setTpl(
            "addresses-create",
            [
                "streetTypes" => array_map(
                    fn (StreetType $streetType): array => $streetType->array(),
                    StreetType::listAll(sortBy: "name")
                ),
                "users" => array_map(
                    fn (User $user): array => $user->array(),
                    User::listAll(sortBy: "name")
                )
            ]
        );

        $response->getBody()->write($page->getTpl());
        return $response;
    }


    /**
     * Retorna o template para alteração de endereço no banco de dados
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
        $address = Address::loadFromId(self::int($args["idAddress"]));

        if (!$address) {
            return $response->withHeader("Location", "/admin/addresses")->withStatus(302);
        }

        $page = new PageAdmin();
        $page->setTpl(
            "addresses-update",
            [
                "address" => $address->array(),
                "streetTypes" => array_map(
                    fn (StreetType $streetType): array => $streetType->array(),
                    StreetType::listAll(sortBy: "name")
                ),
                "users" => array_map(
                    fn (User $user): array => $user->array(),
                    User::listAll(sortBy: "name")
                )
            ]
        );

        $response->getBody()->write($page->getTpl());
        return $response;
    }
}
