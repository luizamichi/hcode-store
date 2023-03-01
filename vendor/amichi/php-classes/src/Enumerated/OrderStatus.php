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
 * Enumerado que define o STATUS DO PEDIDO
 *
 * @category Enumerated
 * @package  Amichi/Enumerated
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
enum OrderStatus: int
{
    case OPEN_ORDER = 1;
    case AWAITING_PAYMENT = 2;
    case PAYMENT_CONFIRMED = 3;
    case ORDER_DISPATCHED = 4;
    case ORDER_DELIVERED = 5;
    case CANCELED_ORDER = 6;
}
