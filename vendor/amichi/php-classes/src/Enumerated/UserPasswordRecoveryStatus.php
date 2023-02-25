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
 * Enumerado que define o STATUS DA RECUPERAÇÃO DE SENHA DE USUÁRIO
 *
 * @category Enumerated
 * @package  Amichi/Enumerated
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
enum UserPasswordRecoveryStatus: string
{
    case REQUESTED = "Solicitado";
    case REALIZED = "Realizado";
    case EXPIRED = "Expirado";
}
