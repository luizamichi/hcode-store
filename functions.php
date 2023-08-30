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

use Amichi\Enumerated\OrderStatus as EnumeratedOrderStatus;
use Amichi\Enumerated\TopicType as EnumeratedTopicType;
use Amichi\Model\Cart;
use Amichi\Model\User;
use Amichi\Model\Wishlist;

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
 * Retorna o número de celular formatado no padrão brasileiro
 *
 * @param ?string $phone Número de telefone celular
 *
 * @return string
 */
function formatPhone(?string $phone): string
{
    return (string) preg_replace("/(\d{2})(\d{5})(\d{4})/", "($1) $2-$3", (string) $phone);
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
    return number_format((float) $number, 2, ",", ".");
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
    return (string) preg_replace("/(\d{2})(\d{3})(\d{2})/", "$1.$2-$3", (string) $zipCode);
}


/**
 * Verifica se o usuário da sessão está logado
 *
 * @return bool
 */
function checkLogin(): bool
{
    return (bool) User::loadFromSession();
}


/**
 * Verifica se o usuário da sessão é administrador
 *
 * @return bool
 */
function checkAdmin(): bool
{
    return User::loadFromSession()?->isAdmin ?? false;
}


/**
 * Retorna o ID do carrinho da sessão
 *
 * @return int
 */
function getCartId(): int
{
    return Cart::loadFromSession()->id;
}


/**
 * Retorna o ID do usuário da sessão
 *
 * @return int
 */
function getUserId(): int
{
    return User::loadFromSession()?->id ?? 0;
}


/**
 * Retorna o nome do usuário da sessão
 *
 * @return string
 */
function getUserName(): string
{
    return User::loadFromSession()?->name ?? "";
}


/**
 * Retorna a quantidade de produtos do carrinho da sessão
 *
 * @return int
 */
function getCountCartProducts(): int
{
    return count(Cart::loadFromSession()->getProducts());
}


/**
 * Retorna a quantidade de produtos da lista de favoritos
 *
 * @return int
 */
function getCountWishlist(): int
{
    return count(Wishlist::listFromUserId(getUserId()));
}


/**
 * Retorna o ícone do status do pedido
 *
 * @param ?string $enum Enumerado do status do pedido
 *
 * @return string
 */
function getStatusIcon(?string $enum): string
{
    return match ($enum) {
        EnumeratedOrderStatus::OPEN_ORDER->name => "<i class=\"fas fa-play-circle\"></i>",
        EnumeratedOrderStatus::AWAITING_PAYMENT->name => "<i class=\"fas fa-clock\"></i>",
        EnumeratedOrderStatus::PAYMENT_CONFIRMED->name => "<i class=\"fas fa-thumbs-up\"></i>",
        EnumeratedOrderStatus::ORDER_DISPATCHED->name => "<i class=\"fab fa-telegram-plane\"></i>",
        EnumeratedOrderStatus::ORDER_DELIVERED->name => "<i class=\"fas fa-check-circle\"></i>",
        EnumeratedOrderStatus::CANCELED_ORDER->name => "<i class=\"fas fa-times-circle\"></i>",
        default => "<i class=\"fas fa-question-circle\"></i>"
    };
}


/**
 * Retorna o ícone do tipo de tópico
 *
 * @param ?string $enum Enumerado do tipo de tópico
 *
 * @return string
 */
function getTopicIcon(?string $enum): string
{
    return match ($enum) {
        EnumeratedTopicType::FAQ->name => "<i class=\"fas fa-question-circle\"></i>",
        EnumeratedTopicType::CCE->name => "<i class=\"fas fa-hand-holding\"></i>",
        EnumeratedTopicType::COOKIE->name => "<i class=\"fas fa-cookie-bite\"></i>",
        EnumeratedTopicType::PRIVACY->name => "<i class=\"fas fa-shield-alt\"></i>",
        default => "<i class=\"fas fa-stream\"></i>"
    };
}
