<?php

/**
 * PHP version 8.1.2
 *
 * @category Enumerated
 * @package  Amichi/Enumerated
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */

namespace Amichi\Enumerated;

/**
 * Enumerado que define a INSTITUIÇÃO BANCÁRIA
 *
 * @category Enumerated
 * @package  Amichi/Enumerated
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
enum Bank: int
{
    case BB = 1; // Banco do Brasil
    case BRB = 2; // Banco de Brasília
    case BRADESCO = 3; // Bradesco
    case ITAU = 4; // Itaú
    case SANTANDER = 5; // Santander
    case UNICRED = 6; // Unicred
}
