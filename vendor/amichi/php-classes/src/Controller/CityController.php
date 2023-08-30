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
use Amichi\Model\City;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Classe que controla a entidade CIDADE
 *
 * @category Controller
 * @package  Amichi/Controller
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class CityController extends Controller
{
    /**
     * Retorna todas as cidades do banco de dados
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
                    fn (City $city): array => $city->array(),
                    City::listAll(
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
     * Retorna a cidade a partir do ID informado na URL
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
        $city = City::loadFromId(self::int($args["idCity"], true, "idCity"));
        $response->getBody()->write(json_encode($city));

        return $response->withStatus($city ? 200 : 204);
    }


    /**
     * Retorna as cidades do estado a partir do ID informado na URL
     *
     * @param Request       $request  Requisição
     * @param Response      $response Resposta
     * @param array<string> $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function getByState(Request $request, Response $response, array $args): Response
    {
        $cities = array_map(
            fn (City $city): array => $city->array(),
            City::listFromStateId(self::int($args["idState"], true, "idState"))
        );

        $response->getBody()->write(json_encode($cities));

        return $response;
    }


    /**
     * Salva a cidade informada no corpo da requisição no banco de dados
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
        $city = City::loadFromData((array) $request->getParsedBody());
        $city->validate($errors);

        if ($errors) {
            $message = count($errors) === 1 ? "O seguinte erro foi encontrado" : "Os seguintes erros foram encontrados";
            throw (new HttpException("Não foi possível cadastrar a cidade. $message: " . implode(", ", $errors) . ".", 400))->json();
        }

        $response->getBody()->write(json_encode($city->save()));

        return $response->withStatus(201);
    }


    /**
     * Altera os dados da cidade informada no corpo da requisição a partir do ID informado na URL
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
        $id = self::int($args["idCity"], true, "idCity");

        if (!City::loadFromId($id)) {
            throw (new HttpException("Não foi possível alterar a cidade $id, pois, é inexistente.", 400))->json();
        }

        $city = City::loadFromData((array) $request->getParsedBody());
        $city->id = $id;
        $city->validate($errors);

        if ($errors) {
            $message = count($errors) === 1 ? "O seguinte erro foi encontrado" : "Os seguintes erros foram encontrados";
            throw (new HttpException("Não foi possível alterar a cidade $id. $message: " . implode(", ", $errors) . ".", 400))->json();
        }

        $response->getBody()->write(json_encode($city->save()));

        return $response->withStatus(200);
    }


    /**
     * Remove a cidade a partir do ID informado na URL
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
        $id = self::int($args["idCity"], true, "idCity");
        $city = City::loadFromId($id);

        if (!$city) {
            throw (new HttpException("Não foi possível remover a cidade $id, pois, é inexistente.", 400))->json();
        }

        $addresses = Address::listFromCityId($id);

        if (!empty($addresses)) {
            $message = count($addresses) === 1 ? "ao endereço" : "aos endereços";
            $addressesIds = array_map(
                fn (Address $address): int => $address->id,
                $addresses
            );

            throw (new HttpException("Não foi possível remover a cidade $id, pois, está vinculada $message " . implode(", ", $addressesIds) . ".", 400))->json();
        }

        $response->getBody()->write(json_encode($city->delete()));

        return $response;
    }
}
