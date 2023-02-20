<?php

/**
 * PHP version 8.1.2
 *
 * @category GlobalClass
 * @package  Amichi
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */

namespace Amichi;

/**
 * Classe que define uma exceção personalizada
 *
 * @category GlobalClass
 * @package  Amichi
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class HttpException extends \Exception
{
    /**
     * Propriedades
     *
     * @var bool $_environmentIsApi Ambiente de execução é API?
     * @var bool $_success          Sucesso na operação?
     */
    private bool $_environmentIsApi;
    private bool $_success;


    /**
     * Construtor
     *
     * @param string $message Mensagem da exceção
     * @param int    $code    Código de status de respostas HTTP
     * @param bool   $success Sucesso na operação?
     *
     * @return void
     */
    public function __construct(string $message, int $code = 500, bool $success = false)
    {
        parent::__construct($message, $code);
        http_response_code($code);

        $this->_success = $success;
        $this->_environmentIsApi = getenv("APPLICATION_ENVIRONMENT") !== "view";

        if ($this->_environmentIsApi) {
            header("Content-type: application/json");
        }
    }


    /**
     * Retorna o JSON com o erro
     *
     * @return string
     */
    public function __toString(): string
    {
        return json_encode($this->array());
    }


    /**
     * Retorna a classe em formato de vetor
     *
     * @param boolean $hideSensitiveInformation Oculta informações sensíveis
     *
     * @return array
     */
    public function array(bool $hideSensitiveInformation = true): array
    {
        $return = [
            "message" => $this->getMessage(),
            "code" => $this->getCode(),
            "success" => $this->_success,
            "file" => null,
            "line" => null,
            "trace" => null
        ];

        if (!$hideSensitiveInformation) {
            $return["file"] = $this->getFile();
            $return["line"] = $this->getLine();
            $return["trace"] = $this->getTraceAsString();
        }

        return $return;
    }


    /**
     * Imprime o erro em formato JSON
     *
     * @return void
     */
    public function json(): void
    {
        if (!$this->_environmentIsApi) {
            $page = new Page(
                [
                    "header" => false,
                    "footer" => false
                ]
            );
            $page->setTpl("error", ["exception" => $this->array(false)]);
            exit($page->getTpl());
        }

        exit($this);
    }
}
