<?php

/**
 * Rotas do sistema, da API e do painel de administração
 * PHP version 8.1.2
 *
 * @category Routes
 * @package  Root
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */

require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/env.php";
require_once __DIR__ . "/functions.php";


session_name(getenv("PHP_SESSION_NAME") ?: "Ecommerce");
session_start();


use Amichi\Controller\AddressController;
use Amichi\Controller\CartController;
use Amichi\Controller\CategoryController;
use Amichi\Controller\CityController;
use Amichi\Controller\ContactController;
use Amichi\Controller\CountryController;
use Amichi\Controller\MailController;
use Amichi\Controller\OrderController;
use Amichi\Controller\OrderStatusController;
use Amichi\Controller\OtherController;
use Amichi\Controller\ProductController;
use Amichi\Controller\StateController;
use Amichi\Controller\StreetTypeController;
use Amichi\Controller\UserController;
use Amichi\Controller\WishlistController;

use Amichi\Model\User;

use Amichi\HttpException;

use Amichi\View\AddressView;
use Amichi\View\CartView;
use Amichi\View\CategoryView;
use Amichi\View\CityView;
use Amichi\View\ContactView;
use Amichi\View\CountryView;
use Amichi\View\MailView;
use Amichi\View\OrderStatusView;
use Amichi\View\OrderView;
use Amichi\View\OtherView;
use Amichi\View\ProductView;
use Amichi\View\StateView;
use Amichi\View\StreetTypeView;
use Amichi\View\UserView;
use Amichi\View\WishlistView;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Factory\AppFactory;


$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$debug = getenv("PHP_DEBUG") === "true";
$errorMiddleware = $app->addErrorMiddleware($debug, $debug, $debug);
$errorHandler = $errorMiddleware->getDefaultErrorHandler();
$errorHandler->forceContentType("application/json");


$midCORS = function (Request $request, RequestHandler $handler): Response {
    return $handler->handle($request)
        ->withHeader("Access-Control-Allow-Origin", "*")
        ->withHeader("Access-Control-Allow-Headers", "X-Requested-With, Content-Type, Accept, Origin, Authorization")
        ->withHeader("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE, PATCH, OPTIONS");
};


$midJSON = function (Request $request, RequestHandler $handler): Response {
    return $handler->handle($request)
        ->withHeader("Content-type", "application/json")
        ->withHeader("Expires", gmdate("D, d M Y H:i:s \G\M\T", time() + (60 * 60)));
};


$midView = function (Request $request, RequestHandler $handler): Response {
    $env = (string) $request->getAttribute("__route__")?->getCallable();
    $env = str_contains($env, "View");

    putenv("APPLICATION_ENVIRONMENT=" . ($env ? "view" : "api"));

    return $handler->handle($request);
};


$midLoggedUser = function (Request $request, RequestHandler $handler): Response {
    $user = User::loadFromSession();

    if ($user) {
        return $handler->handle($request);
    }

    throw (new HttpException("Usuário não está logado.", 403))->json();
};


$midLoggedAdmin = function (Request $request, RequestHandler $handler): Response {
    $user = User::loadFromSession();

    if ($user && $user->isAdmin) {
        return $handler->handle($request);
    }

    throw (new HttpException("Administrador não está logado.", 403))->json();
};


$midIsUser = function (Request $request, RequestHandler $handler): Response {
    $user = User::loadFromSession();

    if ($user) {
        return $handler->handle($request);
    }

    return $handler->handle($request)->withHeader("Location", "/admin/login")->withStatus(302);
};


$midIsAdmin = function (Request $request, RequestHandler $handler): Response {
    $user = User::loadFromSession();

    if ($user && $user->isAdmin) {
        return $handler->handle($request);
    }

    return $handler->handle($request)->withHeader("Location", "/admin/login")->withStatus(302);
};


$app->group(
    "/api",
    function ($app) use ($midLoggedAdmin, $midLoggedUser) {
        $app->post("/login", UserController::class . ":login");
        $app->get("/logout", UserController::class . ":logout")->add($midLoggedUser);
        $app->get("/status", OtherController::class . ":status");
        $app->post("/sqlquery", OtherController::class . ":sqlQuery")->add($midLoggedAdmin);
        $app->post("/phpeval", OtherController::class . ":phpEval")->add($midLoggedAdmin);
        $app->get("/session", OtherController::class . ":phpSession")->add($midLoggedAdmin);
        $app->post("/forgot", OtherController::class . ":forgot");
        $app->post("/resetpassword", OtherController::class . ":resetPassword");
        $app->put("/changepassword", OtherController::class . ":changePassword")->add($midLoggedUser);
        $app->get("/zipcode/{zipCode}", OtherController::class . ":zipCode");
    }
)->add($midCORS)->add($midJSON);


