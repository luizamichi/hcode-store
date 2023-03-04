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
use Amichi\Enumerated\OrderStatus as EnumeratedOrderStatus;
use Amichi\Model\Address;
use Amichi\Model\Cart;
use Amichi\Model\Order;
use Amichi\Model\OrderStatus;
use Amichi\Model\Product;
use Amichi\Model\User;
use Amichi\Page;
use Amichi\PageAdmin;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Classe que controla as views da entidade PEDIDO
 *
 * @category View
 * @package  Amichi/View
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class OrderView extends Controller
{
    /**
     * Retorna o template da lista de todos os pedidos do banco de dados
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
            "orders",
            [
                "orders" => array_map(
                    fn (Order $order): array => $order->array(),
                    array_reverse(
                        Order::listAll(
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
     * Retorna o template para visualização de pedido
     *
     * @param Request  $request  Requisição
     * @param Response $response Resposta
     * @param array    $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function view(Request $request, Response $response, array $args): Response
    {
        $order = Order::loadFromId(self::int($args["idOrder"]));

        if (!$order) {
            return $response->withHeader("Location", "/admin/orders")->withStatus(302);
        }

        $page = new PageAdmin();
        $page->setTpl(
            "orders-view",
            [
                "order" => $order->array(),
                "products" => array_map(
                    fn (Product $product): array => $product->array(),
                    Cart::loadFromId($order->idCart)->getProducts()
                ),
                "status" => array_map(
                    fn (OrderStatus $status): array => $status->array(),
                    OrderStatus::listAll()
                )
            ]
        );

        $response->getBody()->write($page->getTpl());
        return $response;
    }


    /**
     * Retorna o template da página do pedido
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
        $cart = Cart::loadFromSession();
        $user = User::loadFromSession();
        $address = Address::loadFromUserId($user?->id ?? 0);
        $order = null;

        if ($cart && count($cart->products) > 0 && $user && $address) {
            $order = Order::loadFromCartId($cart->id);
            if (!$order) {
                $order = Order::loadFromData(
                    [
                        "idCart" => $cart->id,
                        "idUser" => $user->id,
                        "idStatus" => !$user?->cpf || !$address?->id ? EnumeratedOrderStatus::OPEN_ORDER->value : EnumeratedOrderStatus::AWAITING_PAYMENT->value,
                        "idAddress" => $address?->id,
                        "totalValue" => $cart->totalPrice
                    ]
                );

                $order->save();
            }
        } else {
            return $response->withHeader("Location", "/cart")->withStatus(302);
        }

        $page = new Page();
        $page->setTpl(
            "checkout",
            [
                "order" => $order?->array()
            ]
        );

        $response->getBody()->write($page->getTpl());
        return $response;
    }


    /**
     * Retorna o template da página de pedidos do usuário
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
        $page = new Page();
        $page->setTpl(
            "orders",
            [
                "orders" => array_map(
                    fn (Order $order): array => $order->array(),
                    Order::listFromUserId(User::loadFromSession()?->id ?? 0)
                )
            ]
        );

        $response->getBody()->write($page->getTpl());
        return $response;
    }


    /**
     * Retorna o template da página de redirecionamento do PagSeguro
     *
     * @param Request  $request  Requisição
     * @param Response $response Resposta
     * @param array    $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function pagSeguro(Request $request, Response $response, array $args): Response
    {
        $order = Order::loadFromCode(self::string($args["codeOrder"]));

        if (!$order || $order->user->id !== User::loadFromSession()?->id || $order->expired()) {
            return $response->withHeader("Location", "/checkout")->withStatus(302);
        }

        $page = new Page(
            [
                "header" => false,
                "footer" => false
            ]
        );
        $page->setTpl("pagseguro", ["order" => $order->array()]);

        $response->getBody()->write($page->getTpl());
        return $response;
    }


    /**
     * Retorna o template da página de redirecionamento do PayPal
     *
     * @param Request  $request  Requisição
     * @param Response $response Resposta
     * @param array    $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function payPal(Request $request, Response $response, array $args): Response
    {
        $order = Order::loadFromCode(self::string($args["codeOrder"]));

        if (!$order || $order->user->id !== User::loadFromSession()?->id || $order->expired()) {
            return $response->withHeader("Location", "/checkout")->withStatus(302);
        }

        $page = new Page(
            [
                "header" => false,
                "footer" => false
            ]
        );
        $page->setTpl("paypal", ["order" => $order->array()]);

        $response->getBody()->write($page->getTpl());
        return $response;
    }
}
