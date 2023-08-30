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
use JsonSerializable;

/**
 * Classe que modela a entidade TIPO DE LOGRADOURO
 *
 * @category Model
 * @package  Amichi/Model
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class StreetType extends Model implements JsonSerializable
{
    /**
     * Propriedade
     *
     * @var array<string,string> $_columns Colunas de mapeamento objeto relacional
     */

    private static array $_columns = [
        "id" => "id_street_type", // ID do tipo de logradouro
        "name" => "des_street_type", // Nome do tipo de logradouro
        "acronym" => "des_acronym" // Acrônimo do tipo de logradouro
    ];


    /**
     * Construtor
     *
     * @param int $id ID do tipo de logradouro
     *
     * @return void
     */
    public function __construct(int $id = 0)
    {
        $id > 0 && ($streetType = self::loadFromId($id)) && self::loadFromData($streetType->array(), $this);
    }


    /**
     * Traduz os valores da tabela para o objeto
     *
     * @param object $row        Linha da tabela de tipos de logradouro
     * @param ?self  $streetType Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    private static function _translate(object $row, ?self $streetType = null): self
    {
        $streetType ??= new self();

        foreach (self::$_columns as $key => $value) {
            $streetType->$key = $row->$value;
        }

        parent::cache($streetType->id, $streetType);
        return $streetType;
    }


    /**
     * Obtém o nome dos campos da tabela de tipos de logradouro
     *
     * @static
     *
     * @return string
     */
    private static function _getSelectFields(): string
    {
        return implode(", ", array_values(self::$_columns));
    }


    /**
     * Salva o objeto no banco de dados
     *
     * @return self
     */
    public function save(): self
    {
        $query = "CALL sp_save_street_type (:pid_street_type, :pdes_street_type, :pdes_acronym)";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_street_type", $this->id, \PDO::PARAM_INT);
        $stmt->bindValue("pdes_street_type", $this->name, \PDO::PARAM_STR);
        $stmt->bindValue("pdes_acronym", $this->acronym, \PDO::PARAM_STR);
        $stmt->execute();

        self::_translate($stmt->fetch(), $this);
        return $this;
    }


    /**
     * Remove o objeto no banco de dados
     *
     * @return self
     */
    public function delete(): self
    {
        $query = "DELETE FROM tb_street_types WHERE id_street_type = :pid_street_type";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_street_type", $this->id, \PDO::PARAM_INT);
        $stmt->execute();

        return $this;
    }


    /**
     * Retorna todos os registros da tabela de tipos de logradouro
     *
     * @param int    $limit  Limite de registros
     * @param int    $offset Deslocamento dos registros
     * @param string $sortBy Ordenar pelo campo informado
     *
     * @static
     *
     * @return array<self>
     */
    public static function listAll(int $limit = 0, int $offset = 0, string $sortBy = ""): array
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields FROM tb_street_types" .
            (in_array($sortBy, array_keys(self::$_columns))
                ? " ORDER BY " . self::$_columns[$sortBy]
                : "") .
            ($limit > 0 ? " LIMIT $limit" : "") .
            ($offset > 0 ? " OFFSET $offset" : "");

        return array_map(
            fn (object $row): self => self::_translate($row),
            (SQL::get())->send($query)->fetchAll()
        );
    }


    /**
     * Retorna o tipo de logradouro a partir do ID fornecido
     *
     * @param int $id ID do tipo de logradouro
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromId(int $id): ?self
    {
        if ($streetType = parent::cache($id, __CLASS__)) {
            return $streetType;
        }

        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_street_types
                   WHERE id_street_type = :pid_street_type";

        $row = (SQL::get())->send($query, ["pid_street_type" => $id])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Retorna o tipo de logradouro a partir do nome fornecido
     *
     * @param string $name Nome do tipo de logradouro
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromName(string $name): ?self
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_street_types
                   WHERE des_street_type = :pdes_street_type";

        $row = (SQL::get())->send($query, ["pdes_street_type" => $name])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Retorna o tipo de logradouro a partir do acrônimo fornecido
     *
     * @param string $acronym Acrônimo do tipo de logradouro
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromAcronym(string $acronym): ?self
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_street_types
                   WHERE des_acronym = :pdes_acronym";

        $row = (SQL::get())->send($query, ["pdes_acronym" => $acronym])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Instancia a classe a partir de um vetor de argumentos
     *
     * @param array<mixed> $arguments  Vetor com os dados do tipo de logradouro
     * @param ?self        $streetType Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    public static function loadFromData(array $arguments, ?self $streetType = null): self
    {
        $streetType ??= new self();

        $streetType->id = (int) ($arguments["id"] ?? 0);
        $streetType->name = trim($arguments["name"] ?? "");
        $streetType->acronym = trim(strtoupper($arguments["acronym"] ?? "")) ?: null;

        return $streetType;
    }


    /**
     * Valida se os argumentos da classe estão corretos
     *
     * @param array<string> $errors Vetor para adicionar as mensagens
     *
     * @return bool
     */
    public function validate(array &$errors = []): bool
    {
        $this->id < 0 && array_push($errors, "ID inválido");

        (strlen($this->name) < 2 || strlen($this->name) > 32) &&
        array_push($errors, "nome possui tamanho inválido");

        ($id = (int) self::loadFromName($this->name)?->id) &&
        $id !== $this->id &&
        array_push($errors, "nome já cadastrado");

        $this->acronym !== null && (strlen($this->acronym) === 0 || strlen($this->acronym) > 4) &&
        array_push($errors, "acrônimo possui tamanho inválido");

        ($id = (int) self::loadFromAcronym((string) $this->acronym)?->id) &&
        $id !== $this->id &&
        array_push($errors, "acrônimo já cadastrado");

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
            "id" => $this->id,
            "name" => $this->name,
            "acronym" => $this->acronym
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
