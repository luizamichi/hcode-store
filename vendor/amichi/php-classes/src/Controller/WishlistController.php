<?php

/**
 * PHP version 8.1.2
 *
 * @category Controller
 * @package  Amichi/Controller
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */

namespace Amichi\Controller;

use Amichi\Controller;
use Amichi\HttpException;
use Amichi\Model\User;
use Amichi\Model\Wishlist;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Classe que controla a entidade LISTA DE DESEJOS
 *
 * @category Controller
 * @package  Amichi/Controller
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class WishlistController extends Controller
{
    /**
     * Retorna a lista de desejos do usuário a partir do ID informado na URL
     *
     * @param Request  $request  Requisição
     * @param Response $response Resposta
     * @param array    $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function getByUser(Request $request, Response $response, array $args): Response
    {
        $idUser = self::int($args["idUser"], true, "idUser");
        $sessionUser = User::loadFromSession();

        if ($sessionUser->id !== $idUser && !$sessionUser->isAdmin) {
            throw (new HttpException("Não foi possível consultar os produtos da lista de desejos do usuário $idUser, pois, você não possui permissão.", 400))->json();
        }

        $wishlist = array_map(
            fn (Wishlist $wishlist): array => $wishlist->array(),
            Wishlist::listFromUserId($idUser)
        );

        $response->getBody()->write(json_encode($wishlist));

        return $response;
    }


    /**
     * Adiciona um produto na lista de desejos do usuário a partir do ID informado na URL
     *
     * @param Request  $request  Requisição
     * @param Response $response Resposta
     * @param array    $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function post(Request $request, Response $response, array $args): Response
    {
        $idUser = self::int($args["idUser"], true, "idUser");
        $idProduct = self::int($args["idProduct"], true, "idProduct");

        $sessionUser = User::loadFromSession();
        if ($sessionUser->id !== $idUser && !$sessionUser->isAdmin) {
            throw (new HttpException("Não foi possível adicionar o produto à lista de desejos do usuário $idUser, pois, você não possui permissão.", 400))->json();
        }

        $wishlist = Wishlist::loadFromUserAndProductId($idUser, $idProduct);

        if ($wishlist) {
            throw (new HttpException("Não foi possível adicionar o produto à lista de desejos, pois, já foi adicionado anteriormente.", 400))->json();
        }

        $errors = [];
        $wishlist = Wishlist::loadFromData(
            [
                "idUser" => $idUser,
                "idProduct" => $idProduct
            ]
        );
        $wishlist->validate($errors);

        if ($errors) {
            $message = count($errors) === 1 ? "O seguinte erro foi encontrado" : "Os seguintes erros foram encontrados";
            throw (new HttpException("Não foi possível adicionar o produto à lista de desejos. $message: " . implode(", ", $errors) . ".", 400))->json();
        }

        $response->getBody()->write(json_encode($wishlist->create()));

        return $response->withStatus(201);
    }


    /**
     * Remove o produto da lista de desejos do usuário a partir do ID informado na URL
     *
     * @param Request  $request  Requisição
     * @param Response $response Resposta
     * @param array    $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function delete(Request $request, Response $response, array $args): Response
    {
        $idUser = self::int($args["idUser"], true, "idUser");
        $idProduct = self::int($args["idProduct"], true, "idProduct");

        $sessionUser = User::loadFromSession();
        if ($sessionUser->id !== $idUser && !$sessionUser->isAdmin) {
            throw (new HttpException("Não foi possível remove o produto da lista de desejos do usuário $idUser, pois, você não possui permissão.", 400))->json();
        }

        $wishlist = Wishlist::loadFromUserAndProductId($idUser, $idProduct);

        if (!$wishlist) {
            throw (new HttpException("Não foi possível remover o produto da lista de desejos, pois, é inexistente.", 400))->json();
        }

        $response->getBody()->write(json_encode($wishlist->delete()));

        return $response;
    }
}
