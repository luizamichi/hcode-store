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
use Amichi\Page;
use Amichi\PageAdmin;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Classe que controla as views da entidade TÓPICO
 *
 * @category View
 * @package  Amichi/View
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class TopicView extends Controller
{
    /**
     * Retorna o template da lista de todos os tópicos do banco de dados
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
            "topics",
            [
                "topics" => array_map(
                    fn (Topic $topic): array => $topic->array(),
                    Topic::listAll(
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
     * Retorna o template para cadastro de tópico no banco de dados
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
            "topics-create",
            [
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
     * Retorna o template para alteração de tópico no banco de dados
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
        $topic = Topic::loadFromId(self::int($args["idTopic"]));

        if (!$topic) {
            return $response->withHeader("Location", "/admin/topics")->withStatus(302);
        }

        $page = new PageAdmin();
        $page->setTpl(
            "topics-update",
            [
                "topic" => $topic->array(),
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
     * Retorna o template da página de tópicos e subtópicos
     *
     * @param Request  $request  Requisição
     * @param Response $response Resposta
     * @param array    $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function webView(Request $request, Response $response, array $args): Response
    {
        $topicType = TopicType::loadFromSlug(self::string($args["slugTopicType"]));

        if (!$topicType) {
            return $response->withHeader("Location", "/posts")->withStatus(302);
        }

        $page = new Page();
        $page->setTpl(
            "topics",
            [
                "topicType" => $topicType->array(),
                "topics" => [
                    ...array_filter(
                        array_map(
                            fn (Topic $topic): array => $topic->subtopics ? $topic->array() : [],
                            $topicType->getTopics()
                        )
                    ),
                    ...array_map(
                        fn (Subtopic $subtopic): array => $subtopic->array(false),
                        $topicType->getSubtopics()
                    )
                ]
            ]
        );

        $response->getBody()->write($page->getTpl());
        return $response;
    }
}
