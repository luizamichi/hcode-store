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
use Amichi\Model\State;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Classe que controla a entidade ESTADO
 *
 * @category Controller
 * @package  Amichi/Controller
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class StateController extends Controller
{
    /**
     * Retorna todos os estados do banco de dados
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
                    fn (State $state): array => $state->array(),
                    State::listAll(
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
     * Retorna o estado a partir do ID informado na URL
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
        $state = State::loadFromId(self::int($args["idState"], true, "idState"));
        $response->getBody()->write(json_encode($state));

        return $response->withStatus($state ? 200 : 204);
    }


    /**
     * Retorna os estados do país a partir do ID informado na URL
     *
     * @param Request       $request  Requisição
     * @param Response      $response Resposta
     * @param array<string> $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function getByCountry(Request $request, Response $response, array $args): Response
    {
        $states = array_map(
            fn (State $state): array => $state->array(),
            State::listFromCountryId(self::int($args["idCountry"], true, "idCountry"))
        );

        $response->getBody()->write(json_encode($states));

        return $response;
    }


    /**
     * Salva o estado informado no corpo da requisição no banco de dados
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
        $state = State::loadFromData((array) $request->getParsedBody());
        $state->validate($errors);

        if ($errors) {
            $message = count($errors) === 1 ? "O seguinte erro foi encontrado" : "Os seguintes erros foram encontrados";
            throw (new HttpException("Não foi possível cadastrar o estado. $message: " . implode(", ", $errors) . ".", 400))->json();
        }

        $response->getBody()->write(json_encode($state->save()));

        return $response->withStatus(201);
    }


    /**
     * Altera os dados do estado informado no corpo da requisição a partir do ID informado na URL
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
        $id = self::int($args["idState"], true, "idState");

        if (!State::loadFromId($id)) {
            throw (new HttpException("Não foi possível alterar o estado $id, pois, é inexistente.", 400))->json();
        }

        $state = State::loadFromData((array) $request->getParsedBody());
        $state->id = $id;
        $state->validate($errors);

        if ($errors) {
            $message = count($errors) === 1 ? "O seguinte erro foi encontrado" : "Os seguintes erros foram encontrados";
            throw (new HttpException("Não foi possível alterar o estado $id. $message: " . implode(", ", $errors) . ".", 400))->json();
        }

        $response->getBody()->write(json_encode($state->save()));

        return $response->withStatus(200);
    }


    /**
     * Remove o estado a partir do ID informado na URL
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
        $id = self::int($args["idState"], true, "idState");
        $state = State::loadFromId($id);

        if (!$state) {
            throw (new HttpException("Não foi possível remover o estado $id, pois, é inexistente.", 400))->json();
        }

        $response->getBody()->write(json_encode($state->delete()));

        return $response;
    }
}
