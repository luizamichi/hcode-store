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
 * Classe que modela a entidade ESTADO
 *
 * @category Model
 * @package  Amichi/Model
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class State extends Model implements JsonSerializable
{
    /**
     * Propriedade
     *
     * @var array $_columns Colunas de mapeamento objeto relacional
     */
    private static array $_columns = [
        "id" => "id_state", // ID do estado
        "idCountry" => "id_country", // ID do país do estado
        "ibgeCode" => "num_ibge_state", // Código IBGE do estado
        "name" => "des_state", // Nome do estado
        "uf" => "des_uf" // Unidade federativa do estado
    ];


    /**
     * Construtor
     *
     * @param int $id ID do estado
     *
     * @return void
     */
    public function __construct(int $id = 0)
    {
        $id > 0 && ($state = self::loadFromId($id)) && self::loadFromData($state->array(), $this);
    }


    /**
     * Traduz os valores da tabela para o objeto
     *
     * @param object $row   Linha da tabela de estados
     * @param ?self  $state Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    private static function _translate(object $row, ?self $state = null): self
    {
        $state ??= new self();

        foreach (self::$_columns as $key => $value) {
            $state->$key = $row->$value;
        }

        $state->country = new Country($row->id_country);

        parent::cache($state->id, $state);
        return $state;
    }


    /**
     * Obtém o nome dos campos da tabela de estados
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
        $query = "CALL sp_save_state (:pid_state, :pid_country, :pnum_ibge_state, :pdes_state, :pdes_uf)";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_state", $this->id, \PDO::PARAM_INT);
        $stmt->bindValue("pid_country", $this->idCountry, \PDO::PARAM_INT);
        $stmt->bindValue("pnum_ibge_state", $this->ibgeCode, \PDO::PARAM_INT);
        $stmt->bindValue("pdes_state", $this->name, \PDO::PARAM_STR);
        $stmt->bindValue("pdes_uf", $this->uf, \PDO::PARAM_STR);
        $stmt->execute();

        $state = $stmt->fetch();
        $stmt->closeCursor();

        self::_translate($state, $this);
        return $this;
    }


    /**
     * Remove o objeto no banco de dados
     *
     * @return self
     */
    public function delete(): self
    {
        $query = "DELETE FROM tb_states WHERE id_state = :pid_state";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_state", $this->id, \PDO::PARAM_INT);
        $stmt->execute();

        return $this;
    }


    /**
     * Retorna todos os registros da tabela de estados
     *
     * @param int    $limit  Limite de registros
     * @param int    $offset Deslocamento dos registros
     * @param string $sortBy Ordenar pelo campo informado
     *
     * @static
     *
     * @return array[self]
     */
    public static function listAll(int $limit = 0, int $offset = 0, string $sortBy = ""): array
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields FROM tb_states" .
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
     * Retorna o estado a partir do ID fornecido
     *
     * @param int $id ID do estado
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromId(int $id): ?self
    {
        if ($state = parent::cache($id, __CLASS__)) {
            return $state;
        }

        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_states
                   WHERE id_state = :pid_state";

        $row = (SQL::get())->send($query, ["pid_state" => $id])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Retorna os estados a partir do ID do país fornecido
     *
     * @param int $idCountry ID do país
     *
     * @static
     *
     * @return array[self]
     */
    public static function listFromCountryId(int $idCountry): array
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_states
                   WHERE id_country = :pid_country";

        return array_map(
            fn (object $row): self => self::_translate($row),
            (SQL::get())->send($query, ["pid_country" => $idCountry])->fetchAll()
        );
    }


    /**
     * Retorna o estado a partir do código IBGE fornecido
     *
     * @param int $ibgeCode Código IBGE do estado
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromIbgeCode(int $ibgeCode): ?self
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_states
                   WHERE num_ibge_state = :pnum_ibge_state";

        $row = (SQL::get())->send($query, ["pnum_ibge_state" => $ibgeCode])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Retorna o estado a partir da unidade federativa e do país fornecido
     *
     * @param string $uf        Unidade federativa
     * @param int    $idCountry ID do país
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromUf(string $uf, int $idCountry): ?self
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_states
                   WHERE des_uf = :pdes_uf
                     AND id_country = :pid_country";

        $row = (SQL::get())->send(
            $query,
            [
                "pdes_uf" => $uf,
                "pid_country" => $idCountry
            ]
        )->fetch();

        return $row ? self::_translate($row) : null;
    }


    /**
     * Instancia a classe a partir de um vetor de argumentos
     *
     * @param array $arguments Vetor com os dados do estado
     * @param ?self $state     Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    public static function loadFromData(array $arguments, ?self $state = null): self
    {
        $state ??= new self();

        $state->id = (int) ($arguments["id"] ?? 0);
        $state->idCountry = (int) ($arguments["idCountry"] ?? 0);
        $state->country = ($arguments["country"] ?? []) ? Country::loadFromData($arguments["country"]) : Country::loadFromId($state->idCountry);
        $state->ibgeCode = (int) ($arguments["ibgeCode"] ?? 0) ?: null;
        $state->name = trim($arguments["name"] ?? "");
        $state->uf = trim(strtoupper($arguments["uf"] ?? ""));

        return $state;
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
        $this->id < 0 && array_push($errors, "ID inválido");

        $this->idCountry < 0 && array_push($errors, "país inválido");

        (int) Country::loadFromId($this->idCountry)?->id <= 0 &&
        array_push($errors, "país inexistente");

        $this->ibgeCode !== null && $this->ibgeCode <= 0 &&
        array_push($errors, "código IBGE inválido");

        $this->ibgeCode !== null && ($id = (int) self::loadFromIbgeCode($this->ibgeCode)?->id) &&
        $id !== $this->id &&
        array_push($errors, "código IBGE já cadastrado");

        (strlen($this->name) < 2 || strlen($this->name) > 32) &&
        array_push($errors, "nome possui tamanho inválido");

        (strlen($this->uf) !== 2 || !preg_match("/[A-Z]/", $this->uf)) &&
        array_push($errors, "unidade federativa possui tamanho inválido");

        ($id = (int) self::loadFromUf($this->uf, $this->idCountry)?->id) &&
        $id !== $this->id &&
        array_push($errors, "unidade federativa já cadastrada");

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
            "idCountry" => $this->idCountry,
            "country" => $this->country->array(),
            "ibgeCode" => $this->ibgeCode,
            "name" => $this->name,
            "uf" => $this->uf
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
