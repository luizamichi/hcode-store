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
use Amichi\Model\Mail;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Classe que controla a entidade E-MAIL
 *
 * @category Controller
 * @package  Amichi/Controller
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class MailController extends Controller
{
    /**
     * Retorna todos os e-mails do banco de dados
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
                    fn (Mail $mail): array => $mail->array(),
                    Mail::listAll(
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
     * Retorna o e-mail a partir do ID informado na URL
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
        $mail = Mail::loadFromId(self::int($args["idMail"], true, "idMail"));
        $response->getBody()->write(json_encode($mail));

        return $response->withStatus($mail ? 200 : 204);
    }


    /**
     * Salva o e-mail informado no corpo da requisição no banco de dados e tenta enviar
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
        $mail = Mail::loadFromData((array) $request->getParsedBody());
        $mail->validate($errors);

        if ($errors) {
            $message = count($errors) === 1 ? "O seguinte erro foi encontrado" : "Os seguintes erros foram encontrados";
            throw (new HttpException("Não foi possível enviar o e-mail. $message: " . implode(", ", $errors) . ".", 400))->json();
        }

        $response->getBody()->write(json_encode($mail->send()));

        return $response->withStatus(201);
    }


    /**
     * Altera os dados do e-mail informado no corpo da requisição a partir do ID informado na URL e envia o e-mail
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
        $id = self::int($args["idMail"], true, "idMail");
        $mail = Mail::loadFromId($id);

        if (!$mail) {
            throw (new HttpException("Não foi possível alterar o e-mail $id, pois, é inexistente.", 400))->json();
        }

        if ($mail->isSent) {
            throw (new HttpException("Não foi possível reenviar o e-mail $id, pois, já foi enviado.", 400))->json();
        }

        $mail = Mail::loadFromData((array) $request->getParsedBody());
        $mail->id = $id;
        $mail->validate($errors);

        if ($errors) {
            $message = count($errors) === 1 ? "O seguinte erro foi encontrado" : "Os seguintes erros foram encontrados";
            throw (new HttpException("Não foi possível alterar o e-mail $id. $message: " . implode(", ", $errors) . ".", 400))->json();
        }

        $response->getBody()->write(json_encode($mail->send()));

        return $response->withStatus(200);
    }


    /**
     * Remove o e-mail a partir do ID informado na URL
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
        $id = self::int($args["idMail"], true, "idMail");
        $mail = Mail::loadFromId($id);

        if (!$mail) {
            throw (new HttpException("Não foi possível remover o e-mail $id, pois, é inexistente.", 400))->json();
        }

        $response->getBody()->write(json_encode($mail->delete()));

        return $response;
    }
}
