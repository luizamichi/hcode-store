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
 * Classe que modela a entidade CIDADE
 *
 * @category Model
 * @package  Amichi/Model
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class City extends Model implements JsonSerializable
{
    /**
     * Propriedade
     *
     * @var array<string,string> $_columns Colunas de mapeamento objeto relacional
     */
    private static array $_columns = [
        "id" => "id_city", // ID da cidade
        "idState" => "id_state", // ID do estado da cidade
        "ibgeCode" => "num_ibge_city", // Código IBGE da cidade
        "name" => "des_city", // Nome da cidade
        "ddd" => "num_ddd", // Código DDD da cidade
        "dateRegister" => "dt_city_created_at" // Data de cadastro da cidade
    ];


    /**
     * Construtor
     *
     * @param int $id ID da cidade
     *
     * @return void
     */
    public function __construct(int $id = 0)
    {
        $id > 0 && ($city = self::loadFromId($id)) && self::loadFromData($city->array(), $this);
    }


    /**
     * Traduz os valores da tabela para o objeto
     *
     * @param object $row  Linha da tabela de cidades
     * @param ?self  $city Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    private static function _translate(object $row, ?self $city = null): self
    {
        $city ??= new self();

        foreach (self::$_columns as $key => $value) {
            $city->$key = $row->$value;
        }

        $city->state = new State($row->id_state);

        parent::cache($city->id, $city);
        return $city;
    }


    /**
     * Obtém o nome dos campos da tabela de cidades
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
        $query = "CALL sp_save_city (:pid_city, :pid_state, :pnum_ibge_city, :pdes_city, :pnum_ddd)";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_city", $this->id, \PDO::PARAM_INT);
        $stmt->bindValue("pid_state", $this->idState, \PDO::PARAM_INT);
        $stmt->bindValue("pnum_ibge_city", $this->ibgeCode, \PDO::PARAM_INT);
        $stmt->bindValue("pdes_city", $this->name, \PDO::PARAM_STR);
        $stmt->bindValue("pnum_ddd", $this->ddd, \PDO::PARAM_INT);
        $stmt->execute();

        $city = $stmt->fetch();
        $stmt->closeCursor();

        self::_translate($city, $this);
        return $this;
    }


    /**
     * Remove o objeto no banco de dados
     *
     * @return self
     */
    public function delete(): self
    {
        $query = "DELETE FROM tb_cities WHERE id_city = :pid_city";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_city", $this->id, \PDO::PARAM_INT);
        $stmt->execute();

        return $this;
    }


    /**
     * Retorna todos os registros da tabela de cidades
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
        $query = "SELECT $fields FROM tb_cities" .
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
     * Retorna a cidade a partir do ID fornecido
     *
     * @param int $id ID da cidade
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromId(int $id): ?self
    {
        if ($city = parent::cache($id, __CLASS__)) {
            return $city;
        }

        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_cities
                   WHERE id_city = :pid_city";

        $row = (SQL::get())->send($query, ["pid_city" => $id])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Retorna as cidades a partir do ID do estado fornecido
     *
     * @param int $idState ID do estado
     *
     * @static
     *
     * @return array<self>
     */
    public static function listFromStateId(int $idState): array
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_cities
                   WHERE id_state = :pid_state";

        return array_map(
            fn (object $row): self => self::_translate($row),
            (SQL::get())->send($query, ["pid_state" => $idState])->fetchAll()
        );
    }


    /**
     * Retorna a cidade a partir do código IBGE fornecido
     *
     * @param int $ibgeCode Código IBGE da cidade
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromIbgeCode(int $ibgeCode): ?self
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_cities
                   WHERE num_ibge_city = :pnum_ibge_city";

        $row = (SQL::get())->send($query, ["pnum_ibge_city" => $ibgeCode])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Retorna a cidade a partir do nome e do ID do estado fornecido
     *
     * @param string $name    Nome da cidade
     * @param int    $idState ID do estado
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromName(string $name, int $idState): ?self
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_cities
                   WHERE id_state = :pid_state
                     AND des_city = :pdes_city";

        $row = (SQL::get())->send(
            $query,
            [
                "pid_state" => $idState,
                "pdes_city" => $name
            ]
        )->fetch();

        return $row ? self::_translate($row) : null;
    }


    /**
     * Instancia a classe a partir de um vetor de argumentos
     *
     * @param array<mixed> $arguments Vetor com os dados da cidade
     * @param ?self        $city      Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    public static function loadFromData(array $arguments, ?self $city = null): self
    {
        $city ??= new self();

        $city->id = (int) ($arguments["id"] ?? 0);
        $city->idState = (int) ($arguments["idState"] ?? 0);
        $city->state = ($arguments["state"] ?? []) ? State::loadFromData($arguments["state"]) : State::loadFromId($city->idState);
        $city->ibgeCode = (int) ($arguments["ibgeCode"] ?? 0) ?: null;
        $city->name = trim($arguments["name"] ?? "");
        $city->ddd = (int) ($arguments["ddd"] ?? 0) ?: null;
        $city->dateRegister = $arguments["dateRegister"] ?? date("Y-m-d H:i:s");

        return $city;
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

        $this->idState < 0 && array_push($errors, "estado inválido");

        (int) State::loadFromId($this->idState)?->id <= 0 &&
        array_push($errors, "estado inexistente");

        $this->ibgeCode !== null && $this->ibgeCode <= 0 &&
        array_push($errors, "código IBGE inválido");

        $this->ibgeCode !== null && ($id = (int) self::loadFromIbgeCode($this->ibgeCode)?->id) &&
        $id !== $this->id &&
        array_push($errors, "código IBGE já cadastrado");

        (strlen($this->name) < 2 || strlen($this->name) > 32) &&
        array_push($errors, "nome possui tamanho inválido");

        ($id = (int) self::loadFromName($this->name, $this->idState)?->id) &&
        $id !== $this->id &&
        array_push($errors, "nome já cadastrado");

        $this->ddd !== null && $this->ddd <= 0 &&
        array_push($errors, "DDD inválido");

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
            "idState" => $this->idState,
            "state" => $this->state->array(),
            "ibgeCode" => $this->ibgeCode,
            "name" => $this->name,
            "ddd" => $this->ddd,
            "dateRegister" => $this->dateRegister
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
