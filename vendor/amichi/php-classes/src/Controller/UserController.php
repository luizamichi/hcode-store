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
use Amichi\Model\User;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Classe que controla a entidade USUÁRIO
 *
 * @category Controller
 * @package  Amichi/Controller
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class UserController extends Controller
{
    /**
     * Retorna todos os usuários do banco de dados
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
                    fn (User $user): array => $user->array(),
                    User::listAll(
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
     * Retorna o usuário a partir do ID informado na URL
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
        $user = User::loadFromId(self::int($args["idUser"], true, "idUser"));
        $response->getBody()->write(json_encode($user));

        return $response->withStatus($user ? 200 : 204);
    }


    /**
     * Salva o usuário informado no corpo da requisição no banco de dados
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
        $files = (array) $request->getUploadedFiles();

        $body = [
            ...(array) $request->getParsedBody(),
            "photo" => (
                isset($files["photo"]) && $files["photo"]->getError() === UPLOAD_ERR_OK &&
                $files["photo"]->getSize() <= 16777215 &&
                in_array($files["photo"]->getClientMediaType(), ["image/jpeg", "image/png"])
                    ? (string) $files["photo"]->getStream()->getContents()
                    : null
            )
        ];

        $user = User::loadFromData($body);
        $user->validate($errors);

        if ($errors) {
            $message = count($errors) === 1 ? "O seguinte erro foi encontrado" : "Os seguintes erros foram encontrados";
            throw (new HttpException("Não foi possível cadastrar o usuário. $message: " . implode(", ", $errors) . ".", 400))->json();
        }

        $response->getBody()->write(json_encode($user->create()));

        return $response->withStatus(201);
    }


    /**
     * Altera os dados do usuário informado no corpo da requisição a partir do ID informado na URL
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
        $id = self::int($args["idUser"], true, "idUser");

        $userDB = User::loadFromId($id);
        if (!$userDB) {
            throw (new HttpException("Não foi possível alterar o usuário $id, pois, é inexistente.", 400))->json();
        }

        $user = User::loadFromData((array) $request->getParsedBody());
        $user->id = $id;
        $user->idPerson = $userDB->idPerson;
        $user->password = $userDB->password;

        $user->validate($errors);

        if ($errors) {
            $message = count($errors) === 1 ? "O seguinte erro foi encontrado" : "Os seguintes erros foram encontrados";
            throw (new HttpException("Não foi possível alterar o usuário $id. $message: " . implode(", ", $errors) . ".", 400))->json();
        }

        $response->getBody()->write(json_encode($user->update()));

        return $response->withStatus(200);
    }


    /**
     * Remove o usuário a partir do ID informado na URL
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
        $id = self::int($args["idUser"], true, "idUser");

        $user = User::loadFromId($id);
        if (!$user) {
            throw (new HttpException("Não foi possível remover o usuário $id, pois, é inexistente.", 400))->json();
        }

        $response->getBody()->write(json_encode($user->delete()));

        return $response;
    }
}
