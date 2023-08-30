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
use Amichi\Model\TopicType;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Classe que controla a entidade TIPO DE TÓPICO
 *
 * @category Controller
 * @package  Amichi/Controller
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class TopicTypeController extends Controller
{
    /**
     * Retorna todos os tipo de tópicos do banco de dados
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
                    fn (TopicType $topicType): array => $topicType->array(),
                    TopicType::listAll(
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
     * Retorna o tipo de tópico a partir do ID informado na URL
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
        $topicType = TopicType::loadFromId(self::int($args["idType"], true, "idType"));
        $response->getBody()->write(json_encode($topicType));

        return $response->withStatus($topicType ? 200 : 204);
    }


    /**
     * Salva o tipo de tópico informado no corpo da requisição no banco de dados
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
        $topicType = TopicType::loadFromData((array) $request->getParsedBody());
        $topicType->validate($errors);

        if ($errors) {
            $message = count($errors) === 1 ? "O seguinte erro foi encontrado" : "Os seguintes erros foram encontrados";
            throw (new HttpException("Não foi possível cadastrar o tipo de tópico. $message: " . implode(", ", $errors) . ".", 400))->json();
        }

        $response->getBody()->write(json_encode($topicType->save()));

        return $response->withStatus(201);
    }


    /**
     * Altera os dados do tipo de tópico informado no corpo da requisição a partir do ID informado na URL
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
        $id = self::int($args["idType"], true, "idType");

        if (!TopicType::loadFromId($id)) {
            throw (new HttpException("Não foi possível alterar o tipo de tópico $id, pois, é inexistente.", 400))->json();
        }

        $topicType = TopicType::loadFromData((array) $request->getParsedBody());
        $topicType->id = $id;
        $topicType->validate($errors);

        if ($errors) {
            $message = count($errors) === 1 ? "O seguinte erro foi encontrado" : "Os seguintes erros foram encontrados";
            throw (new HttpException("Não foi possível alterar o tipo de tópico $id. $message: " . implode(", ", $errors) . ".", 400))->json();
        }

        $response->getBody()->write(json_encode($topicType->save()));

        return $response->withStatus(200);
    }


    /**
     * Remove o tipo de tópico a partir do ID informado na URL
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
        $id = self::int($args["idType"], true, "idType");
        $topicType = TopicType::loadFromId($id);

        if (!$topicType) {
            throw (new HttpException("Não foi possível remover o tipo de tópico $id, pois, é inexistente.", 400))->json();
        }

        $response->getBody()->write(json_encode($topicType->delete()));

        return $response;
    }
}
