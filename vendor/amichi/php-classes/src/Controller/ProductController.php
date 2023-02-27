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
use Amichi\Model\Product;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Classe que controla a entidade PRODUTO
 *
 * @category Controller
 * @package  Amichi/Controller
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class ProductController extends Controller
{
    /**
     * Retorna todos os produtos do banco de dados
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
                    fn (Product $product): array => $product->array(),
                    Product::listAll(
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
     * Retorna o produto a partir do ID informado na URL
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
        $product = Product::loadFromId(self::int($args["idProduct"], true, "idProduct"));
        $response->getBody()->write(json_encode($product));

        return $response->withStatus($product ? 200 : 204);
    }


    /**
     * Salva o produto informado no corpo da requisição no banco de dados
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
            "image" => (
                isset($files["image"]) && $files["image"]->getError() === UPLOAD_ERR_OK &&
                $files["image"]->getSize() <= 16777215 &&
                in_array($files["image"]->getClientMediaType(), ["image/jpeg", "image/png"])
                    ? (string) $files["image"]->getStream()->getContents()
                    : null
            )
        ];

        $product = Product::loadFromData($body);
        $product->validate($errors);

        if ($errors) {
            $message = count($errors) === 1 ? "O seguinte erro foi encontrado" : "Os seguintes erros foram encontrados";
            throw (new HttpException("Não foi possível cadastrar o produto. $message: " . implode(", ", $errors) . ".", 400))->json();
        }

        $response->getBody()->write(json_encode($product->save()));

        return $response->withStatus(201);
    }


    /**
     * Altera os dados do produto informado no corpo da requisição a partir do ID informado na URL
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
        $id = self::int($args["idProduct"], true, "idProduct");

        if (!Product::loadFromId($id)) {
            throw (new HttpException("Não foi possível alterar o produto $id, pois, é inexistente.", 400))->json();
        }

        $product = Product::loadFromData((array) $request->getParsedBody());
        $product->id = $id;
        $product->validate($errors);

        if ($errors) {
            $message = count($errors) === 1 ? "O seguinte erro foi encontrado" : "Os seguintes erros foram encontrados";
            throw (new HttpException("Não foi possível alterar o produto $id. $message: " . implode(", ", $errors) . ".", 400))->json();
        }

        $response->getBody()->write(json_encode($product->save()));

        return $response->withStatus(200);
    }


    /**
     * Remove o produto a partir do ID informado na URL
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
        $id = self::int($args["idProduct"], true, "idProduct");
        $product = Product::loadFromId($id);

        if (!$product) {
            throw (new HttpException("Não foi possível remover o produto $id, pois, é inexistente.", 400))->json();
        }

        $response->getBody()->write(json_encode($product->delete()));

        return $response;
    }
}
