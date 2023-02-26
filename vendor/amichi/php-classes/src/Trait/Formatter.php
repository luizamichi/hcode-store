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
 * Classe que define funções formatadoras de dados
 *
 * @category Trait
 * @package  Amichi/Trait
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
trait Formatter
{
    /**
     * Converte um valor decimal em ponto flutuante
     *
     * @param string $decimal Valor decimal
     *
     * @return float
     */
    private function _decimalToFloat(string $decimal): float
    {
        $decimal = str_replace(".", "", $decimal);
        return (float) str_replace(",", ".", $decimal);
    }


    /**
     * Aplica uma máscara no texto
     *
     * @param string $value Valor
     * @param string $mask  Máscara
     *
     * @return string
     */
    private function _mask(string $value, string $mask): string
    {
        $maskared = "";
        $k = 0;

        for ($i = 0; $i <= strlen($mask) - 1; $i++) {
            if ($mask[$i] == "#") {
                if (isset($value[$k])) {
                    $maskared .= $value[$k++];
                }
            } else {
                if (isset($mask[$i])) {
                    $maskared .= $mask[$i];
                }
            }
        }
        return $maskared;
    }


    /**
     * Formata um CEP
     *
     * @param string $zipCode CEP
     *
     * @return string
     */
    private function _zipCode(string $zipCode): string
    {
        return $this->_mask($zipCode, "##.###-###");
    }


    /**
     * Formata um CPF
     *
     * @param string $cpf CPF
     *
     * @return string
     */
    private function _cpf(string $cpf): string
    {
        return $this->_mask($cpf, "###.###.###-##");
    }


    /**
     * Formata um CNPJ
     *
     * @param string $cnpj CNPJ
     *
     * @return string
     */
    private function _cnpj(string $cnpj): string
    {
        return $this->_mask($cnpj, "##.###.###/####-##");
    }


    /**
     * Formata um telefone celular
     *
     * @param string $phone Número do celular
     *
     * @return string
     */
    private function _phone(string $phone): string
    {
        return $this->_mask($phone, "(##) #####-####");
    }
}
