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


use Amichi\Controller\CityController;
use Amichi\Controller\ContactController;
use Amichi\Controller\CountryController;
use Amichi\Controller\OtherController;
use Amichi\Controller\StateController;
use Amichi\Controller\StreetTypeController;
use Amichi\Controller\UserController;

use Amichi\Model\User;

use Amichi\HttpException;

use Amichi\View\CityView;
use Amichi\View\ContactView;
use Amichi\View\CountryView;
use Amichi\View\OtherView;
use Amichi\View\StateView;
use Amichi\View\StreetTypeView;
use Amichi\View\UserView;

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
    }
)->add($midCORS)->add($midJSON);


$app->group(
    "/admin",
    function ($app) {
        $app->get("/login", UserView::class . ":login");
        $app->get("/logout", UserView::class . ":logout");
        $app->get("/register", UserView::class . ":register");
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

        $app->get("/users", UserView::class . ":getAll");
        $app->get("/users/create", UserView::class . ":create");
        $app->get("/users/{idUser}", UserView::class . ":update");
    }
)->add($midView)->add($midIsAdmin);


$app->group(
    "",
    function ($app) {
        $app->get("/contact", ContactView::class . ":webView");
    }
)->add($midView);


$app->get("/api/{route}", OtherController::class . ":error")->add($midJSON);
$app->get("/{route}", OtherView::class . ":error")->add($midView);
$app->get("/admin/{route}", OtherView::class . ":administrativePanel")->add($midView)->add($midIsAdmin);


$app->run();
