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
use Amichi\Model\Category;
use Amichi\Model\Product;
use Amichi\Page;
use Amichi\PageAdmin;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Classe que controla as views da entidade CATEGORIA
 *
 * @category View
 * @package  Amichi/View
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class CategoryView extends Controller
{
    /**
     * Retorna o template da lista de todas as categorias do banco de dados
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

        $page = new PageAdmin();
        $page->setTpl(
            "categories",
            [
                "categories" => array_map(
                    fn (Category $category): array => $category->array(),
                    Category::listAll(
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
     * Retorna o template da lista de todos os produtos da categoria
     *
     * @param Request       $request  Requisição
     * @param Response      $response Resposta
     * @param array<string> $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function getProducts(Request $request, Response $response, array $args): Response
    {
        $category = Category::loadFromId(self::int($args["idCategory"]));

        if (!$category) {
            return $response->withHeader("Location", "/admin/categories")->withStatus(302);
        }

        $page = new PageAdmin();
        $page->setTpl(
            "categories-products",
            [
                "category" => $category->array(),
                "productsRelated" => array_map(
                    fn (Product $product): array => $product->array(),
                    $category->getProducts()
                ),
                "productsNotRelated" => array_map(
                    fn (Product $product): array => $product->array(),
                    $category->getProducts(false)
                )
            ]
        );

        $response->getBody()->write($page->getTpl());
        return $response;
    }


    /**
     * Retorna o template para cadastro de categoria no banco de dados
     *
     * @param Request       $request  Requisição
     * @param Response      $response Resposta
     * @param array<string> $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function create(Request $request, Response $response, array $args): Response
    {
        $page = new PageAdmin();
        $page->setTpl(
            "categories-create",
            [
                "categories" => array_map(
                    fn (Category $category): array => $category->array(),
                    Category::listAll()
                )
            ]
        );

        $response->getBody()->write($page->getTpl());
        return $response;
    }


    /**
     * Retorna o template para alteração de categoria no banco de dados
     *
     * @param Request       $request  Requisição
     * @param Response      $response Resposta
     * @param array<string> $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function update(Request $request, Response $response, array $args): Response
    {
        $category = Category::loadFromId(self::int($args["idCategory"]));

        if (!$category) {
            return $response->withHeader("Location", "/admin/categories")->withStatus(302);
        }

        $page = new PageAdmin();
        $page->setTpl(
            "categories-update",
            [
                "category" => $category->array(),
                "categories" => array_map(
                    fn (Category $category): array => $category->array(),
                    Category::listAll()
                )
            ]
        );

        $response->getBody()->write($page->getTpl());
        return $response;
    }


    /**
     * Retorna o template da página de categorias
     *
     * @param Request       $request  Requisição
     * @param Response      $response Resposta
     * @param array<string> $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function webList(Request $request, Response $response, array $args): Response
    {
        $params = $request->getQueryParams();

        $pageNumber = max(1, self::int($args["page"]));
        $count = Category::count();
        $limit = 15;

        $pageNumber = $pageNumber * $limit <= $count ? $pageNumber : ceil($count / $limit);
        $offset = ($pageNumber - 1) * $limit;

        $page = new Page();
        $page->setTpl(
            "categories",
            [
                "categories" => array_map(
                    fn (Category $category): array => $category->array(),
                    Category::listAll(
                        $limit,
                        (int) $offset,
                        self::string($params["_sortBy"])
                    )
                ),
                "page" => $pageNumber,
                "pages" => range(1, ceil($count / $limit)),
                "count" => $count
            ]
        );

        $response->getBody()->write($page->getTpl());
        return $response;
    }


    /**
     * Retorna o template da página de produtos da categoria
     *
     * @param Request       $request  Requisição
     * @param Response      $response Resposta
     * @param array<string> $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function webView(Request $request, Response $response, array $args): Response
    {
        $category = Category::loadFromSlug(self::string($args["slugCategory"]));

        if (!$category) {
            return $response->withHeader("Location", "/categories")->withStatus(302);
        }

        $page = new Page();
        $page->setTpl(
            "category",
            [
                "category" => $category->array(),
                "products" => array_map(
                    fn (Product $product): array => $product->array(),
                    $category->getProducts()
                )
            ]
        );

        $response->getBody()->write($page->getTpl());
        return $response;
    }
}
