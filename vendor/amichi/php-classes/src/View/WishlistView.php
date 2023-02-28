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
use Amichi\Model\User;
use Amichi\Model\Wishlist;
use Amichi\Page;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Classe que controla as views da entidade LISTA DE DESEJOS
 *
 * @category View
 * @package  Amichi/View
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class WishlistView extends Controller
{
    /**
     * Retorna o template da página da lista de desejos
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
        $products = array_map(
            fn (Product $product): array => $product->array(),
            Wishlist::getProducts(User::loadFromSession()?->id ?? 0)
        );

        $page = new Page();
        $page->setTpl("wishlist", ["products" => $products]);

        $response->getBody()->write($page->getTpl());
        return $response;
    }
}
