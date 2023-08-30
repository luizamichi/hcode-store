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
use Amichi\Model\Subtopic;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Classe que controla a entidade SUBTÓPICO
 *
 * @category Controller
 * @package  Amichi/Controller
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class SubtopicController extends Controller
{
    /**
     * Retorna todos os subtópicos do banco de dados
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
                    fn (Subtopic $subtopic): array => $subtopic->array(),
                    Subtopic::listAll(
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
     * Retorna o subtópico a partir do ID informado na URL
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
        $subtopic = Subtopic::loadFromId(self::int($args["idSubtopic"], true, "idSubtopic"));
        $response->getBody()->write(json_encode($subtopic));

        return $response->withStatus($subtopic ? 200 : 204);
    }


    /**
     * Salva o subtópico informado no corpo da requisição no banco de dados
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
        $subtopic = Subtopic::loadFromData((array) $request->getParsedBody());
        $subtopic->validate($errors);

        if ($errors) {
            $message = count($errors) === 1 ? "O seguinte erro foi encontrado" : "Os seguintes erros foram encontrados";
            throw (new HttpException("Não foi possível cadastrar o subtópico. $message: " . implode(", ", $errors) . ".", 400))->json();
        }

        $response->getBody()->write(json_encode($subtopic->save()));

        return $response->withStatus(201);
    }


    /**
     * Altera os dados do subtópico informado no corpo da requisição a partir do ID informado na URL
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
        $id = self::int($args["idSubtopic"], true, "idSubtopic");

        if (!Subtopic::loadFromId($id)) {
            throw (new HttpException("Não foi possível alterar o subtópico $id, pois, é inexistente.", 400))->json();
        }

        $subtopic = Subtopic::loadFromData((array) $request->getParsedBody());
        $subtopic->id = $id;
        $subtopic->validate($errors);

        if ($errors) {
            $message = count($errors) === 1 ? "O seguinte erro foi encontrado" : "Os seguintes erros foram encontrados";
            throw (new HttpException("Não foi possível alterar o subtópico $id. $message: " . implode(", ", $errors) . ".", 400))->json();
        }

        $response->getBody()->write(json_encode($subtopic->save()));

        return $response->withStatus(200);
    }


    /**
     * Remove o subtópico a partir do ID informado na URL
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
        $id = self::int($args["idSubtopic"], true, "idSubtopic");
        $subtopic = Subtopic::loadFromId($id);

        if (!$subtopic) {
            throw (new HttpException("Não foi possível remover o subtópico $id, pois, é inexistente.", 400))->json();
        }

        $response->getBody()->write(json_encode($subtopic->delete()));

        return $response;
    }
}
