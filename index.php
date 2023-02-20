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


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;


$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$debug = getenv("PHP_DEBUG") === "true";
$errorMiddleware = $app->addErrorMiddleware($debug, $debug, $debug);
$errorHandler = $errorMiddleware->getDefaultErrorHandler();
$errorHandler->forceContentType("application/json");


$app->get("/", function (Request $req, Response $res, array $args): Response {
    $res->getBody()->write("Hcode Store");
    return $res;
});


$app->run();
