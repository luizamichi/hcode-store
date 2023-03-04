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
use Amichi\Enumerated\Bank;
use Amichi\Model;
use Amichi\Trait\Formatter;
use DateTime;
use JsonSerializable;
use OpenBoleto\Agente;
use OpenBoleto\Banco\BancoDoBrasil;
use OpenBoleto\Banco\Bradesco;
use OpenBoleto\Banco\Brb;
use OpenBoleto\Banco\Itau;
use OpenBoleto\Banco\Santander;
use OpenBoleto\Banco\Unicred;

/**
 * Classe que modela a entidade PEDIDO
 *
 * @category Model
 * @package  Amichi/Model
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class Order extends Model implements JsonSerializable
{
    use Formatter;


    /**
     * Propriedade
     *
     * @var array  $_columns Colunas de mapeamento objeto relacional
     */
    private static array $_columns = [
        "id" => "id_order", // ID do pedido
        "idCart" => "id_cart", // ID do carrinho do pedido
        "idUser" => "id_user", // ID do usuário do pedido
        "idStatus" => "id_status", // ID do status do pedido
        "idAddress" => "id_address", // ID do endereço do pedido
        "totalValue" => "vl_total", // Valor total do pedido
        "code" => "des_code", // Código do pedido
        "annotation" => "des_annotation", // Anotação do pedido
        "dateRegister" => "dt_order_created_at" // Data de cadastro do pedido
    ];


    /**
     * Construtor
     *
     * @param int $id ID do pedido
     *
     * @return void
     */
    public function __construct(int $id = 0)
    {
        $id > 0 && ($order = self::loadFromId($id)) && self::loadFromData($order->array(), $this);
    }


    /**
     * Traduz os valores da tabela para o objeto
     *
     * @param object $row   Linha da tabela de pedidos
     * @param ?self  $order Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    private static function _translate(object $row, ?self $order = null): self
    {
        $order ??= new self();

        foreach (self::$_columns as $key => $value) {
            $order->$key = $row->$value;
        }

        $order->cart = Cart::loadFromId($row->id_cart);
        $order->user = new User($row->id_user);
        $order->status = new OrderStatus($row->id_status);
        $order->address = new Address($row->id_address);

        parent::cache($order->id, $order);
        return $order;
    }


    /**
     * Obtém o nome dos campos da tabela de pedidos
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
        $query = "CALL sp_save_order (:pid_order, :pid_cart, :pid_user, :pid_status, :pid_address, :pvl_total)";

        if ($this->id <= 0) {
            Cart::clearSession();
        }

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_order", $this->id, \PDO::PARAM_INT);
        $stmt->bindValue("pid_cart", $this->idCart, \PDO::PARAM_INT);
        $stmt->bindValue("pid_user", $this->idUser, \PDO::PARAM_INT);
        $stmt->bindValue("pid_status", $this->idStatus, \PDO::PARAM_INT);
        $stmt->bindValue("pid_address", $this->idAddress, \PDO::PARAM_INT);
        $stmt->bindValue("pvl_total", $this->totalValue, \PDO::PARAM_STR);
        $stmt->execute();

        $order = $stmt->fetch();
        $stmt->closeCursor();

        self::_translate($order, $this);
        return $this;
    }


    /**
     * Remove o objeto no banco de dados
     *
     * @return self
     */
    public function delete(): self
    {
        $query = "DELETE FROM tb_orders WHERE id_order = :pid_order";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_order", $this->id, \PDO::PARAM_INT);
        $stmt->execute();

        return $this;
    }


    /**
     * Retorna todos os registros da tabela de pedidos
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
        $query = "SELECT $fields FROM tb_orders" .
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
     * Retorna o pedido a partir do ID fornecido
     *
     * @param int $id ID do pedido
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromId(int $id): ?self
    {
        if ($order = parent::cache($id, __CLASS__)) {
            return $order;
        }

        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_orders
                   WHERE id_order = :pid_order";

        $row = (SQL::get())->send($query, ["pid_order" => $id])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Retorna o pedido a partir do código fornecido
     *
     * @param str $code Código do pedido
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromCode(string $code): ?self
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_orders
                   WHERE des_code = :pdes_code";

        $row = (SQL::get())->send($query, ["pdes_code" => $code])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Instancia a classe a partir de um vetor de argumentos
     *
     * @param array $arguments Vetor com os dados do pedido
     * @param ?self $order     Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    public static function loadFromData(array $arguments, ?self $order = null): self
    {
        $order ??= new self();

        $order->id = (int) ($arguments["id"] ?? 0);
        $order->idCart = (int) ($arguments["idCart"] ?? 0) ?: null;
        $order->cart = ($arguments["cart"] ?? []) ? Cart::loadFromData($arguments["cart"]) : Cart::loadFromId((int) $order->idCart);
        $order->idUser = (int) ($arguments["idUser"] ?? 0) ?: null;
        $order->user = ($arguments["user"] ?? []) ? User::loadFromData($arguments["user"]) : User::loadFromId((int) $order->idUser);
        $order->idStatus = (int) ($arguments["idStatus"] ?? 0) ?: null;
        $order->status = ($arguments["status"] ?? []) ? OrderStatus::loadFromData($arguments["status"]) : OrderStatus::loadFromId((int) $order->idStatus);
        $order->idAddress = (int) ($arguments["idAddress"] ?? 0) ?: null;
        $order->address = ($arguments["address"] ?? []) ? Address::loadFromData($arguments["address"]) : Address::loadFromId((int) $order->idAddress);
        $order->totalValue = (float) ($arguments["totalValue"] ?? 0.0);
        $order->code = trim($arguments["code"] ?? "") ?: null;
        $order->annotation = trim($arguments["annotation"] ?? "") ?: null;
        $order->dateRegister = $arguments["dateRegister"] ?? date("Y-m-d H:i:s");

        return $order;
    }


    /**
     * Retorna o pedido a partir do ID do carrinho fornecido
     *
     * @param int $idCart ID do carrinho
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromCartId(int $idCart): ?self
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_orders
                   WHERE id_cart = :pid_cart";

        $row = (SQL::get())->send($query, ["pid_cart" => $idCart])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Retorna os pedidos a partir do ID do usuário fornecido
     *
     * @param int $idUser ID do usuário
     *
     * @static
     *
     * @return array[self]
     */
    public static function listFromUserId(int $idUser): array
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_orders
                   WHERE id_user = :pid_user";

        return array_map(
            fn (object $row): self => self::_translate($row),
            (SQL::get())->send($query, ["pid_user" => $idUser])->fetchAll()
        );
    }


    /**
     * Retorna os pedidos a partir do ID do status fornecido
     *
     * @param int $idStatus ID do status
     *
     * @static
     *
     * @return array[self]
     */
    public static function listFromStatusId(int $idStatus): array
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_orders
                   WHERE id_status = :pid_status";

        return array_map(
            fn (object $row): self => self::_translate($row),
            (SQL::get())->send($query, ["pid_status" => $idStatus])->fetchAll()
        );
    }


    /**
     * Retorna os pedidos a partir do ID do endereço fornecido
     *
     * @param int $idAddress ID do endereço
     *
     * @static
     *
     * @return array[self]
     */
    public static function listFromAddressId(int $idAddress): array
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_orders
                   WHERE id_address = :pid_address";

        return array_map(
            fn (object $row): self => self::_translate($row),
            (SQL::get())->send($query, ["pid_address" => $idAddress])->fetchAll()
        );
    }


    /**
     * Retorna o boleto bancário do pedido
     *
     * @static
     *
     * @return string
     */
    public function getBankPaymentSlip(): string
    {
        $drawee = new Agente(
            $this->user->name,
            $this->_cpf((string) $this->user->cpf),
            $this->address->streetType?->name . " " . $this->address->publicPlace . ", " . $this->address->number,
            $this->_zipCode($this->address->zipCode),
            $this->address->city->name,
            $this->address->city->state->uf
        );

        $assignor = new Agente(
            getenv("ENTERPRISE_NAME") ?? "",
            $this->_cnpj(getenv("ENTERPRISE_CNPJ") ?? ""),
            getenv("ENTERPRISE_ADDRESS") ?? "",
            $this->_zipCode(getenv("ENTERPRISE_ZIP_CODE") ?? ""),
            getenv("ENTERPRISE_CITY") ?? "",
            getenv("ENTERPRISE_FU") ?? ""
        );

        $data = [
            "dataVencimento" => (new DateTime($this->dateRegister))->modify("+" . getenv("BANK_EXPIRATION_DAYS") . " days"),
            "valor" => $this->totalValue,
            "sequencial" => getenv("BANK_SEQUENTIAL"),
            "sacado" => $drawee,
            "cedente" => $assignor,
            "agencia" => getenv("BANK_AGENCY"),
            "carteira" => getenv("BANK_PORTFOLIO"),
            "conta" => getenv("BANK_ACCOUNT"),
            "convenio" => getenv("BANK_AGREEMENT"),
            "logoPath" => getenv("ENTERPRISE_LOGO"),
            "descricaoDemonstrativo" => array_map(
                fn (Product $product): string => $product->quantity . " x " . $product->name . ": R$ " . $product->totalPrice,
                $this->cart->products
            )
        ];

        $bank = match (Bank::tryFrom(getenv("BANK_NAME"))) {
            Bank::BRB => new Brb($data),
            Bank::BB => new BancoDoBrasil($data),
            Bank::BRADESCO => new Bradesco($data),
            Bank::ITAU => new Itau($data),
            Bank::SANTANDER => new Santander($data),
            Bank::UNICRED => new Unicred($data),
            default => new BancoDoBrasil($data)
        };

        return $bank->getOutput();
    }


    /**
     * Verifica se o a data de pagamento expirou
     *
     * @return bool
     */
    public function expired(): bool
    {
        return (new DateTime()) > (new DateTime($this->dateRegister))->modify("+" . getenv("BANK_EXPIRATION_DAYS") . " days");
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

        $this->idCart < 0 && array_push($errors, "carrinho inválido");

        (int) Cart::loadFromId($this->idCart)?->id <= 0 &&
        array_push($errors, "carrinho inexistente");

        $this->id <= 0 && ($id = (int) self::loadFromCartId($this->idCart)?->id) &&
        array_push($errors, "carrinho já efetivado");

        ($id = (int) self::loadFromCartId($this->idCart)?->id) &&
        $id !== $this->id &&
        array_push($errors, "carrinho pertence a outro pedido");

        $this->idUser < 0 && array_push($errors, "usuário inválido");

        (int) User::loadFromId($this->idUser)?->id <= 0 &&
        array_push($errors, "usuário inexistente");

        User::loadFromId($this->idUser)?->cpf === null &&
        array_push($errors, "CPF do usuário");

        $this->idStatus < 0 && array_push($errors, "status inválido");

        (int) OrderStatus::loadFromId($this->idStatus)?->id <= 0 &&
        array_push($errors, "status inexistente");

        $this->idAddress < 0 && array_push($errors, "endereço inválido");

        (int) Address::loadFromId($this->idAddress)?->id <= 0 &&
        array_push($errors, "endereço inexistente");

        $this->totalValue <= 0.0 && array_push($errors, "valor total inválido");

        !empty($this->code) && strlen($this->code) !== 32 && array_push($errors, "código inválido");

        ($id = (int) self::loadFromCode((string) $this->code)?->id) &&
        $id !== $this->id &&
        array_push($errors, "código já cadastrado");

        !empty($this->annotation) && strlen($this->annotation) < 8 &&
        array_push($errors, "anotação possui tamanho inválido");

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
            "idCart" => $this->idCart,
            "cart" => $this->cart->array(),
            "idUser" => $this->idUser,
            "user" => $this->user->array(),
            "idStatus" => $this->idStatus,
            "status" => $this->status->array(),
            "idAddress" => $this->idAddress,
            "address" => $this->address->array(),
            "totalValue" => (float) $this->totalValue,
            "code" => $this->code,
            "annotation" => $this->annotation,
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
