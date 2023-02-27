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
use Amichi\Model\Category;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Classe que controla a entidade CATEGORIA
 *
 * @category Controller
 * @package  Amichi/Controller
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class CategoryController extends Controller
{
    /**
     * Retorna todas as categorias do banco de dados
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
                    fn (Category $category): array => $category->array(),
                    Category::listAll(
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
     * Retorna a categoria a partir do ID informado na URL
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
        $category = Category::loadFromId(self::int($args["idCategory"], true, "idCategory"));
        $response->getBody()->write(json_encode($category));

        return $response->withStatus($category ? 200 : 204);
    }


    /**
     * Salva a categoria informada no corpo da requisição no banco de dados
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
        $category = Category::loadFromData((array) $request->getParsedBody());
        $category->validate($errors);

        if ($errors) {
            $message = count($errors) === 1 ? "O seguinte erro foi encontrado" : "Os seguintes erros foram encontrados";
            throw (new HttpException("Não foi possível cadastrar a categoria. $message: " . implode(", ", $errors) . ".", 400))->json();
        }

        $response->getBody()->write(json_encode($category->save()));

        return $response->withStatus(201);
    }


    /**
     * Altera os dados da categoria informada no corpo da requisição a partir do ID informado na URL
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
        $id = self::int($args["idCategory"], true, "idCategory");

        if (!Category::loadFromId($id)) {
            throw (new HttpException("Não foi possível alterar a categoria $id, pois, é inexistente.", 400))->json();
        }

        $category = Category::loadFromData((array) $request->getParsedBody());
        $category->id = $id;
        $category->validate($errors);

        $idSuper = $category->idSuper;
        while ($superCategory = Category::loadFromId((int) $idSuper)) {
            if ($superCategory->id === $category->id) {
                throw (new HttpException("Não foi possível alterar a categoria $id, pois, a categoria mãe entra em loop.", 400))->json();
            }

            $idSuper = $superCategory->idSuper;
        }

        if ($errors) {
            $message = count($errors) === 1 ? "O seguinte erro foi encontrado" : "Os seguintes erros foram encontrados";
            throw (new HttpException("Não foi possível alterar a categoria $id. $message: " . implode(", ", $errors) . ".", 400))->json();
        }

        $response->getBody()->write(json_encode($category->save()));

        return $response->withStatus(200);
    }


    /**
     * Remove a categoria a partir do ID informado na URL
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
        $id = self::int($args["idCategory"], true, "idCategory");
        $category = Category::loadFromId($id);

        if (!$category) {
            throw (new HttpException("Não foi possível remover a categoria $id, pois, é inexistente.", 400))->json();
        }

        $response->getBody()->write(json_encode($category->delete()));

        return $response;
    }
}
