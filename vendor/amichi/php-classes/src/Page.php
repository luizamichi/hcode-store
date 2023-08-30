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

use Rain\Tpl;

/**
 * Classe que renderiza arquivos HTML
 *
 * @category GlobalClass
 * @package  Amichi
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class Page
{
    /**
     * Propriedades
     *
     * @var Tpl   $_tpl        Template
     * @var bool  $_returnHTML Retorna o HTML ou imprime?
     */
    private Tpl $_tpl;
    private bool $_returnHTML;


    /**
     * Propriedade
     *
     * @var array<string> $_templates Vetor de templates HTML
     */
    private array $_templates;


    /**
     * Propriedade
     *
     * @var array<string,mixed> $_options Opcões de template
     */
    private array $_options = [
        "data" => [], // Dados que serão populados dinamicamente
        "header" => true, // Monta o cabeçalho?
        "footer" => true // Monta o rodapé?
    ];


    /**
     * Construtor
     *
     * @param array<string,mixed> $options    Opções de definição do template
     * @param string              $directory  Diretório de templates HTML
     * @param bool                $returnHTML Retorna o HTML?
     *
     * @return void
     */
    public function __construct(array $options = [], string $directory = "/views/", bool $returnHTML = true)
    {
        $root = $_SERVER["DOCUMENT_ROOT"] ?? "";
        $this->_returnHTML = $returnHTML;
        $this->_templates = [];
        $this->_options = array_merge($this->_options, $options);

        $configurations = [
            "tpl_dir" => $root . $directory,
            "cache_dir" => $root . "/views-cache/",
            "debug" => getenv("PHP_DEBUG") === "true"
        ];

        Tpl::configure($configurations);

        $this->_tpl = new Tpl();
        $this->_setData($this->_options["data"]);
        $this->_options["header"] === true && $this->_tpl->draw("header", $this->_returnHTML);
    }


    /**
     * Destrutor
     *
     * @return void
     */
    public function __destruct()
    {
        $this->_options["footer"] === true && $this->_tpl->draw("footer", $this->_returnHTML);
    }


    /**
     * Define o template HTML
     *
     * @param string              $name Nome do arquivo
     * @param array<string,mixed> $data Vetor de dados
     *
     * @return string
     */
    public function setTpl(string $name, array $data = []): string
    {
        $this->_setData($data);
        $this->_templates[] = $name;
        return $this->_tpl->draw($name, $this->_returnHTML) ?: "";
    }


    /**
     * Retorna o template HTML
     *
     * @return string
     */
    public function getTpl(): string
    {
        $html = "";

        if ($this->_options["header"]) {
            $html .= $this->_tpl->draw("header", $this->_returnHTML) ?: "";
        }

        foreach ($this->_templates as $name) {
            $html .= $this->_tpl->draw($name, $this->_returnHTML) ?: "";
        }

        if ($this->_options["footer"]) {
            $html .= $this->_tpl->draw("footer", $this->_returnHTML) ?: "";
        }

        return $html;
    }


    /**
     * Define os dados dinâmicos
     *
     * @param array<string,mixed> $data Vetor de dados
     *
     * @return void
     */
    private function _setData(array $data = []): void
    {
        foreach ($data as $key => $value) {
            $this->_tpl->assign($key, $value);
        }
    }
}
