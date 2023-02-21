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
 * Classe que modela a entidade PAÍS
 *
 * @category Model
 * @package  Amichi/Model
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class Country extends Model implements JsonSerializable
{
    /**
     * Propriedade
     *
     * @var array $_columns Colunas de mapeamento objeto relacional
     */
    private static array $_columns = [
        "id" => "id_country", // ID do país
        "ibgeCode" => "num_ibge_country", // Código IBGE do país
        "name" => "des_country", // Nome do país
        "coi" => "des_coi", // Código do Comitê Olímpico Internacional (COI) do país
        "ddi" => "num_ddi" // Código DDI do país
    ];


    /**
     * Construtor
     *
     * @param int $id ID do país
     *
     * @return void
     */
    public function __construct(int $id = 0)
    {
        $id > 0 && ($country = self::loadFromId($id)) && self::loadFromData($country->array(), $this);
    }


    /**
     * Traduz os valores da tabela para o objeto
     *
     * @param object $row     Linha da tabela de países
     * @param ?self  $country Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    private static function _translate(object $row, ?self $country = null): self
    {
        $country ??= new self();

        foreach (self::$_columns as $key => $value) {
            $country->$key = $row->$value;
        }

        parent::cache($country->id, $country);
        return $country;
    }


    /**
     * Obtém o nome dos campos da tabela de países
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
        $query = "CALL sp_save_country (:pid_country, :pnum_ibge_country, :pdes_country, :pdes_coi, :pnum_ddi)";

        $stmt = (new SQL())->prepare($query);
        $stmt->bindValue("pid_country", $this->id, \PDO::PARAM_INT);
        $stmt->bindValue("pnum_ibge_country", $this->ibgeCode, \PDO::PARAM_INT);
        $stmt->bindValue("pdes_country", $this->name, \PDO::PARAM_STR);
        $stmt->bindValue("pdes_coi", $this->coi, \PDO::PARAM_STR);
        $stmt->bindValue("pnum_ddi", $this->ddi, \PDO::PARAM_INT);
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
        $query = "DELETE FROM tb_countries WHERE id_country = :pid_country";

        $stmt = (new SQL())->prepare($query);
        $stmt->bindValue("pid_country", $this->id, \PDO::PARAM_INT);
        $stmt->execute();

        return $this;
    }


    /**
     * Retorna todos os registros da tabela de países
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
        $query = "SELECT $fields FROM tb_countries" .
            (in_array($sortBy, array_keys(self::$_columns))
                ? " ORDER BY " . self::$_columns[$sortBy]
                : "") .
            ($limit > 0 ? " LIMIT $limit" : "") .
            ($offset > 0 ? " OFFSET $offset" : "");

        return array_map(
            fn (object $row): self => self::_translate($row),
            (new SQL())->send($query)->fetchAll()
        );
    }


    /**
     * Retorna o país a partir do ID fornecido
     *
     * @param int $id ID do país
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromId(int $id): ?self
    {
        if ($country = parent::cache($id, __CLASS__)) {
            return $country;
        }

        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_countries
                   WHERE id_country = :pid_country";

        $row = (new SQL())->send($query, ["pid_country" => $id])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Retorna o país a partir do código IBGE fornecido
     *
     * @param int $ibgeCode Código IBGE do país
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromIbgeCode(int $ibgeCode): ?self
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_countries
                   WHERE num_ibge_country = :pnum_ibge_country";

        $row = (new SQL())->send($query, ["pnum_ibge_country" => $ibgeCode])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Retorna o país a partir do código COI do país fornecido
     *
     * @param string $coi Código COI do país
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromCOI(string $coi): ?self
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_countries
                   WHERE des_coi = :pdes_coi";

        $row = (new SQL())->send($query, ["pdes_coi" => $coi])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Instancia a classe a partir de um vetor de argumentos
     *
     * @param array $arguments Vetor com os dados do país
     * @param ?self $country   Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    public static function loadFromData(array $arguments, ?self $country = null): self
    {
        $country ??= new self();

        $country->id = (int) ($arguments["id"] ?? 0);
        $country->ibgeCode = (int) ($arguments["ibgeCode"] ?? 0) ?: null;
        $country->name = trim($arguments["name"] ?? "");
        $country->coi = trim(strtoupper($arguments["coi"] ?? ""));
        $country->ddi = (int) ($arguments["ddi"] ?? 0) ?: null;

        return $country;
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

        $this->ibgeCode !== null && $this->ibgeCode <= 0 &&
        array_push($errors, "código IBGE inválido");

        $this->ibgeCode !== null && ($id = (int) self::loadFromIbgeCode($this->ibgeCode)?->id) &&
        $id !== $this->id &&
        array_push($errors, "código IBGE já cadastrado");

        (strlen($this->name) < 2 || strlen($this->name) > 32) &&
        array_push($errors, "nome possui tamanho inválido");

        (strlen($this->coi) !== 3 || !preg_match("/[A-Z]/", $this->coi)) &&
        array_push($errors, "COI inválido");

        ($id = (int) self::loadFromCOI($this->coi)?->id) &&
        $id !== $this->id &&
        array_push($errors, "COI já cadastrado");

        $this->ddi !== null && $this->ddi <= 0 &&
        array_push($errors, "código DDI inválido");

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
            "ibgeCode" => $this->ibgeCode,
            "name" => $this->name,
            "coi" => $this->coi,
            "ddi" => $this->ddi
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
