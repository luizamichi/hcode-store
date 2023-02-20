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
 * Classe que renderiza arquivos HTML do painel de administração
 *
 * @category GlobalClass
 * @package  Amichi
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class PageAdmin extends Page
{
    /**
     * Construtor
     *
     * @param array  $options    Opções de definição do template
     * @param string $directory  Diretório de templates HTML
     * @param bool   $returnHTML Retorna o HTML?
     *
     * @return void
     */
    public function __construct(array $options = [], string $directory = "/views/admin/", bool $returnHTML = true)
    {
        parent::__construct($options, $directory, $returnHTML);
    }
}
