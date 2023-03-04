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
use Amichi\Trait\Formatter;
use Amichi\Trait\Validator;
use JsonSerializable;

/**
 * Classe que modela a entidade CARRINHO
 *
 * @category Model
 * @package  Amichi/Model
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class Cart extends Model implements JsonSerializable
{
    use Formatter;
    use Validator;


    /**
     * Propriedades
     *
     * @var string SESSION   Nome da sessão para armazenar os dados do carrinho
     * @var array  $_columns Colunas de mapeamento objeto relacional
     */
    public const SESSION = "Cart";
    private static array $_columns = [
        "id" => "id_cart", // ID do carrinho
        "sessionId" => "des_session_id", // ID da sessão PHP do carrinho
        "idUser" => "id_user", // ID do usuário do carrinho
        "idAddress" => "id_address", // ID do endereço do carrinho
        "temporaryZipCode" => "num_temporary_zip_code", // Número do CEP temporário do endereço de entrega do carrinho
        "freightValue" => "vl_freight", // Valor do frete do carrinho
        "freightType" => "des_type_freight", // Tipo do frete do carrinho
        "days" => "num_days", // Quantidade de dias para entrega dos produtos do carrinho
        "dateRegister" => "dt_cart_created_at" // Data de cadastro do carrinho
    ];


    /**
     * Construtor
     *
     * @param int $id ID do carrinho
     *
     * @return void
     */
    public function __construct(int $id = 0)
    {
        $id > 0 && ($cart = self::loadFromId($id)) && self::loadFromData($cart->array(), $this);
    }


    /**
     * Traduz os valores da tabela para o objeto
     *
     * @param object $row  Linha da tabela de carrinhos
     * @param ?self  $cart Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    private static function _translate(object $row, ?self $cart = null): self
    {
        $cart ??= new self();

        foreach (self::$_columns as $key => $value) {
            $cart->$key = $row->$value;
        }

        $cart->user = $row->id_user ? new User($row->id_user) : null;
        $cart->address = $row->id_address ? new Address($row->id_address) : null;
        $cart->_getProducts();
        $cart->_getTotalValue();

        parent::cache($cart->id, $cart);
        return $cart;
    }


    /**
     * Obtém o nome dos campos da tabela de carrinhos
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
     * Salva o carrinho na sessão do PHP
     *
     * @return self
     */
    public function saveInSession(): self
    {
        $_SESSION[self::SESSION] = $this->array();
        return $this;
    }


    /**
     * Limpa o carrinho salvo na sessão do PHP
     *
     * @static
     *
     * @return ?self
     */
    public static function clearSession(): ?self
    {
        $array = $_SESSION[self::SESSION] ?? [];
        $_SESSION[self::SESSION] = null;
        session_regenerate_id(); // Renova o ID da sessão para criar um novo carrinho (posteriormente)

        return $array ? self::loadFromData($array) : null;
    }


    /**
     * Salva o objeto no banco de dados
     *
     * @return self
     */
    public function save(): self
    {
        $query = "CALL sp_save_cart (:pid_cart, :pdes_session_id, :pid_user, :pid_address, :pnum_temporary_zip_code,
                                     :pvl_freight, :pdes_type_freight, :pnum_days)";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_cart", $this->id, \PDO::PARAM_INT);
        $stmt->bindValue("pdes_session_id", $this->sessionId, \PDO::PARAM_STR);
        $stmt->bindValue("pid_user", $this->idUser, \PDO::PARAM_INT);
        $stmt->bindValue("pid_address", $this->idAddress, \PDO::PARAM_INT);
        $stmt->bindValue("pnum_temporary_zip_code", $this->temporaryZipCode, \PDO::PARAM_INT);
        $stmt->bindValue("pvl_freight", $this->freightValue, \PDO::PARAM_STR);
        $stmt->bindValue("pdes_type_freight", $this->freightType, \PDO::PARAM_STR);
        $stmt->bindValue("pnum_days", $this->days, \PDO::PARAM_INT);
        $stmt->execute();

        $cart = $stmt->fetch();
        $stmt->closeCursor();

        self::_translate($cart, $this);
        return $this;
    }


    /**
     * Retorna todos os produtos que estão no carrinho
     *
     * @param bool $related Relacionado ou não (adicionado/removido)
     *
     * @return array[Product]
     */
    public function getProducts(bool $related = true): array
    {
        if ($related) {
            $query = "SELECT p.*
                        FROM tb_products p
                       INNER JOIN tb_carts_products pc ON p.id_product = pc.id_product
                       WHERE pc.id_cart = :pid_cart
                         AND pc.dt_removed IS NULL";
        } else {
            $query = "SELECT p.*
                        FROM tb_products p
                       INNER JOIN tb_carts_products pc ON p.id_product = pc.id_product
                       WHERE pc.id_cart = :pid_cart
                         AND pc.dt_removed IS NOT NULL";
        }

        return array_map(
            fn (object $row): Product => Product::translate($row),
            (SQL::get())->send($query, ["pid_cart" => $this->id])->fetchAll()
        );
    }


    /**
     * Verifica se o produto consta no carrinho
     *
     * @param int  $idProduct ID do produto
     * @param bool $related   Relacionado ou não (adicionado/removido)
     *
     * @return self
     */
    public function containsProduct(int $idProduct, bool $related = true): bool
    {
        $query = "SELECT COUNT(*) quantity
                    FROM tb_carts_products
                   WHERE id_cart = :pid_cart
                     AND id_product = :pid_product
                     AND dt_removed IS " . ($related ? "NULL" : "NOT NULL");

        $row = (SQL::get())->send(
            $query,
            [
                "pid_cart" => $this->id,
                "pid_product" => $idProduct
            ]
        )->fetch();

        return $row->quantity > 0;
    }


    /**
     * Adiciona o produto ao carrinho
     *
     * @param int $idProduct ID do produto
     *
     * @return self
     */
    public function postProduct(int $idProduct): self
    {
        $query = "INSERT INTO tb_carts_products (id_cart, id_product, vl_unit_price) VALUES (:pid_cart, :pid_product, :pvl_unit_price)";

        $product = Product::loadFromId($idProduct);

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_cart", $this->id, \PDO::PARAM_INT);
        $stmt->bindValue("pid_product", $idProduct, \PDO::PARAM_INT);
        $stmt->bindValue("pvl_unit_price", $product?->price, \PDO::PARAM_STR);
        $stmt->execute();

        return $this->_calculateFreight();
    }


    /**
     * Remove o objeto no banco de dados
     *
     * @return self
     */
    public function delete(): self
    {
        $query = "DELETE FROM tb_carts WHERE id_cart = :pid_cart";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_cart", $this->id, \PDO::PARAM_INT);
        $stmt->execute();

        return $this;
    }


    /**
     * Remove o produto do carrinho
     *
     * @param int  $idProduct ID do produto
     * @param bool $all       Todos os produtos
     *
     * @return self
     */
    public function deleteProduct(int $idProduct, bool $all = false): self
    {
        if ($all) {
            $query = "UPDATE tb_carts_products SET dt_removed = NOW() WHERE id_cart = :pid_cart AND id_product = :pid_product AND dt_removed IS NULL";
        } else {
            $query = "UPDATE tb_carts_products SET dt_removed = NOW() WHERE id_cart = :pid_cart AND id_product = :pid_product AND dt_removed IS NULL LIMIT 1";
        }

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_cart", $this->id, \PDO::PARAM_INT);
        $stmt->bindValue("pid_product", $idProduct, \PDO::PARAM_INT);
        $stmt->execute();

        return $this->_calculateFreight();
    }


    /**
     * Retorna todos os registros da tabela de carrinhos
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
        $query = "SELECT $fields FROM tb_carts" .
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
     * Retorna o carrinho a partir do ID fornecido
     *
     * @param int $id ID do carrinho
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromId(int $id): ?self
    {
        if ($cart = parent::cache($id, __CLASS__)) {
            return $cart;
        }

        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_carts
                   WHERE id_cart = :pid_cart";

        $row = (SQL::get())->send($query, ["pid_cart" => $id])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Instancia a classe a partir de um vetor de argumentos
     *
     * @param array $arguments Vetor com os dados do carrinho
     * @param ?self $cart      Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    public static function loadFromData(array $arguments, ?self $cart = null): self
    {
        $cart ??= new self();

        $cart->id = (int) ($arguments["id"] ?? 0);
        $cart->sessionId = trim($arguments["sessionId"] ?? "");
        $cart->idUser = (int) ($arguments["idUser"] ?? 0) ?: null;
        $cart->user = ($arguments["user"] ?? []) ? User::loadFromData($arguments["user"]) : User::loadFromId((int) $cart->idUser);
        $cart->idAddress = (int) ($arguments["idAddress"] ?? 0) ?: null;
        $cart->address = ($arguments["address"] ?? []) ? Address::loadFromData($arguments["address"]) : Address::loadFromId((int) $cart->idAddress);
        $cart->temporaryZipCode = (int) (preg_replace("/\D/", "", $arguments["temporaryZipCode"] ?? "")) ?: null;
        $cart->freightValue = (float) ($arguments["freightValue"] ?? 0.0) ?: null;
        $cart->freightType = trim($arguments["freightType"] ?? "") ?: null;
        $cart->days = (int) ($arguments["days"] ?? 0) ?: null;
        $cart->dateRegister = $arguments["dateRegister"] ?? date("Y-m-d H:i:s");

        return $cart;
    }


    /**
     * Instancia a classe a partir da sessão
     *
     * @static
     *
     * @return self
     */
    public static function loadFromSession(): self
    {
        $cart = self::loadFromData($_SESSION[self::SESSION] ?? []);
        if ($cart->id > 0) {
            return $cart;
        }

        $cart = self::loadFromSessionId();
        if ($cart) {
            return $cart;
        }

        $cart = self::loadFromData([]);
        $cart->sessionId = session_id();

        $user = User::loadFromSession();
        if ($user) {
            $cart->idUser = $user->id;
        }

        return $cart->save();
    }


    /**
     * Retorna os carrinhos a partir do ID do usuário fornecido
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
                    FROM tb_carts
                   WHERE id_user = :pid_user";

        return array_map(
            fn (object $row): self => self::_translate($row),
            (SQL::get())->send($query, ["pid_user" => $idUser])->fetchAll()
        );
    }


    /**
     * Instancia a classe a partir do ID da sessão
     *
     * @param ?string $sessionId ID da sessão
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromSessionId(?string $sessionId = null): ?self
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_carts
                   WHERE des_session_id = :pdes_session_id";

        $row = (SQL::get())->send($query, ["pdes_session_id" => $sessionId ?? session_id()])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Carrega todos os produtos que estão inseridos no carrinho
     *
     * @return void
     */
    private function _getProducts(): void
    {
        $query = "SELECT id_cart, vl_unit_price, dt_removed,
                         id_product, des_product, des_description, bin_image, vl_price, vl_width,
                         vl_height, vl_length, vl_weight, num_quantity_stock, is_national, des_slug,
                         dt_product_created_at, dt_product_changed_in, COUNT(*) num_quantity, SUM(vl_price) vl_total
                    FROM tb_carts_products
                   INNER JOIN tb_products USING (id_product)
                   WHERE id_cart = :pid_cart
                     AND dt_removed IS NULL
                   GROUP BY id_cart, vl_unit_price, dt_removed,
                         id_product, des_product, des_description, bin_image, vl_price, vl_width,
                         vl_height, vl_length, vl_weight, num_quantity_stock, is_national, des_slug,
                         dt_product_created_at, dt_product_changed_in
                   ORDER BY des_product DESC";

        $this->products = array_map(
            function (object $row): Product {
                $product = Product::translate($row);
                $product->price = $row->vl_unit_price;
                $product->quantity = $row->num_quantity;
                $product->totalPrice = $row->vl_total;
                return $product;
            },
            (SQL::get())->send($query, ["pid_cart" => $this->id])->fetchAll()
        );

        $this->totalPrice = array_reduce($this->products, fn (int $agg, Product $product): float => $product->totalPrice + $agg, 0) + $this->freightValue;
    }


    /**
     * Calcula o valor total do carrinho
     *
     * @return void
     */
    private function _getTotalValue(): void
    {
        $query = "SELECT SUM(vl_price) vl_price, SUM(vl_width) vl_width, SUM(vl_height) vl_height,
                         SUM(vl_length) vl_length, SUM(vl_weight) vl_weight, COUNT(*) num_quantity
                    FROM tb_carts_products
                   INNER JOIN tb_products USING (id_product)
                   WHERE id_cart = :pid_cart
                     AND dt_removed IS NULL";

        $row = (SQL::get())->send($query, ["pid_cart" => $this->id])->fetch();

        $this->package = (object) [
            "price" => $row->vl_price,
            "width" => $row->vl_width,
            "height" => $row->vl_height,
            "length" => $row->vl_length,
            "weight" => $row->vl_weight,
            "quantity" => $row->num_quantity
        ];
    }


    /**
     * Calcula o valor de envio dos produtos do carrinho
     *
     * @return self
     */
    private function _calculateFreight(): self
    {
        if ($this->_validateZipCode((string) $this->temporaryZipCode) && $this->package->quantity > 0) {
            $queryString = http_build_query(
                [
                    "nCdEmpresa" => getenv("COURIER_COMPANY_CODE"),
                    "sDsSenha" => getenv("COURIER_PASSWORD"),
                    "nCdServico" => getenv("COURIER_SERVICE_CODE"),
                    "sCepOrigem" => getenv("COURIER_ORIGIN_ZIP_CODE"),
                    "sCepDestino" => $this->temporaryZipCode,
                    "nVlPeso" => $this->package->weight,
                    "nCdFormato" => getenv("COURIER_ORDER_FORMAT"),
                    "nVlComprimento" => $this->package->length * 100,
                    "nVlAltura" => $this->package->height * 100,
                    "nVlLargura" => $this->package->width * 100,
                    "nVlDiametro" => 0,
                    "sCdMaoPropria" => getenv("COURIER_OWN_HAND"),
                    "nVlValorDeclarado" => $this->package->price,
                    "sCdAvisoRecebimento" => getenv("COURIER_ORDER_RECEIPT_NOTICE")
                ]
            );

            $xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?$queryString");
            $result = $xml->Servicos->cServico;

            if (empty($result->MsgErro)) {
                $this->days = $result->PrazoEntrega;
                $this->freightValue = $this->_decimalToFloat($result->Valor);
                $this->freightType = match ((int) getenv("COURIER_ORDER_FORMAT")) {
                    1 => "Formato caixa/pacote",
                    2 => "Formato rolo/prisma",
                    3 => "Envelope"
                };

                $this->save();
            }
        }

        return $this;
    }


    /**
     * Recalcula o frete e o valor total do carrinho.
     * Recarrega os produtos do carrinho.
     *
     * @return self
     */
    public function refresh(): self
    {
        $this->_getProducts();
        $this->_getTotalValue();
        return $this->_calculateFreight();
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

        $this->sessionId === "" && array_push($errors, "ID da sessão inválido");

        $this->idUser < 0 && array_push($errors, "usuário inválido");

        $this->idUser !== null && (int) User::loadFromId($this->idUser)?->id <= 0 &&
        array_push($errors, "usuário inexistente");

        $this->idAddress < 0 && array_push($errors, "endereço inválido");

        $this->idAddress !== null && (int) Address::loadFromId($this->idAddress)?->id <= 0 &&
        array_push($errors, "endereço inexistente");

        $this->idUser !== null && $this->idAddress !== null &&
        (int) Address::loadFromUserId($this->idUser)?->id !== $this->idAddress &&
        array_push($errors, "endereço não pertence ao usuário");

        !empty($this->temporaryZipCode) && !$this->_validateZipCode($this->temporaryZipCode) &&
        array_push($errors, "CEP inválido");

        $this->freightValue < 0.0 && array_push($errors, "valor do frete inválido");

        $this->days < 0 && array_push($errors, "prazo inválido");


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
            "sessionId" => $this->sessionId,
            "idUser" => $this->idUser,
            "user" => $this->user?->array(),
            "idAddress" => $this->idAddress,
            "address" => $this->address?->array(),
            "temporaryZipCode" => $this->temporaryZipCode,
            "freightValue" => $this->freightValue ? (float) $this->freightValue : $this->freightValue,
            "freightType" => $this->freightType,
            "days" => $this->days,
            "dateRegister" => $this->dateRegister,
            "package" => (array) $this->package,
            "products" => array_map(fn (Product $product): array => $product->array(), $this->products),
            "totalPrice" => $this->totalPrice
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
