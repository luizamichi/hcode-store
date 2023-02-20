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
 * Classe que renderiza arquivos HTML dos e-mails
 *
 * @category GlobalClass
 * @package  Amichi
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class PageMail extends Page
{
    /**
     * Construtor
     *
     * @param array  $options   Opções de definição do template
     * @param string $directory Diretório de templates HTML
     *
     * @return void
     */
    public function __construct(array $options = [], string $directory = "/views/email/")
    {
        $options = array_merge(
            [
                "header" => false,
                "footer" => false
            ],
            $options
        );
        parent::__construct($options, $directory);
    }
}
