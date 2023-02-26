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
use Amichi\HttpException;
use Amichi\Model\UserPasswordRecovery;
use Amichi\Page;
use Amichi\PageAdmin;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Classe que controla as views diversas
 *
 * @category View
 * @package  Amichi/View
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class OtherView extends Controller
{
    /**
     * Retorna o template da página inicial do painel administrativo
     *
     * @param Request  $request  Requisição
     * @param Response $response Resposta
     * @param array    $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function administrativePanel(Request $request, Response $response, array $args): Response
    {
        $page = new PageAdmin();
        $page->setTpl("index");

        $response->getBody()->write($page->getTpl());
        return $response;
    }


    /**
     * Retorna o template das configurações
     *
     * @param Request  $request  Requisição
     * @param Response $response Resposta
     * @param array    $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function configurations(Request $request, Response $response, array $args): Response
    {
        $index = 0;

        $page = new PageAdmin();
        $page->setTpl(
            "configurations",
            [
                "configurations" => array_map(
                    function (array $configuration) use (&$index): array {
                        $configuration["index"] = ++$index;
                        return $configuration;
                    },
                    [
                        [
                            "key" => "PHP Debug",
                            "value" => getenv("PHP_DEBUG")
                        ],
                        [
                            "key" => "PHP Hostname",
                            "value" => $_SERVER["HTTP_HOST"]
                        ],
                        [
                            "key" => "PHP Session Name",
                            "value" => getenv("PHP_SESSION_NAME")
                        ],
                        [
                            "key" => "SMTP Hostname",
                            "value" => getenv("SMTP_EMAIL_HOSTNAME")
                        ],
                        [
                            "key" => "SMTP E-mail Address",
                            "value" => getenv("SMTP_EMAIL_ADDRESS")
                        ],
                        [
                            "key" => "SMTP Name From",
                            "value" => getenv("SMTP_EMAIL_NAME_FROM")
                        ],
                        [
                            "key" => "SMTP E-mail Reply",
                            "value" => getenv("SMTP_EMAIL_REPLY")
                        ],
                        [
                            "key" => "SMTP Debug",
                            "value" => getenv("SMTP_DEBUG")
                        ],
                        [
                            "key" => "SMTP Port",
                            "value" => getenv("SMTP_PORT")
                        ],
                        [
                            "key" => "SMTP Secure",
                            "value" => getenv("SMTP_SECURE")
                        ],
                        [
                            "key" => "SMTP Auth",
                            "value" => getenv("SMTP_AUTH")
                        ],
                        [
                            "key" => "SQL Hostname",
                            "value" => getenv("MYSQL_HOSTNAME")
                        ],
                        [
                            "key" => "SQL Driver",
                            "value" => getenv("MYSQL_DRIVER")
                        ],
                        [
                            "key" => "SQL Schema",
                            "value" => getenv("MYSQL_SCHEMA")
                        ],
                        [
                            "key" => "SQL Port",
                            "value" => getenv("MYSQL_PORT")
                        ],
                        [
                            "key" => "Razão Social",
                            "value" => getenv("ENTERPRISE_NAME")
                        ],
                        [
                            "key" => "CNPJ",
                            "value" => getenv("ENTERPRISE_CNPJ")
                        ],
                        [
                            "key" => "Endereço",
                            "value" => getenv("ENTERPRISE_ADDRESS")
                        ],
                        [
                            "key" => "Localidade",
                            "value" => getenv("ENTERPRISE_CITY") . ", " . getenv("ENTERPRISE_FU")
                        ],
                        [
                            "key" => "CEP",
                            "value" => getenv("ENTERPRISE_ZIP_CODE") ?: getenv("COURIER_ORIGIN_ZIP_CODE")
                        ]
                    ]
                )
            ]
        );

        $response->getBody()->write($page->getTpl());
        return $response;
    }


    /**
     * Retorna o template de recuperação de senha
     *
     * @param Request  $request  Requisição
     * @param Response $response Resposta
     * @param array    $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function forgot(Request $request, Response $response, array $args): Response
    {
        $page = new PageAdmin(
            [
                "header" => false,
                "footer" => false
            ]
        );
        $page->setTpl("forgot");

        $response->getBody()->write($page->getTpl());
        return $response;
    }


    /**
     * Retorna o template de restauração de senha
     *
     * @param Request  $request  Requisição
     * @param Response $response Resposta
     * @param array    $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function resetPassword(Request $request, Response $response, array $args): Response
    {
        $params = $request->getQueryParams();
        $userPasswordRecovery = UserPasswordRecovery::loadFromValidationKeys(self::string($params["code"]), self::string($params["sk"]));

        if ($userPasswordRecovery?->validate()) {
            $page = new PageAdmin(
                [
                    "header" => false,
                    "footer" => false
                ]
            );
            $page->setTpl("reset-password", ["userPasswordRecovery" => $userPasswordRecovery->array()]);

            $response->getBody()->write($page->getTpl());
            return $response;
        } else {
            return $response->withHeader("Location", "/admin")->withStatus(302);
        }
    }


    /**
     * Retorna o template de alteração de senha
     *
     * @param Request  $request  Requisição
     * @param Response $response Resposta
     * @param array    $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function changePassword(Request $request, Response $response, array $args): Response
    {
        $page = new PageAdmin(
            [
                "header" => false,
                "footer" => false
            ]
        );
        $page->setTpl("change-password");

        $response->getBody()->write($page->getTpl());
        return $response;
    }


    /**
     * Retorna o template de erro
     *
     * @param Request  $request  Requisição
     * @param Response $response Resposta
     * @param array    $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function error(Request $request, Response $response, array $args): Response
    {
        $page = new Page(
            [
                "header" => false,
                "footer" => false
            ]
        );
        $page->setTpl("error", ["exception" => (new HttpException("Página não encontrada", 404))->array(getenv("PHP_DEBUG") !== "true")]);

        $response->getBody()->write($page->getTpl());
        return $response;
    }
}
