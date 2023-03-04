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
 * Classe que modela a entidade ENDEREÇO
 *
 * @category Model
 * @package  Amichi/Model
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class Address extends Model implements JsonSerializable
{
    use Validator;


    /**
     * Propriedade
     *
     * @var array $_columns Colunas de mapeamento objeto relacional
     */
    private static array $_columns = [
        "id" => "id_address", // ID do endereço
        "idPerson" => "id_person", // ID da pessoa do endereço
        "idCity" => "id_city", // ID da cidade do endereço
        "idStreetType" => "id_street_type", // ID do tipo de logradouro do endereço
        "publicPlace" => "des_address", // Logradouro do endereço
        "number" => "des_number", // Número do endereço
        "district" => "des_district", // Bairro do endereço
        "complement" => "des_complement", // Complemento do endereço
        "reference" => "des_reference", // Referência do endereço
        "zipCode" => "num_zip_code", // CEP do endereço
        "dateRegister" => "dt_address_created_at", // Data de cadastro do endereço
        "dateLastChange" => "dt_address_changed_in" // Data da última alteração do endereço
    ];


    /**
     * Construtor
     *
     * @param int $id ID do endereço
     *
     * @return void
     */
    public function __construct(int $id = 0)
    {
        $id > 0 && ($address = self::loadFromId($id)) && self::loadFromData($address->array(), $this);
    }


    /**
     * Traduz os valores da tabela para o objeto
     *
     * @param object $row     Linha da tabela de endereços
     * @param ?self  $address Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    private static function _translate(object $row, ?self $address = null): self
    {
        $address ??= new self();

        foreach (self::$_columns as $key => $value) {
            $address->$key = $row->$value;
        }

        $address->user = User::loadFromPersonId($row->id_person);
        $address->city = new City($row->id_city);
        $address->streetType = $address->idStreetType ? new StreetType($row->id_street_type) : null;

        parent::cache($address->id, $address);
        return $address;
    }


    /**
     * Obtém o nome dos campos da tabela de endereços
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
        $query = "CALL sp_save_address (:pid_address, :pid_person, :pid_city, :pid_street_type, :pdes_address,
                                        :pdes_number, :pdes_district, :pdes_complement, :pdes_reference, :pnum_zip_code)";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_address", $this->id, \PDO::PARAM_INT);
        $stmt->bindValue("pid_person", $this->idPerson, \PDO::PARAM_INT);
        $stmt->bindValue("pid_city", $this->idCity, \PDO::PARAM_INT);
        $stmt->bindValue("pid_street_type", $this->idStreetType, \PDO::PARAM_INT);
        $stmt->bindValue("pdes_address", $this->publicPlace, \PDO::PARAM_STR);
        $stmt->bindValue("pdes_number", $this->number, \PDO::PARAM_STR);
        $stmt->bindValue("pdes_district", $this->district, \PDO::PARAM_STR);
        $stmt->bindValue("pdes_complement", $this->complement, \PDO::PARAM_STR);
        $stmt->bindValue("pdes_reference", $this->reference, \PDO::PARAM_STR);
        $stmt->bindValue("pnum_zip_code", $this->zipCode, \PDO::PARAM_INT);
        $stmt->execute();

        $address = $stmt->fetch();
        $stmt->closeCursor();

        self::_translate($address, $this);
        return $this;
    }


    /**
     * Remove o objeto no banco de dados
     *
     * @return self
     */
    public function delete(): self
    {
        $query = "DELETE FROM tb_addresses WHERE id_address = :pid_address";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_address", $this->id, \PDO::PARAM_INT);
        $stmt->execute();

        return $this;
    }


    /**
     * Retorna todos os registros da tabela de endereços
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
        $query = "SELECT $fields FROM tb_addresses" .
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
     * Retorna os endereços a partir do ID da cidade fornecido
     *
     * @param int $idCity ID da cidade
     *
     * @static
     *
     * @return array[self]
     */
    public static function listFromCityId(int $idCity): array
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_addresses
                   INNER JOIN tb_cities USING (id_city)
                   WHERE id_city = :pid_city";

        return array_map(
            fn (object $row): self => self::_translate($row),
            (SQL::get())->send($query, ["pid_city" => $idCity])->fetchAll()
        );
    }


    /**
     * Retorna os endereços a partir do ID do tipo de logradouro fornecido
     *
     * @param int $idStreetType ID do tipo de logradouro
     *
     * @static
     *
     * @return array[self]
     */
    public static function listFromStreetTypeId(int $idStreetType): array
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_addresses
                   INNER JOIN tb_street_types USING (id_street_type)
                   WHERE id_street_type = :pid_street_type";

        return array_map(
            fn (object $row): self => self::_translate($row),
            (SQL::get())->send($query, ["pid_street_type" => $idStreetType])->fetchAll()
        );
    }


    /**
     * Retorna o endereço a partir do ID fornecido
     *
     * @param int $id ID do endereço
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromId(int $id): ?self
    {
        if ($address = parent::cache($id, __CLASS__)) {
            return $address;
        }

        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_addresses
                   WHERE id_address = :pid_address";

        $row = (SQL::get())->send($query, ["pid_address" => $id])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Retorna o endereço a partir do ID da pessoa fornecido
     *
     * @param int $idPerson ID da pessoa
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromPersonId(int $idPerson): ?self
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_addresses
                   WHERE id_person = :pid_person";

        $row = (SQL::get())->send($query, ["pid_person" => $idPerson])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Retorna o endereço a partir do ID do usuário fornecido
     *
     * @param int $idUser ID do usuário
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromUserId(int $idUser): ?self
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_addresses
                   INNER JOIN tb_users USING (id_person)
                   WHERE id_user = :pid_user";

        $row = (SQL::get())->send($query, ["pid_user" => $idUser])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Instancia a classe a partir de um vetor de argumentos
     *
     * @param array $arguments Vetor com os dados do endereço
     * @param ?self $address   Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    public static function loadFromData(array $arguments, ?self $address = null): self
    {
        $address ??= new self();

        $address->id = (int) ($arguments["id"] ?? 0);
        $address->idPerson = (int) ($arguments["idPerson"] ?? 0);
        $address->user = ($arguments["user"] ?? []) ? User::loadFromData($arguments["user"]) : User::loadFromPersonId($address->idPerson);
        $address->idCity = (int) ($arguments["idCity"] ?? 0);
        $address->city = ($arguments["city"] ?? []) ? City::loadFromData($arguments["city"]) : City::loadFromId($address->idCity);
        $address->idStreetType = (int) ($arguments["idStreetType"] ?? 0) ?: null;
        $address->streetType = ($arguments["streetType"] ?? []) ? StreetType::loadFromData($arguments["streetType"]) : StreetType::loadFromId((int) $address->idStreetType);
        $address->publicPlace = trim($arguments["publicPlace"] ?? "");
        $address->number = trim(strtoupper($arguments["number"] ?? ""));
        $address->district = trim($arguments["district"] ?? "") ?: null;
        $address->complement = trim($arguments["complement"] ?? "");
        $address->reference = trim($arguments["reference"] ?? "") ?: null;
        $address->zipCode = (int) (preg_replace("/\D/", "", $arguments["zipCode"] ?? "")) ?: null;
        $address->dateRegister = $arguments["dateRegister"] ?? date("Y-m-d H:i:s");
        $address->dateLastChange = $arguments["dateLastChange"] ?? null;

        return $address;
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

        $this->idPerson < 0 && array_push($errors, "pessoa inválida");

        (int) User::loadFromPersonId($this->idPerson)?->id <= 0 &&
        array_push($errors, "pessoa inexistente");

        $this->idPerson > 0 && ($id = (int) self::loadFromPersonId($this->idPerson)?->id) &&
        $id !== $this->id &&
        array_push($errors, "pessoa com outro endereço cadastrado");

        $this->idCity < 0 && array_push($errors, "cidade inválida");

        (int) City::loadFromId($this->idCity)?->id <= 0 &&
        array_push($errors, "cidade inexistente");

        $this->idStreetType !== null && $this->idStreetType <= 0 &&
        array_push($errors, "tipo de logradouro inválido");

        $this->idStreetType !== null && (int) StreetType::loadFromId($this->idStreetType)?->id <= 0 &&
        array_push($errors, "tipo de logradouro inexistente");

        (strlen($this->publicPlace) < 6 || strlen($this->publicPlace) > 128) &&
        array_push($errors, "logradouro possui tamanho inválido");

        (strlen($this->number) < 1 || strlen($this->number) > 8) &&
        array_push($errors, "número possui tamanho inválido");

        !preg_match("/^[A-Z0-9]+$/", $this->number) &&
        array_push($errors, "número possui caracteres inválidos");

        $this->district !== null && (strlen($this->district) < 4 || strlen($this->district) > 32) &&
        array_push($errors, "bairro possui tamanho inválido");

        (strlen($this->complement) < 4 || strlen($this->complement) > 32) &&
        array_push($errors, "complemento possui tamanho inválido");

        $this->reference !== null && (strlen($this->reference) < 4 || strlen($this->reference) > 32) &&
        array_push($errors, "referência possui tamanho inválido");

        !$this->_validateZipCode((string) $this->zipCode) &&
        array_push($errors, "CEP inválido");

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
            "idPerson" => $this->idPerson,
            "user" => $this->user->array(),
            "idCity" => $this->idCity,
            "city" => $this->city->array(),
            "idStreetType" => $this->idStreetType,
            "streetType" => $this->streetType?->array(),
            "publicPlace" => $this->publicPlace,
            "number" => $this->number,
            "district" => $this->district,
            "complement" => $this->complement,
            "reference" => $this->reference,
            "zipCode" => $this->zipCode,
            "dateRegister" => $this->dateRegister,
            "dateLastChange" => $this->dateLastChange
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
