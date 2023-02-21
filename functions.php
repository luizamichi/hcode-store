<?php

/**
 * Funções para serem utilizadas no front-end
 * PHP version 8.1.2
 *
 * @category Functions
 * @package  Root
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */

/**
 * Retorna o valor da variável
 *
 * @param mixed $var Variável
 *
 * @return mixed
 */
function getValue(mixed &$var): mixed
{
    return isset($var) ? $var : null;
}


/**
 * Retorna a data/hora formatada no padrão nacional
 *
 * @param ?string $date Data/hora
 *
 * @return string
 */
function formatDate(?string $date): string
{
    return $date ? date_format(new DateTime($date), "d/m/Y - H:i:s") : "";
}


/**
 * Retorna o número formatado no padrão monetário nacional
 *
 * @param ?float $number Número
 *
 * @return string
 */
function formatPrice(?float $number): string
{
    return number_format($number, 2, ",", ".");
}


/**
 * Retorna o CEP formatado no padrão dos Correios
 *
 * @param ?string $zipCode Código de endereçamento postal
 *
 * @return string
 */
function formatZipCode(?string $zipCode): string
{
    return (string) preg_replace("/(\d{2})(\d{3})(\d{2})/", "$1.$2-$3", $zipCode);
}


/**
 * Verifica se o usuário da sessão está logado
 *
 * @return bool
 */
function checkLogin(): bool
{
    return (bool) random_int(0, 1);
}


/**
 * Retorna o ID do carrinho da sessão
 *
 * @return int
 */
function getCartId(): int
{
    return random_int(1, 1000);
}


/**
 * Retorna o ID do usuário da sessão
 *
 * @return int
 */
function getUserId(): int
{
    return random_int(0, 1) ? random_int(1, 1000) : 0;
}


/**
 * Retorna o nome do usuário da sessão
 *
 * @return string
 */
function getUserName(): string
{
    return substr(md5(mt_rand()), 10);
}


/**
 * Retorna a quantidade de produtos do carrinho da sessão
 *
 * @return int
 */
function getCountCartProducts(): int
{
    return random_int(0, 10);
}


/**
 * Retorna a quantidade de produtos da lista de favoritos
 *
 * @return int
 */
function getCountWishlist(): int
{
    return random_int(0, 100);
}


/**
 * Retorna o ícone do status do pedido
 *
 * @param string $enum Enumerado do status do pedido
 *
 * @return string
 */
function getStatusIcon(string $enum): string
{
    return match ($enum) {
        "OPEN_ORDER" => "<i class=\"fas fa-play-circle\"></i>",
        "AWAITING_PAYMENT" => "<i class=\"fas fa-clock\"></i>",
        "PAYMENT_CONFIRMED" => "<i class=\"fas fa-thumbs-up\"></i>",
        "ORDER_DISPATCHED" => "<i class=\"fab fa-telegram-plane\"></i>",
        "ORDER_DELIVERED" => "<i class=\"fas fa-check-circle\"></i>",
        "CANCELED_ORDER" => "<i class=\"fas fa-times-circle\"></i>",
        default => "<i class=\"fas fa-question-circle\"></i>"
    };
}
