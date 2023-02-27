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
use Amichi\Model\Product;
use Amichi\Page;
use Amichi\PageAdmin;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Classe que controla as views da entidade PRODUTO
 *
 * @category View
 * @package  Amichi/View
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class ProductView extends Controller
{
    /**
     * Retorna o template da lista de todos os produtos do banco de dados
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
            "products",
            [
                "products" => array_map(
                    fn (Product $product): array => $product->array(),
                    Product::listAll(
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
     * Retorna o template para cadastro de produto no banco de dados
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
        $page->setTpl("products-create");

        $response->getBody()->write($page->getTpl());
        return $response;
    }


    /**
     * Retorna o template para alteração de produto no banco de dados
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
        $product = Product::loadFromId(self::int($args["idProduct"]));

        if (!$product) {
            return $response->withHeader("Location", "/admin/products")->withStatus(302);
        }

        $page = new PageAdmin();
        $page->setTpl("products-update", ["product" => $product->array()]);

        $response->getBody()->write($page->getTpl());
        return $response;
    }


    /**
     * Retorna o template da página de produtos
     *
     * @param Request  $request  Requisição
     * @param Response $response Resposta
     * @param array    $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function webList(Request $request, Response $response, array $args): Response
    {
        $params = $request->getQueryParams();

        $search = self::string($params["search"]);
        $pageNumber = max(1, self::int($params["page"]));
        $count = Product::count();
        $limit = 15;

        $pageNumber = $pageNumber * $limit <= $count ? $pageNumber : ceil($count / $limit);
        $offset = ($pageNumber - 1) * $limit;

        $page = new Page();
        $page->setTpl(
            "products",
            [
                "products" => array_filter(
                    array_map(
                        fn (Product $product): ?array => str_contains(mb_strtoupper($product->name), mb_strtoupper($search)) ? $product->array() : null,
                        Product::listAll(
                            $limit,
                            $offset,
                            self::string($params["_sortBy"])
                        )
                    )
                ),
                "search" => $search,
                "page" => $pageNumber,
                "pages" => range(1, ceil($count / $limit)),
                "count" => $count
            ]
        );

        $response->getBody()->write($page->getTpl());
        return $response;
    }


    /**
     * Retorna o template da página de produto
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
        $product = Product::loadFromSlug(self::string($args["slugProduct"]));

        if (!$product) {
            return $response->withHeader("Location", "/products")->withStatus(302);
        }

        $page = new Page();
        $page->setTpl("product", ["product" => $product->array()]);

        $response->getBody()->write($page->getTpl());
        return $response;
    }
}
