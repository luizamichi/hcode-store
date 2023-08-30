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
use Amichi\Model\Cart;
use Amichi\Model\Product;
use Amichi\Page;
use Amichi\PageAdmin;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Classe que controla as views da entidade CARRINHO
 *
 * @category View
 * @package  Amichi/View
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class CartView extends Controller
{
    /**
     * Retorna o template da lista de todos os carrinho do banco de dados
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
            "carts",
            [
                "carts" => array_map(
                    fn (Cart $cart): array => $cart->array(),
                    array_reverse(
                        Cart::listAll(
                            self::int($params["_limit"]),
                            self::int($params["_offset"]),
                            self::string($params["_sortBy"])
                        )
                    )
                )
            ]
        );

        $response->getBody()->write($page->getTpl());
        return $response;
    }


    /**
     * Retorna o template da lista de todos os produtos do carrinho
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
        $cart = Cart::loadFromId(self::int($args["idCart"]));

        if (!$cart) {
            return $response->withHeader("Location", "/admin/carts")->withStatus(302);
        }

        $page = new PageAdmin();
        $page->setTpl(
            "carts-products",
            [
                "cart" => $cart->array(),
                "productsAdded" => array_map(
                    fn (Product $product): array => $product->array(),
                    $cart->getProducts()
                ),
                "productsRemoved" => array_map(
                    fn (Product $product): array => $product->array(),
                    $cart->getProducts(false)
                )
            ]
        );

        $response->getBody()->write($page->getTpl());
        return $response;
    }


    /**
     * Retorna o template da página do carrinho
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
        $cart = Cart::loadFromSession();
        $cart->refresh();

        $page = new Page();
        $page->setTpl(
            "cart",
            [
                "cart" => $cart->array()
            ]
        );

        $response->getBody()->write($page->getTpl());
        return $response;
    }
}
