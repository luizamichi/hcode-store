<?php

/**
 * PHP version 8.1.2
 *
 * @category Model
 * @package  Amichi/Model
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */

namespace Amichi\Model;

use Amichi\DB\SQL;
use Amichi\Model;
use Amichi\Trait\Validator;
use JsonSerializable;

/**
 * Classe que modela a entidade PESSOA
 *
 * @category Model
 * @package  Amichi/Model
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 * @abstract
 */
abstract class Person extends Model implements JsonSerializable
{
    use Validator;


    /**
     * Propriedade
     *
     * @var array $columns Colunas de mapeamento objeto relacional
     */
    protected static array $columns = [
        "idPerson" => "id_person", // ID da pessoa
        "name" => "des_person", // Nome da pessoa
        "email" => "des_email", // E-mail da pessoa
        "cpf" => "des_cpf", // CPF da pessoa
        "phone" => "num_phone", // Número do telefone celular da pessoa
        "photo" => "bin_photo" // Foto da pessoa
    ];


    /**
     * Traduz os valores da tabela para o objeto
     *
     * @param object $row Linha da tabela de pessoas
     *
     * @return void
     */
    private function _translate(object $row): void
    {
        foreach (self::$columns as $key => $value) {
            $this->$key = $row->$value;
        }
    }


    /**
     * Obtém o nome dos campos da tabela de pessoas
     *
     * @static
     *
     * @return string
     */
    private static function _getSelectFields(): string
    {
        return implode(", ", array_values(self::$columns));
    }


    /**
     * Carrega a pessoa a partir do ID fornecido
     *
     * @param int $idPerson ID da pessoa
     *
     * @return void
     */
    protected function loadPersonId(int $idPerson): void
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_persons
                   WHERE id_person = :pid_person";

        $row = (new SQL())->send($query, ["pid_person" => $idPerson])->fetch();
        $row && $this->_translate($row);
    }


    /**
     * Retorna o ID da pessoa a partir do e-mail fornecido
     *
     * @param string $email E-mail da pessoa
     *
     * @static
     *
     * @return ?int
     */
    private static function _getFromEmail(string $email): ?int
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_persons
                   WHERE des_email = :pdes_email";

        $row = (new SQL())->send($query, ["pdes_email" => $email])->fetch();
        return $row ? $row->id_person : null;
    }


    /**
     * Retorna o ID da pessoa a partir do CPF fornecido
     *
     * @param string $cpf CPF da pessoa
     *
     * @static
     *
     * @return ?int
     */
    private static function _getFromCpf(string $cpf): ?int
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_persons
                   WHERE des_cpf = :pdes_cpf";

        $row = (new SQL())->send($query, ["pdes_cpf" => $cpf])->fetch();
        return $row ? $row->id_person : null;
    }


    /**
     * Carrega a classe a partir de um vetor de argumentos
     *
     * @param array $arguments Vetor com os dados da pessoa
     *
     * @return void
     */
    protected function loadData(array $arguments): void
    {
        $this->idPerson = (int) ($arguments["idPerson"] ?? 0);
        $this->name = trim($arguments["name"] ?? "");
        $this->email = trim(strtolower($arguments["email"] ?? "")) ?: null;
        $this->cpf = (string) preg_replace("/\D/", "", $arguments["cpf"] ?? null) ?: null;
        $this->phone = (int) (preg_replace("/\D/", "", $arguments["phone"] ?? 0)) ?: null;
        $this->photo = $arguments["photo"] ?? null;
    }


    /**
     * Valida se os argumentos da classe estão corretos
     *
     * @param array $errors Vetor para adicionar as mensagens
     *
     * @return bool
     */
    public function validate(array &$errors = []): bool
    {
        $this->idPerson < 0 && array_push($errors, "ID da pessoa inválido");

        (strlen($this->name) < 6 || strlen($this->name) > 64) &&
        array_push($errors, "nome possui tamanho inválido");

        $this->email !== null && (strlen($this->email) < 6 || strlen($this->email) > 128) &&
        array_push($errors, "e-mail possui tamanho inválido");

        $this->email !== null && !filter_var($this->email, FILTER_VALIDATE_EMAIL) &&
        array_push($errors, "e-mail inválido");

        $this->email !== null && ($id = (int) self::_getFromEmail($this->email)) &&
        $id !== $this->idPerson &&
        array_push($errors, "e-mail já cadastrado");

        $this->cpf !== null && !$this->_validateCpf($this->cpf) &&
        array_push($errors, "CPF inválido");

        $this->cpf !== null && ($id = (int) self::_getFromCpf($this->cpf)) &&
        $id !== $this->idPerson &&
        array_push($errors, "CPF já cadastrado");

        $this->phone !== null && !$this->_validatePhone($this->phone) &&
        array_push($errors, "telefone inválido");

        return empty($errors);
    }


    /**
     * Retorna a classe em formato de vetor
     *
     * @return array
     */
    public function array(): array
    {
        return [
            "idPerson" => $this->idPerson,
            "name" => $this->name,
            "email" => $this->email,
            "cpf" => $this->cpf,
            "phone" => $this->phone,
            "photo" => $this->photo ? base64_encode($this->photo) : $this->photo
        ];
    }


    /**
     * Retorna a classe em formato de vetor para serializar em formato JSON
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->array();
    }
}
