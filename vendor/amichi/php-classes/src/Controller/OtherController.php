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
use Amichi\DB\SQL;
use Amichi\HttpException;
use Amichi\Model\City;
use Amichi\Model\Country;
use Amichi\Model\Mail;
use Amichi\Model\State;
use Amichi\Model\User;
use Amichi\Model\UserPasswordRecovery;
use Amichi\PageMail;
use Amichi\Trait\Encoder;
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
    use Encoder;


    /**
     * Envia um e-mail para restauração de senha
     *
     * @param Request       $request  Requisição
     * @param Response      $response Resposta
     * @param array<string> $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function forgot(Request $request, Response $response, array $args): Response
    {
        $email = self::string($request->getParsedBody()["email"], true, "email");
        $user = User::loadFromEmail($email);

        if (!$user) {
            throw (new HttpException("Não foi possível solicitar a redefinição de senha, pois, o endereço de e-mail é inexistente.", 400))->json();
        }

        $userPasswordRecovery = UserPasswordRecovery::loadFromData(["idUser" => $user->id]);
        $userPasswordRecovery->create();

        $link = $_SERVER["HTTP_HOST"] . "/admin/resetpassword?";

        $page = new PageMail();
        $page->setTpl(
            "forgot",
            [
                "name" => $user->name,
                "link" => $link . http_build_query(
                    [
                        "code" => self::crypt($userPasswordRecovery->id),
                        "sk" => self::crypt($userPasswordRecovery->securityKey)
                    ]
                )
            ]
        );

        $mail = Mail::loadFromData(
            [
                "email" => $user->email,
                "name" => $user->name,
                "subject" => "[Hcode Store] Redefinição de senha",
                "content" => $page->getTpl()
            ]
        );

        $response->getBody()->write(json_encode($mail->send()));

        return $response->withStatus(201);
    }


    /**
     * Restaura a senha do usuário
     *
     * @param Request       $request  Requisição
     * @param Response      $response Resposta
     * @param array<string> $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function resetPassword(Request $request, Response $response, array $args): Response
    {
        $errors = [];
        $data = (array) $request->getParsedBody();

        $userPasswordRecovery = UserPasswordRecovery::loadFromValidationKeys(self::string($data["code"]), self::string($data["sk"]));
        $userPasswordRecovery?->validate($errors);

        if (!$userPasswordRecovery || $errors) {
            !empty($errors) ?: array_push($errors, "os códigos de verificação são inválidos");
            $message = count($errors) === 1 ? "O seguinte erro foi encontrado" : "Os seguintes erros foram encontrados";
            throw (new HttpException("Não foi possível restaurar a senha do usuário. $message: " . implode(", ", $errors) . ".", 400))->json();
        }

        UserPasswordRecovery::loadFromData($data)->validate($errors);

        if ($errors) {
            $message = count($errors) === 1 ? "O seguinte erro foi encontrado" : "Os seguintes erros foram encontrados";
            throw (new HttpException("Não foi possível restaurar a senha do usuário {$userPasswordRecovery->idUser}. $message: " . implode(", ", $errors) . ".", 400))->json();
        }

        $userPasswordRecovery->password = self::string($data["password"]);

        $response->getBody()->write(json_encode($userPasswordRecovery->update()));

        return $response;
    }


    /**
     * Altera a senha do usuário da sessão
     *
     * @param Request       $request  Requisição
     * @param Response      $response Resposta
     * @param array<string> $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function changePassword(Request $request, Response $response, array $args): Response
    {
        $errors = [];
        $data = (array) $request->getParsedBody();
        $user = User::loadFromSession();

        if (!$user) {
            throw (new HttpException("Não foi possível alterar a senha do usuário, pois, não está autenticado.", 400))->json();
        }

        $oldPassword = $data["password"] ?? "";
        $newPassword = $data["newPassword"] ?? "";

        if (!password_verify($oldPassword, $user->password)) {
            throw (new HttpException("Não foi possível alterar a senha do usuário, pois, a senha atual está incorreta.", 400))->json();
        }

        if (password_verify($newPassword, $user->password)) {
            throw (new HttpException("Não foi possível alterar a senha do usuário, pois, a nova senha é igual a senha anterior.", 400))->json();
        }

        $user->password = $newPassword;
        $user->validate($errors);

        if ($errors) {
            $message = count($errors) === 1 ? "O seguinte erro foi encontrado" : "Os seguintes erros foram encontrados";
            throw (new HttpException("Não foi possível alterar a senha do usuário {$user->id}. $message: " . implode(", ", $errors) . ".", 400))->json();
        }

        $response->getBody()->write(json_encode($user->updatePassword($newPassword)->saveInSession()));

        return $response;
    }


    /**
     * Executa uma consulta no banco de dados
     *
     * @param Request       $request  Requisição
     * @param Response      $response Resposta
     * @param array<string> $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function sqlQuery(Request $request, Response $response, array $args): Response
    {
        $response->getBody()->write(
            json_encode(
                (SQL::get())->send(self::string($request->getParsedBody()["query"], true, "query"))->fetchAll()
            )
        );

        return $response;
    }


    /**
     * Executa um código PHP
     *
     * @param Request       $request  Requisição
     * @param Response      $response Resposta
     * @param array<string> $args     Argumentos da URL
     *
     * @throws \Throwable Se não conseguir executar o script PHP
     *
     * @static
     *
     * @return Response
     */
    public static function phpEval(Request $request, Response $response, array $args): Response
    {
        $code = self::string($request->getParsedBody()["code"], true, "code");
        $message = "Código executado com sucesso.";
        $success = true;

        try {
            $eval = eval($code);
        } catch (\Throwable $th) {
            $message = $th->getMessage();
            $success = false;
        } finally {
            $response->getBody()->write(
                json_encode(
                    [
                        "code" => $code,
                        "eval" => $eval ?? "",
                        "message" => $message,
                        "success" => $success
                    ]
                )
            );
        }

        return $response;
    }


    /**
     * Retorna os dados armazenados na sessão
     *
     * @param Request       $request  Requisição
     * @param Response      $response Resposta
     * @param array<string> $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function phpSession(Request $request, Response $response, array $args): Response
    {
        $response->getBody()->write(json_encode($_SESSION));

        return $response;
    }


    /**
     * Retorna o status do webservice
     *
     * @param Request       $request  Requisição
     * @param Response      $response Resposta
     * @param array<string> $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function status(Request $request, Response $response, array $args): Response
    {
        $response->getBody()->write(
            json_encode(
                [
                    "phpVersion" => phpversion(),
                    "mysqlVersion" => (SQL::get())->getAttribute(SQL::ATTR_SERVER_VERSION),
                    "phpDateTime" => date("Y-m-d H:i:s"),
                    "mysqlDateTime" => (SQL::get())->send("SELECT NOW() dt")->fetch()->dt,
                    "requestTime" => $_SERVER["REQUEST_TIME"],
                    "server" => $_SERVER["SERVER_NAME"],
                    "remotePort" => $_SERVER["REMOTE_PORT"]
                ]
            )
        );

        return $response;
    }


    /**
     * Retorna os dados de um endereço a partir do CEP informado na URL
     *
     * @param Request       $request  Requisição
     * @param Response      $response Resposta
     * @param array<string> $args     Argumentos da URL
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


    /**
     * Retorna o JSON de erro de acesso 404
     *
     * @param Request       $request  Requisição
     * @param Response      $response Resposta
     * @param array<string> $args     Argumentos da URL
     *
     * @static
     *
     * @return Response
     */
    public static function error(Request $request, Response $response, array $args): Response
    {
        throw (new HttpException("Serviço não encontrado.", 404))->json();
    }
}
