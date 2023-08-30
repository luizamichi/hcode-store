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
 * Classe que define funções validadoras de dados
 *
 * @category Trait
 * @package  Amichi/Trait
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
trait Validator
{
    /**
     * Verifica se o valor do CPF é válido
     *
     * @param string $cpf Cadastro de pessoa física
     *
     * @return bool
     */
    private function _validateCpf(string $cpf): bool
    {
        if (!in_array(strlen($cpf), [11, 14]) || (!preg_match("/[0-9]{11}/", $cpf) && !preg_match("/\d{3}\.\d{3}\.\d{3}\-\d{2}/", $cpf))) { // Verifica se o formato está correto
            return false;
        }

        $cpf = (string) preg_replace("/[^0-9]/is", "", $cpf); // Remove os traços e pontos
        $cpf = str_split($cpf);
        $cpf = array_map("intval", $cpf);
        if (count($cpf) !== 11) { // Verifica se o tamanho está correto
            return false;
        } elseif (in_array($cpf, array_map(fn (int $number): string => str_repeat((string) $number, 11), range(0, 9)))) { // Verifica se foi informada uma sequência de dígitos repetidos
            return false;
        }

        for ($i = 9; $i < 11; ++$i) { // Valida o dígito verificador
            for ($j = 0, $k = 0; $k < $i; ++$k) {
                $j += $cpf[$k] * (($i + 1) - $k);
            }

            $j = ((10 * $j) % 11) % 10;

            if ((int) $cpf[$k] !== $j) {
                return false;
            }
        }
        return true;
    }


    /**
     * Verifica se o valor do endereço IP é válido
     *
     * @param string $ip Endereço IP
     *
     * @return bool
     */
    private function _validateIp(string $ip): bool
    {
        return (bool) filter_var($ip, FILTER_VALIDATE_IP);
    }


    /**
     * Verifica se a senha é forte
     *
     * @param string $password Senha
     *
     * @return bool
     */
    private function _validatePasswordStrength(string $password): bool
    {
        $uppercase = preg_match("@[A-Z]@", $password);
        $lowercase = preg_match("@[a-z]@", $password);
        $number = preg_match("@[0-9]@", $password);
        $specialChars = preg_match("@[^\w]@", $password);
        return $uppercase && $lowercase && $number && $specialChars;
    }


    /**
     * Verifica se o valor do telefone celular é válido
     *
     * @param int|string $phone Número do celular
     *
     * @return bool
     */
    private function _validatePhone(int|string $phone): bool
    {
        $phone = (string) preg_replace("/[^0-9]/", "", $phone);
        return strlen($phone) === 11;
    }


    /**
     * Verifica se o valor do CEP é válido
     *
     * @param int|string $zipCode Número do CEP
     *
     * @return bool
     */
    private function _validateZipCode(int|string $zipCode): bool
    {
        $zipCode = (string) preg_replace("/[^0-9]/", "", $zipCode);
        return strlen($zipCode) === 8;
    }
}
