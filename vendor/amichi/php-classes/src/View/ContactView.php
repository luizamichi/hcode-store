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
use Amichi\Model\Contact;
use Amichi\Page;
use Amichi\PageAdmin;
use Amichi\Trait\Formatter;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Classe que controla as views da entidade CONTATO
 *
 * @category View
 * @package  Amichi/View
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class ContactView extends Controller
{
    use Formatter;


    /**
     * Retorna o template da lista de todos os contatos do banco de dados
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
            "contacts",
            [
                "contacts" => array_map(
                    fn (Contact $contact): array => $contact->array(),
                    Contact::listAll(
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
     * Retorna o template da página de contato
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
        $phone = (new self)->_phone((string) getenv("ENTERPRISE_PHONE"));

        $page = new Page();
        $page->setTpl(
            "contact",
            [
                "address" => getenv("ENTERPRISE_ADDRESS"),
                "city" => getenv("ENTERPRISE_CITY"),
                "uf" => getenv("ENTERPRISE_FU"),
                "zipCode" => getenv("ENTERPRISE_ZIP_CODE"),
                "mail" => getenv("ENTERPRISE_MAIL"),
                "phone" => $phone ? "+55 $phone" : "",
                "url" => urlencode(getenv("ENTERPRISE_ADDRESS") . " - " . getenv("ENTERPRISE_CITY") . ", " . getenv("ENTERPRISE_FU") . ", " . getenv("ENTERPRISE_ZIP_CODE"))
            ]
        );

        $response->getBody()->write($page->getTpl());
        return $response;
    }
}