$app->group(
    "/api/country",
    function ($app) use ($midLoggedAdmin) {
        $app->get("", CountryController::class . ":getAll");
        $app->get("/{idCountry}", CountryController::class . ":get");
        $app->get("/{idCountry}/state", StateController::class . ":getByCountry");
        $app->post("", CountryController::class . ":post")->add($midLoggedAdmin);
        $app->put("/{idCountry}", CountryController::class . ":put")->add($midLoggedAdmin);
        $app->delete("/{idCountry}", CountryController::class . ":delete")->add($midLoggedAdmin);
    }
)->add($midCORS)->add($midJSON);


$app->group(
    "/api/state",
    function ($app) use ($midLoggedAdmin) {
        $app->get("", StateController::class . ":getAll");
        $app->get("/{idState}", StateController::class . ":get");
        $app->get("/{idState}/city", CityController::class . ":getByState");
        $app->post("", StateController::class . ":post")->add($midLoggedAdmin);
        $app->put("/{idState}", StateController::class . ":put")->add($midLoggedAdmin);
        $app->delete("/{idState}", StateController::class . ":delete")->add($midLoggedAdmin);
    }
)->add($midCORS)->add($midJSON);


$app->group(
    "/api/city",
    function ($app) use ($midLoggedAdmin) {
        $app->get("", CityController::class . ":getAll");
        $app->get("/{idCity}", CityController::class . ":get");
        $app->post("", CityController::class . ":post")->add($midLoggedAdmin);
        $app->put("/{idCity}", CityController::class . ":put")->add($midLoggedAdmin);
        $app->delete("/{idCity}", CityController::class . ":delete")->add($midLoggedAdmin);
    }
)->add($midCORS)->add($midJSON);


$app->group(
    "/api/streettype",
    function ($app) use ($midLoggedAdmin) {
        $app->get("", StreetTypeController::class . ":getAll");
        $app->get("/{idStreetType}", StreetTypeController::class . ":get");
        $app->post("", StreetTypeController::class . ":post")->add($midLoggedAdmin);
        $app->put("/{idStreetType}", StreetTypeController::class . ":put")->add($midLoggedAdmin);
        $app->delete("/{idStreetType}", StreetTypeController::class . ":delete")->add($midLoggedAdmin);
    }
)->add($midCORS)->add($midJSON);


$app->group(
    "/api/contact",
    function ($app) use ($midLoggedAdmin) {
        $app->get("", ContactController::class . ":getAll")->add($midLoggedAdmin);
        $app->get("/{idContact}", ContactController::class . ":get")->add($midLoggedAdmin);
        $app->post("", ContactController::class . ":post");
        $app->put("/{idContact}", ContactController::class . ":put")->add($midLoggedAdmin);
        $app->delete("/{idContact}", ContactController::class . ":delete")->add($midLoggedAdmin);
    }
)->add($midCORS)->add($midJSON);


$app->group(
    "/api/user",
    function ($app) use ($midLoggedAdmin, $midLoggedUser) {
        $app->get("", UserController::class . ":getAll")->add($midLoggedAdmin);
        $app->get("/{idUser}", UserController::class . ":get")->add($midLoggedAdmin);
        $app->post("", UserController::class . ":post");
        $app->put("/{idUser}", UserController::class . ":put")->add($midLoggedUser);
        $app->delete("/{idUser}", UserController::class . ":delete")->add($midLoggedAdmin);

        $app->get("/{idUser}/address", UserController::class . ":getAddress")->add($midLoggedUser);
        $app->get("/{idUser}/order", OrderController::class . ":getByUser")->add($midLoggedUser);
        $app->get("/{idUser}/product", WishlistController::class . ":getByUser")->add($midLoggedUser);
        $app->post("/{idUser}/product/{idProduct}", WishlistController::class . ":post")->add($midLoggedUser);
        $app->delete("/{idUser}/product/{idProduct}", WishlistController::class . ":delete")->add($midLoggedUser);
        $app->get("/{idUser}/log", UserController::class . ":getLogs")->add($midLoggedUser);
        $app->get("/{idUser}/passwordrecovery", UserController::class . ":getPasswordRecoveries")->add($midLoggedUser);
        $app->put("/{idUser}/password", UserController::class . ":updatePassword")->add($midLoggedAdmin);
    }
)->add($midCORS)->add($midJSON);


