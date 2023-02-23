<?php

/**
 * PHP version 8.1.2
 *
 * @category Trait
 * @package  Amichi/Trait
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */

namespace Amichi\Trait;

/**
 * Classe que define funções de codificação/decodificação de dados
 *
 * @category Trait
 * @package  Amichi/Trait
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
trait Encoder
{
    /**
     * Criptografa/descriptografa um texto
     *
     * @param string $text   Texto a ser criptografado
     * @param string $action Ação de criptografia/descriptografia
     *
     * @static
     *
     * @return string
     */
    private static function _encrypt(string $text, string $action = "encrypt"): string
    {
        $cipher = "AES-128-CTR";
        $key = hash("sha256", getenv("SECRET_KEY") ?: "ChaveSecreta");
        $iv = substr(hash("sha256", getenv("SECRET_IV") ?: "ContraChaveSecreta"), 0, 16);

        if ($action === "encrypt") {
            $output = openssl_encrypt($text, $cipher, $key, 0, $iv);
            $output = base64_encode($output);
        } else {
            $output = openssl_decrypt(base64_decode($text), $cipher, $key, 0, $iv);
        }

        return (string) $output;
    }


    /**
     * Criptografa um texto
     *
     * @param string $text Texto a ser criptografado
     *
     * @static
     *
     * @return string
     */
    public static function crypt(string $text): string
    {
        return self::_encrypt($text, "encrypt");
    }


    /**
     * Descriptografa um texto
     *
     * @param string $text Texto a ser descriptografado
     *
     * @static
     *
     * @return string
     */
    public static function decrypt(string $text): string
    {
        return self::_encrypt($text, "decrypt");
    }
}
