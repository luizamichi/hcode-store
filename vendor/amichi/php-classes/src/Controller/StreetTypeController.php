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
use Amichi\Model\StreetType;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Classe que controla a entidade TIPO DE LOGRADOURO
 *
 * @category Controller
 * @package  Amichi/Controller
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class StreetTypeController extends Controller
{
    /**
     * Retorna todos os tipos de logradouro do banco de dados
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
                    fn (StreetType $streetType): array => $streetType->array(),
                    StreetType::listAll(
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
     * Retorna o tipo de logradouro a partir do ID informado na URL
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
        $streetType = StreetType::loadFromId(self::int($args["idStreetType"], true, "idStreetType"));
        $response->getBody()->write(json_encode($streetType));

        return $response->withStatus($streetType ? 200 : 204);
    }


    /**
     * Salva o tipo de logradouro informado no corpo da requisição no banco de dados
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
        $streetType = StreetType::loadFromData((array) $request->getParsedBody());
        $streetType->validate($errors);

        if ($errors) {
            $message = count($errors) === 1 ? "O seguinte erro foi encontrado" : "Os seguintes erros foram encontrados";
            throw (new HttpException("Não foi possível cadastrar o tipo de logradouro. $message: " . implode(", ", $errors) . ".", 400))->json();
        }

        $response->getBody()->write(json_encode($streetType->save()));

        return $response->withStatus(201);
    }


    /**
     * Altera os dados do tipo de logradouro informado no corpo da requisição a partir do ID informado na URL
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
        $id = self::int($args["idStreetType"], true, "idStreetType");

        if (!StreetType::loadFromId($id)) {
            throw (new HttpException("Não foi possível alterar o tipo de logradouro $id, pois, é inexistente.", 400))->json();
        }

        $streetType = StreetType::loadFromData((array) $request->getParsedBody());
        $streetType->id = $id;
        $streetType->validate($errors);

        if ($errors) {
            $message = count($errors) === 1 ? "O seguinte erro foi encontrado" : "Os seguintes erros foram encontrados";
            throw (new HttpException("Não foi possível alterar o tipo de logradouro $id. $message: " . implode(", ", $errors) . ".", 400))->json();
        }

        $response->getBody()->write(json_encode($streetType->save()));

        return $response->withStatus(200);
    }


    /**
     * Remove o tipo de logradouro a partir do ID informado na URL
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
        $id = self::int($args["idStreetType"], true, "idStreetType");
        $streetType = StreetType::loadFromId($id);

        if (!$streetType) {
            throw (new HttpException("Não foi possível remover o tipo de logradouro $id, pois, é inexistente.", 400))->json();
        }

        $addresses = Address::listFromStreetTypeId($id);

        if (!empty($addresses)) {
            $message = count($addresses) === 1 ? "ao endereço" : "aos endereços";
            $addressesIds = array_map(
                fn (Address $address): int => $address->id,
                $addresses
            );

            throw (new HttpException("Não foi possível remover o tipo de logradouro $id, pois, está vinculado $message " . implode(", ", $addressesIds) . ".", 400))->json();
        }

        $response->getBody()->write(json_encode($streetType->delete()));

        return $response;
    }
}