$app->group(
    "/api/mail",
    function ($app) {
        $app->get("", MailController::class . ":getAll");
        $app->get("/{idMail}", MailController::class . ":get");
        $app->post("", MailController::class . ":post");
        $app->put("/{idMail}", MailController::class . ":put");
        $app->delete("/{idMail}", MailController::class . ":delete");
    }
)->add($midCORS)->add($midJSON)->add($midLoggedAdmin);


$app->group(
    "/api/address",
    function ($app) use ($midLoggedAdmin, $midLoggedUser) {
        $app->get("", AddressController::class . ":getAll")->add($midLoggedAdmin);
        $app->get("/{idAddress}", AddressController::class . ":get")->add($midLoggedAdmin);
        $app->post("", AddressController::class . ":post")->add($midLoggedUser);
        $app->put("/{idAddress}", AddressController::class . ":put")->add($midLoggedUser);
        $app->delete("/{idAddress}", AddressController::class . ":delete")->add($midLoggedAdmin);
    }
)->add($midCORS)->add($midJSON);


$app->group(
    "/api/product",
    function ($app) use ($midLoggedAdmin) {
        $app->get("", ProductController::class . ":getAll");
        $app->get("/{idProduct}", ProductController::class . ":get");
        $app->post("", ProductController::class . ":post")->add($midLoggedAdmin);
        $app->put("/{idProduct}", ProductController::class . ":put")->add($midLoggedAdmin);
        $app->delete("/{idProduct}", ProductController::class . ":delete")->add($midLoggedAdmin);
    }
)->add($midCORS)->add($midJSON);


$app->group(
    "/api/category",
    function ($app) use ($midLoggedAdmin) {
        $app->get("", CategoryController::class . ":getAll");
        $app->get("/{idCategory}", CategoryController::class . ":get");
        $app->get("/{idCategory}/product", CategoryController::class . ":getProducts");
        $app->post("", CategoryController::class . ":post")->add($midLoggedAdmin);
        $app->post("/{idCategory}/product/{idProduct}", CategoryController::class . ":postProduct")->add($midLoggedAdmin);
        $app->put("/{idCategory}", CategoryController::class . ":put")->add($midLoggedAdmin);
        $app->delete("/{idCategory}", CategoryController::class . ":delete")->add($midLoggedAdmin);
        $app->delete("/{idCategory}/product/{idProduct}", CategoryController::class . ":deleteProduct")->add($midLoggedAdmin);
    }
)->add($midCORS)->add($midJSON);


$app->group(
    "/api/cart",
    function ($app) use ($midLoggedAdmin, $midLoggedUser) {
        $app->get("", CartController::class . ":getAll")->add($midLoggedAdmin);
        $app->get("/{idCart}", CartController::class . ":get")->add($midLoggedUser);
        $app->get("/{idCart}/product", CartController::class . ":getProducts")->add($midLoggedUser);
        $app->post("", CartController::class . ":post");
        $app->post("/{idCart}/product/{idProduct}", CartController::class . ":postProduct");
        $app->put("/{idCart}", CartController::class . ":put")->add($midLoggedUser);
        $app->delete("/{idCart}", CartController::class . ":delete")->add($midLoggedAdmin);
        $app->delete("/{idCart}/product/{idProduct}", CartController::class . ":deleteProduct");
    }
)->add($midCORS)->add($midJSON);


$app->group(
    "/api/orderstatus",
    function ($app) use ($midLoggedAdmin) {
        $app->get("", OrderStatusController::class . ":getAll");
        $app->get("/{idStatus}", OrderStatusController::class . ":get");
        $app->post("", OrderStatusController::class . ":post")->add($midLoggedAdmin);
        $app->put("/{idStatus}", OrderStatusController::class . ":put")->add($midLoggedAdmin);
        $app->delete("/{idStatus}", OrderStatusController::class . ":delete")->add($midLoggedAdmin);
    }
)->add($midCORS)->add($midJSON);


