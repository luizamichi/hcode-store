<?php

/**
 * PHP version 8.1.2
 *
 * @category Controller
 * @package  Amichi/Controller
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */

namespace Amichi\Controller;

use Amichi\Controller;
use Amichi\HttpException;
use Amichi\Model\City;
use Amichi\Model\Country;
use Amichi\Model\State;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Classe que controla as demais rotas
 *
 * @category Controller
 * @package  Amichi/Controller
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class OtherController extends Controller
{
    /**
     * Retorna os dados de um endereço a partir do CEP informado na URL
     *
     * @param Request  $request  Requisição
     * @param Response $response Resposta
     * @param array    $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function zipCode(Request $request, Response $response, array $args): Response
    {
        $zipCode = str_replace("-", "", self::string($args["zipCode"], true, "zipCode"));

        $ch = curl_init();
        curl_setopt_array(
            $ch,
            [
                \CURLOPT_URL => "https://viacep.com.br/ws/$zipCode/json/",
                \CURLOPT_RETURNTRANSFER => true,
                \CURLOPT_SSL_VERIFYPEER => false
            ]
        );

        $data = curl_exec($ch);
        $data = json_decode($data, true);
        $status = curl_getinfo($ch, \CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status !== 200) {
            throw (new HttpException("Não foi possível encontrar o CEP \"$zipCode\".", 400))->json();
        }

        $state = State::loadFromUf($data["uf"] ?? "", (int) Country::loadFromCOI("BRA")?->id);
        $city = City::loadFromName($data["localidade"] ?? "", (int) $state?->id);

        if (!$city) {
            $city = City::loadFromData(
                [
                    "idState" => $state?->id,
                    "ibgeCode" => $data["ibge"] ?? null,
                    "name" => $data["localidade"] ?? "",
                    "ddd" => $data["ddd"] ?? ""
                ]
            );

            $state && $city->save();
        }

        $json = [
            "zipCode" => $data["cep"] ?? null,
            "address" => $data["logradouro"] ?? null,
            "district" => $data["bairro"] ?? null,
            "complement" => $data["complemento"] ?? null,
            "gia" => $data["gia"] ?? null,
            "siafi" => $data["siafi"] ?? null,
            "city" => $city
        ];

        $response->getBody()->write(json_encode($json));

        return $response->withStatus($status);
    }
}
