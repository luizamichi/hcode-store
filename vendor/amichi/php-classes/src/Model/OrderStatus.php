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
use Amichi\Enumerated\OrderStatus as EnumeratedOrderStatus;
use Amichi\Model;
use JsonSerializable;

/**
 * Classe que modela a entidade STATUS DO PEDIDO
 *
 * @category Model
 * @package  Amichi/Model
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class OrderStatus extends Model implements JsonSerializable
{
    /**
     * Propriedade
     *
     * @var array<string,string> $_columns Colunas de mapeamento objeto relacional
     */
    private static array $_columns = [
        "id" => "id_status", // ID do status do pedido
        "description" => "des_status", // Descrição do status do pedido
        "code" => "num_code", // Código do status do pedido
        "dateRegister" => "dt_status_created_at" // Data de cadastro do status do pedido
    ];


    /**
     * Construtor
     *
     * @param int $id ID do status do pedido
     *
     * @return void
     */
    public function __construct(int $id = 0)
    {
        $id > 0 && ($orderStatus = self::loadFromId($id)) && self::loadFromData($orderStatus->array(), $this);
    }


    /**
     * Traduz os valores da tabela para o objeto
     *
     * @param object $row         Linha da tabela de status do pedido
     * @param ?self  $orderStatus Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    private static function _translate(object $row, ?self $orderStatus = null): self
    {
        $orderStatus ??= new self();

        foreach (self::$_columns as $key => $value) {
            $orderStatus->$key = $row->$value;
        }

        $orderStatus->enum = EnumeratedOrderStatus::tryFrom($orderStatus->code);

        parent::cache($orderStatus->id, $orderStatus);
        return $orderStatus;
    }


    /**
     * Obtém o nome dos campos da tabela de status do pedido
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
        $query = "CALL sp_save_order_status (:pid_status, :pdes_status, :pnum_code)";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_status", $this->id, \PDO::PARAM_INT);
        $stmt->bindValue("pdes_status", $this->description, \PDO::PARAM_STR);
        $stmt->bindValue("pnum_code", $this->code, \PDO::PARAM_INT);
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
        $query = "DELETE FROM tb_orders_status WHERE id_status = :pid_status";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_status", $this->id, \PDO::PARAM_INT);
        $stmt->execute();

        return $this;
    }


    /**
     * Retorna todos os registros da tabela de status do pedido
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
        $query = "SELECT $fields FROM tb_orders_status" .
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
     * Retorna o status do pedido a partir do ID fornecido
     *
     * @param int $id ID do status do pedido
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromId(int $id): ?self
    {
        if ($orderStatus = parent::cache($id, __CLASS__)) {
            return $orderStatus;
        }

        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_orders_status
                   WHERE id_status = :pid_status";

        $row = (SQL::get())->send($query, ["pid_status" => $id])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Retorna o status do pedido a partir da descrição fornecida
     *
     * @param string $description Descrição do status do pedido
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromDescription(string $description): ?self
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_orders_status
                   WHERE des_status = :pdes_status";

        $row = (SQL::get())->send($query, ["pdes_status" => $description])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Retorna o status do pedido a partir do código fornecido
     *
     * @param int $code Código do status do pedido
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromCode(int $code): ?self
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_orders_status
                   WHERE num_code = :pnum_code";

        $row = (SQL::get())->send($query, ["pnum_code" => $code])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Retorna o status do pedido a partir do enumerado fornecido
     *
     * @param EnumeratedOrderStatus $enum Enumerado do status do pedido
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromEnum(EnumeratedOrderStatus $enum): ?self
    {
        return self::loadFromCode($enum->value);
    }


    /**
     * Instancia a classe a partir de um vetor de argumentos
     *
     * @param array<mixed> $arguments   Vetor com os dados do status do pedido
     * @param ?self        $orderStatus Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    public static function loadFromData(array $arguments, ?self $orderStatus = null): self
    {
        $orderStatus ??= new self();

        $orderStatus->id = (int) ($arguments["id"] ?? 0);
        $orderStatus->description = trim($arguments["description"] ?? "");
        $orderStatus->code = (int) ($arguments["code"] ?? 0);
        $orderStatus->enum = EnumeratedOrderStatus::tryFrom($orderStatus->code);
        $orderStatus->dateRegister = $arguments["dateRegister"] ?? date("Y-m-d H:i:s");

        return $orderStatus;
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

        (strlen($this->description) < 2 || strlen($this->description) > 32) &&
        array_push($errors, "descrição possui tamanho inválido");

        ($id = (int) self::loadFromDescription($this->description)?->id) &&
        $id !== $this->id &&
        array_push($errors, "descrição já cadastrada");

        ($this->code <= 0 || $this->code > 255) && array_push($errors, "código inválido");

        ($id = (int) self::loadFromCode($this->code)?->id) &&
        $id !== $this->id &&
        array_push($errors, "código já cadastrado");

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
            "description" => $this->description,
            "code" => $this->code,
            "enum" => $this->enum?->name,
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
