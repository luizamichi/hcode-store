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

use Amichi\View\CityView;
use Amichi\View\ContactView;
use Amichi\View\CountryView;
use Amichi\View\StateView;
use Amichi\View\StreetTypeView;

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


$app->group(
    "/api",
    function ($app) {
        $app->get("/zipcode/{zipCode}", OtherController::class . ":zipCode");
    }
)->add($midCORS)->add($midJSON);


$app->group(
    "/api/country",
    function ($app) {
        $app->get("", CountryController::class . ":getAll");
        $app->get("/{idCountry}", CountryController::class . ":get");
        $app->get("/{idCountry}/state", StateController::class . ":getByCountry");
        $app->post("", CountryController::class . ":post");
        $app->put("/{idCountry}", CountryController::class . ":put");
        $app->delete("/{idCountry}", CountryController::class . ":delete");
    }
)->add($midCORS)->add($midJSON);


$app->group(
    "/api/state",
    function ($app) {
        $app->get("", StateController::class . ":getAll");
        $app->get("/{idState}", StateController::class . ":get");
        $app->get("/{idState}/city", CityController::class . ":getByState");
        $app->post("", StateController::class . ":post");
        $app->put("/{idState}", StateController::class . ":put");
        $app->delete("/{idState}", StateController::class . ":delete");
    }
)->add($midCORS)->add($midJSON);


$app->group(
    "/api/city",
    function ($app) {
        $app->get("", CityController::class . ":getAll");
        $app->get("/{idCity}", CityController::class . ":get");
        $app->post("", CityController::class . ":post");
        $app->put("/{idCity}", CityController::class . ":put");
        $app->delete("/{idCity}", CityController::class . ":delete");
    }
)->add($midCORS)->add($midJSON);


$app->group(
    "/api/streettype",
    function ($app) {
        $app->get("", StreetTypeController::class . ":getAll");
        $app->get("/{idStreetType}", StreetTypeController::class . ":get");
        $app->post("", StreetTypeController::class . ":post");
        $app->put("/{idStreetType}", StreetTypeController::class . ":put");
        $app->delete("/{idStreetType}", StreetTypeController::class . ":delete");
    }
)->add($midCORS)->add($midJSON);


$app->group(
    "/api/contact",
    function ($app) {
        $app->get("", ContactController::class . ":getAll");
        $app->get("/{idContact}", ContactController::class . ":get");
        $app->post("", ContactController::class . ":post");
        $app->put("/{idContact}", ContactController::class . ":put");
        $app->delete("/{idContact}", ContactController::class . ":delete");
    }
)->add($midCORS)->add($midJSON);


$app->group(
    "/admin",
    function ($app) {
        $app->get("/countries", CountryView::class . ":getAll");
        $app->get("/states", StateView::class . ":getAll");
        $app->get("/cities", CityView::class . ":getAll");
        $app->get("/streettypes", StreetTypeView::class . ":getAll");

        $app->get("/contacts", ContactView::class . ":getAll");
    }
)->add($midView);


$app->group(
    "",
    function ($app) {
        $app->get("/contact", ContactView::class . ":webView");
    }
)->add($midView);


$app->run();
