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
use Amichi\Model\Order;
use Amichi\Model\OrderStatus;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Classe que controla a entidade STATUS DE PEDIDO
 *
 * @category Controller
 * @package  Amichi/Controller
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class OrderStatusController extends Controller
{
    /**
     * Retorna todos os status de pedidos do banco de dados
     *
     * @param Request       $request  Requisição
     * @param Response      $response Resposta
     * @param array<string> $args     Argumentos da URL
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
                    fn (OrderStatus $orderStatus): array => $orderStatus->array(),
                    OrderStatus::listAll(
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
     * Retorna o status de pedido a partir do ID informado na URL
     *
     * @param Request       $request  Requisição
     * @param Response      $response Resposta
     * @param array<string> $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function get(Request $request, Response $response, array $args): Response
    {
        $orderStatus = OrderStatus::loadFromId(self::int($args["idStatus"], true, "idStatus"));
        $response->getBody()->write(json_encode($orderStatus));

        return $response->withStatus($orderStatus ? 200 : 204);
    }


    /**
     * Salva o status de pedido informado no corpo da requisição no banco de dados
     *
     * @param Request       $request  Requisição
     * @param Response      $response Resposta
     * @param array<string> $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function post(Request $request, Response $response, array $args): Response
    {
        $errors = [];
        $orderStatus = OrderStatus::loadFromData((array) $request->getParsedBody());
        $orderStatus->validate($errors);

        if ($errors) {
            $message = count($errors) === 1 ? "O seguinte erro foi encontrado" : "Os seguintes erros foram encontrados";
            throw (new HttpException("Não foi possível cadastrar o status de pedido. $message: " . implode(", ", $errors) . ".", 400))->json();
        }

        $response->getBody()->write(json_encode($orderStatus->save()));

        return $response->withStatus(201);
    }


    /**
     * Altera os dados do status de pedido informado no corpo da requisição a partir do ID informado na URL
     *
     * @param Request       $request  Requisição
     * @param Response      $response Resposta
     * @param array<string> $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function put(Request $request, Response $response, array $args): Response
    {
        $errors = [];
        $id = self::int($args["idStatus"], true, "idStatus");

        if (!OrderStatus::loadFromId($id)) {
            throw (new HttpException("Não foi possível alterar o status de pedido $id, pois, é inexistente.", 400))->json();
        }

        $orderStatus = OrderStatus::loadFromData((array) $request->getParsedBody());
        $orderStatus->id = $id;
        $orderStatus->validate($errors);

        if ($errors) {
            $message = count($errors) === 1 ? "O seguinte erro foi encontrado" : "Os seguintes erros foram encontrados";
            throw (new HttpException("Não foi possível alterar o status de pedido $id. $message: " . implode(", ", $errors) . ".", 400))->json();
        }

        $response->getBody()->write(json_encode($orderStatus->save()));

        return $response->withStatus(200);
    }


    /**
     * Remove o status de pedido a partir do ID informado na URL
     *
     * @param Request       $request  Requisição
     * @param Response      $response Resposta
     * @param array<string> $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function delete(Request $request, Response $response, array $args): Response
    {
        $id = self::int($args["idStatus"], true, "idStatus");
        $orderStatus = OrderStatus::loadFromId($id);

        if (!$orderStatus) {
            throw (new HttpException("Não foi possível remover o status de pedido $id, pois, é inexistente.", 400))->json();
        }

        $orders = Order::listFromStatusId($id);
        if ($orders) {
            $ordersIds = array_map(fn (Order $order): int => $order->id, $orders);
            $message = count($orders) === 1 ? "ao pedido" : "aos pedidos";
            throw (new HttpException("Não foi possível remover o status de pedido $id, pois, está vinculado $message " . implode(", ", $ordersIds) . ".", 400))->json();
        }

        $response->getBody()->write(json_encode($orderStatus->delete()));

        return $response;
    }
}
