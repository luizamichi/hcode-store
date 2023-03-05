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
 * Enumerado que define o TIPO DE TÓPICO
 *
 * @category Enumerated
 * @package  Amichi/Enumerated
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
enum TopicType: string
{
    case FAQ = "Perguntas frequentes";
    case CCE = "Código de conduta ética";
    case COOKIE = "Política de cookies";
    case PRIVACY = "Política de privacidade";
}