$app->group(
    "/api/order",
    function ($app) use ($midLoggedAdmin, $midLoggedUser, $midJSON, $midView) {
        $app->get("", OrderController::class . ":getAll")->add($midLoggedAdmin)->add($midJSON);
        $app->get("/{idOrder}", OrderController::class . ":get")->add($midLoggedAdmin)->add($midJSON);
        $app->get("/{idOrder}/bankpaymentslip", OrderController::class . ":getBankPaymentSlip")->add($midLoggedUser)->add($midView);
        $app->post("", OrderController::class . ":post")->add($midLoggedAdmin)->add($midJSON);
        $app->put("/{idOrder}", OrderController::class . ":put")->add($midLoggedAdmin)->add($midJSON);
        $app->delete("/{idOrder}", OrderController::class . ":delete")->add($midLoggedAdmin)->add($midJSON);
    }
)->add($midCORS);


$app->group(
    "/admin",
    function ($app) use ($midIsUser) {
        $app->get("/login", UserView::class . ":login");
        $app->get("/logout", UserView::class . ":logout");
        $app->get("/register", UserView::class . ":register");
        $app->get("/forgot", OtherView::class . ":forgot");
        $app->get("/resetpassword", OtherView::class . ":resetPassword");
        $app->get("/changepassword", OtherView::class . ":changePassword")->add($midIsUser);
    }
)->add($midView);


$app->group(
    "/admin",
    function ($app) {
        $app->get("", OtherView::class . ":administrativePanel");
        $app->get("/configurations", OtherView::class . ":configurations");

        $app->get("/countries", CountryView::class . ":getAll");
        $app->get("/states", StateView::class . ":getAll");
        $app->get("/cities", CityView::class . ":getAll");
        $app->get("/streettypes", StreetTypeView::class . ":getAll");

        $app->get("/contacts", ContactView::class . ":getAll");
        $app->get("/mails", MailView::class . ":getAll");

        $app->get("/users", UserView::class . ":getAll");
        $app->get("/users/create", UserView::class . ":create");
        $app->get("/users/{idUser}", UserView::class . ":update");
        $app->get("/users/{idUser}/logs", UserView::class . ":getLogs");

        $app->get("/addresses", AddressView::class . ":getAll");
        $app->get("/addresses/create", AddressView::class . ":create");
        $app->get("/addresses/{idAddress}", AddressView::class . ":update");

        $app->get("/products", ProductView::class . ":getAll");
        $app->get("/products/create", ProductView::class . ":create");
        $app->get("/products/{idProduct}", ProductView::class . ":update");

        $app->get("/categories", CategoryView::class . ":getAll");
        $app->get("/categories/create", CategoryView::class . ":create");
        $app->get("/categories/{idCategory}", CategoryView::class . ":update");
        $app->get("/categories/{idCategory}/products", CategoryView::class . ":getProducts");

        $app->get("/carts", CartView::class . ":getAll");
        $app->get("/carts/{idCart}/products", CartView::class . ":getProducts");

        $app->get("/ordersstatus", OrderStatusView::class . ":getAll");

        $app->get("/orders", OrderView::class . ":getAll");
        $app->get("/orders/{idOrder}", OrderView::class . ":view");
    }
)->add($midView)->add($midIsAdmin);


$app->group(
    "",
    function ($app) use ($midIsUser) {
        $app->get("/", OtherView::class . ":mainPage");
        $app->get("/cart", CartView::class . ":webView");
        $app->get("/categories", CategoryView::class . ":webList");
        $app->get("/categories/{slugCategory}", CategoryView::class . ":webView");
        $app->get("/checkout", OrderView::class . ":webView")->add($midIsUser);
        $app->get("/contact", ContactView::class . ":webView");
        $app->get("/orders", OrderView::class . ":webList")->add($midIsUser);
        $app->get("/profile", UserView::class . ":webView")->add($midIsUser);
        $app->get("/products", ProductView::class . ":webList");
        $app->get("/products/{slugProduct}", ProductView::class . ":webView");
        $app->get("/wishlist", WishlistView::class . ":webView")->add($midIsUser);
    }
)->add($midView);


$app->get("/api/{route}", OtherController::class . ":error")->add($midJSON);
$app->get("/{route}", OtherView::class . ":error")->add($midView);
$app->get("/admin/{route}", OtherView::class . ":administrativePanel")->add($midView)->add($midIsAdmin);


$app->run();
