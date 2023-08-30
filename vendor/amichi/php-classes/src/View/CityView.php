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
use Amichi\Model\City;
use Amichi\PageAdmin;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Classe que controla as views da entidade CIDADE
 *
 * @category View
 * @package  Amichi/View
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class CityView extends Controller
{
    /**
     * Retorna o template da lista de todas as cidades do banco de dados
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
            "cities",
            [
                "cities" => array_map(
                    fn (City $city): array => $city->array(),
                    City::listAll(
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
}
