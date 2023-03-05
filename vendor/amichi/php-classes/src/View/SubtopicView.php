<?php

/**
 * PHP version 8.1.2
 *
 * @category View
 * @package  Amichi/View
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */

namespace Amichi\View;

use Amichi\Controller;
use Amichi\Model\Subtopic;
use Amichi\Model\Topic;
use Amichi\Model\TopicType;
use Amichi\PageAdmin;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Classe que controla as views da entidade SUBTÓPICO
 *
 * @category View
 * @package  Amichi/View
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class SubtopicView extends Controller
{
    /**
     * Retorna o template da lista de todos os subtópicos do banco de dados
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

        $page = new PageAdmin();
        $page->setTpl(
            "subtopics",
            [
                "subtopics" => array_map(
                    fn (Subtopic $subtopic): array => $subtopic->array(),
                    Subtopic::listAll(
                        self::int($params["_limit"]),
                        self::int($params["_offset"]),
                        self::string($params["_sortBy"])
                    )
                )
            ]
        );

        $response->getBody()->write($page->getTpl());
        return $response;
    }


    /**
     * Retorna o template para cadastro de subtópico no banco de dados
     *
     * @param Request  $request  Requisição
     * @param Response $response Resposta
     * @param array    $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function create(Request $request, Response $response, array $args): Response
    {
        $page = new PageAdmin();
        $page->setTpl(
            "subtopics-create",
            [
                "topics" => array_map(
                    fn (Topic $topic): array => $topic->array(),
                    Topic::listAll()
                ),
                "types" => array_map(
                    fn (TopicType $topicType): array => $topicType->array(),
                    TopicType::listAll()
                )
            ]
        );

        $response->getBody()->write($page->getTpl());
        return $response;
    }


    /**
     * Retorna o template para alteração de subtópico no banco de dados
     *
     * @param Request  $request  Requisição
     * @param Response $response Resposta
     * @param array    $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function update(Request $request, Response $response, array $args): Response
    {
        $subtopic = Subtopic::loadFromId(self::int($args["idSubtopic"]));

        if (!$subtopic) {
            return $response->withHeader("Location", "/admin/subtopics")->withStatus(302);
        }

        $page = new PageAdmin();
        $page->setTpl(
            "subtopics-update",
            [
                "subtopic" => $subtopic->array(),
                "topics" => array_map(
                    fn (Topic $topic): array => $topic->array(),
                    Topic::listAll()
                ),
                "types" => array_map(
                    fn (TopicType $topicType): array => $topicType->array(),
                    TopicType::listAll()
                )
            ]
        );

        $response->getBody()->write($page->getTpl());
        return $response;
    }
}
