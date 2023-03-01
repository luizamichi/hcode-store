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
use Amichi\Model\Order;
use Amichi\Model\User;
use Amichi\Model\UserLog;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Classe que controla a entidade ENDEREÇO
 *
 * @category Controller
 * @package  Amichi/Controller
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class AddressController extends Controller
{
    /**
     * Retorna todos os endereços do banco de dados
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
                    fn (Address $address): array => $address->array(),
                    Address::listAll(
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
     * Retorna o endereço a partir do ID informado na URL
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
        $address = Address::loadFromId(self::int($args["idAddress"], true, "idAddress"));
        $response->getBody()->write(json_encode($address));

        return $response->withStatus($address ? 200 : 204);
    }


    /**
     * Salva o endereço informado no corpo da requisição no banco de dados
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
        $address = Address::loadFromData((array) $request->getParsedBody());

        $sessionUser = User::loadFromSession();
        $enableLog = false;

        if (!$sessionUser->isAdmin) {
            if (Address::loadFromPersonId($sessionUser->idPerson)) {
                throw (new HttpException("Não foi possível cadastrar o endereço, pois, você já possui um endereço cadastrado.", 400))->json();
            }

            $address->idPerson = $sessionUser->idPerson;
            $enableLog = true;
        }

        $address->validate($errors);

        if ($errors) {
            $message = count($errors) === 1 ? "O seguinte erro foi encontrado" : "Os seguintes erros foram encontrados";
            throw (new HttpException("Não foi possível cadastrar o endereço. $message: " . implode(", ", $errors) . ".", 400))->json();
        }

        if ($enableLog) {
            $userLog = UserLog::loadFromSession();
            $userLog->description = "Address create";
            $userLog->save();
        }

        $response->getBody()->write(json_encode($address->save()));

        return $response->withStatus(201);
    }


    /**
     * Altera os dados do endereço informado no corpo da requisição a partir do ID informado na URL
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
        $id = self::int($args["idAddress"], true, "idAddress");

        $addressDB = Address::loadFromId($id);
        if (!$addressDB) {
            throw (new HttpException("Não foi possível alterar o endereço $id, pois, é inexistente.", 400))->json();
        }

        $address = Address::loadFromData((array) $request->getParsedBody());
        $address->id = $id;
        $address->idPerson = $addressDB->idPerson;

        $sessionUser = User::loadFromSession();
        $enableLog = !$sessionUser || !$sessionUser->isAdmin;

        if ($sessionUser->idPerson !== $address->idPerson && !$sessionUser->isAdmin) {
            throw (new HttpException("Não foi possível alterar o endereço $id, pois, você não possui permissão.", 400))->json();
        }

        $address->validate($errors);

        if ($errors) {
            $message = count($errors) === 1 ? "O seguinte erro foi encontrado" : "Os seguintes erros foram encontrados";
            throw (new HttpException("Não foi possível alterar o endereço $id. $message: " . implode(", ", $errors) . ".", 400))->json();
        }

        if ($enableLog) {
            $userLog = UserLog::loadFromSession();
            $userLog->description = "Address update";
            $userLog->save();
        }

        $response->getBody()->write(json_encode($address->save()));

        return $response->withStatus(200);
    }


    /**
     * Remove o endereço a partir do ID informado na URL
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
        $id = self::int($args["idAddress"], true, "idAddress");
        $address = Address::loadFromId($id);

        if (!$address) {
            throw (new HttpException("Não foi possível remover o endereço $id, pois, é inexistente.", 400))->json();
        }

        $orders = Order::listFromAddressId($id);
        if ($orders) {
            $ordersIds = array_map(fn (Order $order): int => $order->id, $orders);
            $message = count($orders) === 1 ? "ao pedido" : "aos pedidos";
            throw (new HttpException("Não foi possível remover o endereço $id, pois, está vinculado $message " . implode(", ", $ordersIds) . ".", 400))->json();
        }

        $response->getBody()->write(json_encode($address->delete()));

        return $response;
    }
}
