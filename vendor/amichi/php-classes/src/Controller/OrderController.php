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
use Amichi\Model\Cart;
use Amichi\Model\Order;
use Amichi\Model\User;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Classe que controla a entidade PEDIDO
 *
 * @category Controller
 * @package  Amichi/Controller
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class OrderController extends Controller
{
    /**
     * Retorna todos os pedidos do banco de dados
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
                    fn (Order $order): array => $order->array(),
                    Order::listAll(
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
     * Retorna o pedido a partir do ID informado na URL
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
        $order = Order::loadFromId(self::int($args["idOrder"], true, "idOrder"));
        $response->getBody()->write(json_encode($order));

        return $response->withStatus($order ? 200 : 204);
    }


    /**
     * Retorna os pedidos do usuário a partir do ID informado na URL
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
            throw (new HttpException("Não foi possível consultar os pedidos do usuário $idUser, pois, você não possui permissão.", 400))->json();
        }

        $orders = array_map(
            fn (Order $order): array => $order->array(),
            Order::listFromUserId($idUser)
        );

        $response->getBody()->write(json_encode($orders));

        return $response;
    }


    /**
     * Retorna o boleto de pagamento do pedido a partir do ID informado na URL
     *
     * @param Request  $request  Requisição
     * @param Response $response Resposta
     * @param array    $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function getBankPaymentSlip(Request $request, Response $response, array $args): Response
    {
        $id = self::int($args["idOrder"], true, "idOrder");
        $order = Order::loadFromId($id);

        $sessionUser = User::loadFromSession();
        if ($sessionUser->id !== $order->idUser && !$sessionUser->isAdmin) {
            throw (new HttpException("Não foi possível consultar o boleto do pedido $id, pois, você não possui permissão.", 400))->json();
        }

        $response->getBody()->write($order->getBankPaymentSlip());

        return $response->withStatus($order ? 200 : 204);
    }


    /**
     * Salva o pedido informado no corpo da requisição no banco de dados
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
        $errors = [];
        $order = Order::loadFromData((array) $request->getParsedBody());
        $order->totalValue = Cart::loadFromId($order->idCart)?->totalPrice;

        $order->validate($errors);

        if ($errors) {
            $message = count($errors) === 1 ? "O seguinte erro foi encontrado" : "Os seguintes erros foram encontrados";
            throw (new HttpException("Não foi possível cadastrar o pedido. $message: " . implode(", ", $errors) . ".", 400))->json();
        }

        $response->getBody()->write(json_encode($order->save()));

        return $response->withStatus(201);
    }


    /**
     * Altera os dados do pedido informado no corpo da requisição a partir do ID informado na URL
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
        $id = self::int($args["idOrder"], true, "idOrder");

        if (!Order::loadFromId($id)) {
            throw (new HttpException("Não foi possível alterar o pedido $id, pois, é inexistente.", 400))->json();
        }

        $order = Order::loadFromData((array) $request->getParsedBody());
        $order->id = $id;
        $order->totalValue = Cart::loadFromId($order->idCart)?->totalPrice;
        $order->validate($errors);

        if ($errors) {
            $message = count($errors) === 1 ? "O seguinte erro foi encontrado" : "Os seguintes erros foram encontrados";
            throw (new HttpException("Não foi possível alterar o pedido $id. $message: " . implode(", ", $errors) . ".", 400))->json();
        }

        $response->getBody()->write(json_encode($order->save()));

        return $response->withStatus(200);
    }


    /**
     * Remove o pedido a partir do ID informado na URL
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
        $id = self::int($args["idOrder"], true, "idOrder");
        $order = Order::loadFromId($id);

        if (!$order) {
            throw (new HttpException("Não foi possível remover o pedido $id, pois, é inexistente.", 400))->json();
        }

        $response->getBody()->write(json_encode($order->delete()));

        return $response;
    }
}
