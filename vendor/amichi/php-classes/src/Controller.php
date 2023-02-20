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
 * Classe que define funções úteis para manipulação da entrada de dados
 *
 * @category GlobalClass
 * @package  Amichi
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 * @abstract
 */
abstract class Controller
{
    /**
     * Verifica o valor de uma variável de entrada do tipo inteira
     *
     * @param mixed  $value    Referência da variável de entrada
     * @param bool   $required Parâmetro é obrigatório?
     * @param string $name     Nome da variável de entrada
     *
     * @static
     *
     * @return int
     */
    public static function int(mixed &$value, bool $required = false, string $name = ""): int
    {
        if (isset($value) && filter_var($value, FILTER_VALIDATE_INT)) {
            return (int) $value;
        } elseif (!$required) {
            return (int) ($value ?? 0);
        }

        throw (new HttpException("É necessário informar um número inteiro no parâmetro " . ($name ? "\"$name\"" : "requerido") . ".", 400))->json();
    }


    /**
     * Verifica o valor de uma variável de entrada do tipo ponto flutuante
     *
     * @param mixed  $value    Referência da variável de entrada
     * @param bool   $required Parâmetro é obrigatório?
     * @param string $name     Nome da variável de entrada
     *
     * @static
     *
     * @return float
     */
    public static function float(mixed &$value, bool $required = false, string $name = ""): float
    {
        if (isset($value) && filter_var($value, FILTER_VALIDATE_FLOAT)) {
            return (float) $value;
        } elseif (!$required) {
            return (float) ($value ?? 0.0);
        }

        throw (new HttpException("É necessário informar um ponto flutuante no parâmetro " . ($name ? "\"$name\"" : "requerido") . ".", 400))->json();
    }


    /**
     * Verifica o valor de uma variável de entrada do tipo booleano
     *
     * @param mixed  $value    Referência da variável de entrada
     * @param bool   $required Parâmetro é obrigatório?
     * @param string $name     Nome da variável de entrada
     *
     * @static
     *
     * @return bool
     */
    public static function bool(mixed &$value, bool $required = false, string $name = ""): bool
    {
        if (isset($value) && filter_var($value, FILTER_VALIDATE_BOOL)) {
            return (bool) $value;
        } elseif (!$required) {
            return (bool) ($value ?? false);
        }

        throw (new HttpException("É necessário informar um valor booleano no parâmetro " . ($name ? "\"$name\"" : "requerido") . ".", 400))->json();
    }


    /**
     * Verifica o valor de uma variável de entrada do tipo string
     *
     * @param mixed  $value    Referência da variável de entrada
     * @param bool   $required Parâmetro é obrigatório?
     * @param string $name     Nome da variável de entrada
     *
     * @static
     *
     * @return string
     */
    public static function string(mixed &$value, bool $required = false, string $name = ""): string
    {
        if (isset($value) && is_string($value) && strlen($value) !== 0) {
            return (string) $value;
        } elseif (!$required) {
            return (string) ($value ?? "");
        }

        throw (new HttpException("É necessário informar um texto no parâmetro " . ($name ? "\"$name\"" : "requerido") . ".", 400))->json();
    }


    /**
     * Verifica o valor de uma variável de entrada do tipo vetor
     *
     * @param mixed  $value    Referência da variável de entrada
     * @param bool   $required Parâmetro é obrigatório?
     * @param string $name     Nome da variável de entrada
     *
     * @static
     *
     * @return array
     */
    public static function array(mixed &$value, bool $required = false, string $name = ""): array
    {
        if (isset($value) && filter_var($value, FILTER_REQUIRE_ARRAY)) {
            return (array) $value;
        } elseif (!$required) {
            return (array) ($value ?? []);
        }

        throw (new HttpException("É necessário informar um vetor no parâmetro " . ($name ? "\"$name\"" : "requerido") . ".", 400))->json();
    }
}
