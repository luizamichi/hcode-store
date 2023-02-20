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


session_name(getenv("PHP_SESSION_NAME") ?: "Ecommerce");
session_start();

use Amichi\DB\SQL;

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


$app->get("/", function (Request $req, Response $res, array $args): Response {
    $sql = new SQL();
    $rows = $sql->send("SELECT * FROM tb_tests")->fetchAll();

    $res->getBody()->write(json_encode($rows));
    return $res;
})->add($midCORS)->add($midJSON);


$app->run();
