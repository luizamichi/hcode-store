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
use Amichi\Model\Address;
use Amichi\Model\Cart;
use Amichi\Model\Order;
use Amichi\Model\Product;
use Amichi\Model\User;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Classe que controla a entidade CARRINHO
 *
 * @category Controller
 * @package  Amichi/Controller
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class CartController extends Controller
{
    /**
     * Retorna todos os carrinhos do banco de dados
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
        $response->getBody()->write(
            json_encode(
                array_map(
                    fn (Cart $cart): array => $cart->array(),
                    Cart::listAll(
                        self::int($params["_limit"]),
                        self::int($params["_offset"]),
                        self::string($params["_sortBy"])
                    )
                )
            )
        );

        return $response;
    }


    /**
     * Retorna o carrinho a partir do ID informado na URL
     *
     * @param Request  $request  Requisição
     * @param Response $response Resposta
     * @param array    $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function get(Request $request, Response $response, array $args): Response
    {
        $id = self::int($args["idCart"], true, "idCart");

        $sessionCart = Cart::loadFromSessionId();
        $sessionUser = User::loadFromSession();
        if (($sessionCart?->id !== $id || $sessionCart?->idUser !== $sessionUser->id) && !$sessionUser->isAdmin) {
            throw (new HttpException("Não foi possível consultar o carrinho $id, pois, você não possui permissão.", 400))->json();
        }

        $cart = Cart::loadFromId($id);
        $response->getBody()->write(json_encode($cart));

        return $response->withStatus($cart ? 200 : 204);
    }


    /**
     * Retorna os produtos do carrinho a partir do ID informado na URL
     *
     * @param Request  $request  Requisição
     * @param Response $response Resposta
     * @param array    $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function getProducts(Request $request, Response $response, array $args): Response
    {
        $id = self::int($args["idCart"], true, "idCart");

        $sessionCart = Cart::loadFromSessionId();
        $sessionUser = User::loadFromSession();
        if (($sessionCart?->id !== $id || $sessionCart?->idUser !== $sessionUser->id) && !$sessionUser->isAdmin) {
            throw (new HttpException("Não foi possível consultar os produtos do carrinho $id, pois, você não possui permissão.", 400))->json();
        }

        $products = array_map(
            fn (Product $product): array => $product->array(),
            (array) Cart::loadFromId($id)?->getProducts()
        );

        $response->getBody()->write(json_encode($products));

        return $response;
    }


    /**
     * Salva o carrinho informado no corpo da requisição no banco de dados
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
        if (Cart::loadFromSessionId()) {
            throw (new HttpException("Não foi possível cadastrar o carrinho, pois, há um carrinho armazenado na sessão.", 400))->json();
        }

        $errors = [];
        $sessionUser = User::loadFromSession();
        $address = Address::loadFromUserId($sessionUser?->id ?? 0);

        $cart = Cart::loadFromData((array) $request->getParsedBody());
        $cart->sessionId = session_id();
        $cart->idUser = $sessionUser?->id;
        $cart->idAddress = $address?->id;
        $cart->temporaryZipCode = $cart->temporaryZipCode ?: $address?->zipCode;
        $cart->validate($errors);

        if ($errors) {
            $message = count($errors) === 1 ? "O seguinte erro foi encontrado" : "Os seguintes erros foram encontrados";
            throw (new HttpException("Não foi possível cadastrar o carrinho. $message: " . implode(", ", $errors) . ".", 400))->json();
        }

        $response->getBody()->write(json_encode($cart->save()->saveInSession()));

        return $response->withStatus(201);
    }


    /**
     * Adiciona um produto no carrinho a partir do ID informado na URL
     *
     * @param Request  $request  Requisição
     * @param Response $response Resposta
     * @param array    $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function postProduct(Request $request, Response $response, array $args): Response
    {
        $idCart = self::int($args["idCart"], true, "idCart");
        $idProduct = self::int($args["idProduct"], true, "idProduct");
        $quantity = max(1, self::int($request->getParsedBody()["quantity"]));

        $sessionCart = Cart::loadFromSessionId();
        $sessionUser = User::loadFromSession();
        if (($sessionCart?->id !== $idCart || $sessionCart?->idUser !== $sessionUser?->id) && (!$sessionUser?->isAdmin)) {
            throw (new HttpException("Não foi possível inserir o produto $idProduct no carrinho $idCart, pois, você não possui permissão.", 400))->json();
        }

        $cart = Cart::loadFromId($idCart);
        if (!$cart) {
            throw (new HttpException("Não foi possível inserir o produto $idProduct no carrinho $idCart, pois, o carrinho é inexistente.", 400))->json();
        }

        $product = Product::loadFromId($idProduct);
        if (!$product) {
            throw (new HttpException("Não foi possível inserir o produto $idProduct no carrinho $idCart, pois, o produto é inexistente.", 400))->json();
        }

        $order = Order::loadFromCartId($idCart);
        if ($order) {
            throw (new HttpException("Não foi possível inserir o produto $idProduct no carrinho $idCart, pois, o carrinho se tornou um pedido.", 400))->json();
        }

        for ($i = 0; $i < $quantity; $i++) { // Adiciona individualmente a quantidade de produtos
            $cart->postProduct($idProduct);
        }

        $response->getBody()->write(
            json_encode(
                [
                    "cart" => $cart,
                    "products" => $cart->getProducts()
                ]
            )
        );

        return $response->withStatus(201);
    }


    /**
     * Altera os dados do carrinho informado no corpo da requisição a partir do ID informado na URL
     *
     * @param Request  $request  Requisição
     * @param Response $response Resposta
     * @param array    $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function put(Request $request, Response $response, array $args): Response
    {
        $errors = [];
        $id = self::int($args["idCart"], true, "idCart");

        $cartDB = Cart::loadFromId($id);
        if (!$cartDB) {
            throw (new HttpException("Não foi possível alterar o carrinho $id, pois, é inexistente.", 400))->json();
        }

        $sessionCart = Cart::loadFromSessionId();
        $sessionUser = User::loadFromSession();
        if (($sessionCart?->id !== $id || $sessionCart?->idUser !== $sessionUser->id) && (!$sessionUser->isAdmin)) {
            throw (new HttpException("Não foi possível alterar o carrinho $id, pois, você não possui permissão.", 400))->json();
        }

        $data = (array) $request->getParsedBody();
        if (!$sessionUser->isAdmin && $id === $sessionCart->id) {
            $cart = $sessionCart;
            $cart->temporaryZipCode = (int) (preg_replace("/\D/", "", $data["temporaryZipCode"] ?? "")) ?: null;
        } else {
            $cart = Cart::loadFromData($data);
            $cart->id = $id;
            $cart->sessionId = $cartDB->sessionId;
        }

        $cart->validate($errors);

        if ($errors) {
            $message = count($errors) === 1 ? "O seguinte erro foi encontrado" : "Os seguintes erros foram encontrados";
            throw (new HttpException("Não foi possível alterar o carrinho $id. $message: " . implode(", ", $errors) . ".", 400))->json();
        }

        $order = Order::loadFromCartId($id);
        if ($order) {
            throw (new HttpException("Não foi possível alterar o carrinho $id, pois, este se tornou um pedido.", 400))->json();
        }

        $response->getBody()->write(json_encode($cart->save()->refresh()));

        return $response->withStatus(200);
    }


    /**
     * Remove o carrinho a partir do ID informado na URL
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
        $id = self::int($args["idCart"], true, "idCart");
        $cart = Cart::loadFromId($id);

        if (!$cart) {
            throw (new HttpException("Não foi possível remover o carrinho $id, pois, é inexistente.", 400))->json();
        }

        $order = Order::loadFromCartId($id);
        if ($order) {
            throw (new HttpException("Não foi possível remover o carrinho $id, pois, está vinculado ao pedido {$order->id}.", 400))->json();
        }

        $response->getBody()->write(json_encode($cart->delete()));

        return $response;
    }


    /**
     * Remove o produto do carrinho a partir do ID informado na URL
     *
     * @param Request  $request  Requisição
     * @param Response $response Resposta
     * @param array    $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function deleteProduct(Request $request, Response $response, array $args): Response
    {
        $idCart = self::int($args["idCart"], true, "idCart");
        $idProduct = self::int($args["idProduct"], true, "idProduct");
        $removeAll = self::bool($request->getParsedBody()["removeAll"]);

        $sessionCart = Cart::loadFromSessionId();
        $sessionUser = User::loadFromSession();
        if (($sessionCart?->id !== $idCart || $sessionCart?->idUser !== $sessionUser?->id) && (!$sessionUser?->isAdmin)) {
            throw (new HttpException("Não foi possível remover o produto $idProduct do carrinho $idCart, pois, você não possui permissão.", 400))->json();
        }

        $cart = Cart::loadFromId($idCart);
        if (!$cart) {
            throw (new HttpException("Não foi possível remover o produto $idProduct do carrinho $idCart, pois, o carrinho é inexistente.", 400))->json();
        }

        if (!$cart->containsProduct($idProduct)) {
            throw (new HttpException("Não foi possível remover o produto $idProduct do carrinho $idCart, pois, o produto não está relacionado.", 400))->json();
        }

        $product = Product::loadFromId($idProduct);
        if (!$product) {
            throw (new HttpException("Não foi possível remover o produto $idProduct do carrinho $idCart, pois, o produto é inexistente.", 400))->json();
        }

        $order = Order::loadFromCartId($idCart);
        if ($order) {
            throw (new HttpException("Não foi possível remover o produto $idProduct do carrinho $idCart, pois, o carrinho se tornou um pedido.", 400))->json();
        }

        $response->getBody()->write(
            json_encode(
                [
                    "cart" => $cart->deleteProduct($idProduct, $removeAll),
                    "products" => $cart->getProducts()
                ]
            )
        );

        return $response;
    }
}
