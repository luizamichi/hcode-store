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
 * Classe que modela a entidade PRODUTO
 *
 * @category Model
 * @package  Amichi/Model
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class Product extends Model implements JsonSerializable
{
    /**
     * Propriedade
     *
     * @var array $_columns Colunas de mapeamento objeto relacional
     */
    private static array $_columns = [
        "id" => "id_product", // ID do produto
        "name" => "des_product", // Nome do produto
        "description" => "des_description", // Descrição do produto
        "image" => "bin_image", // Imagem do produto
        "price" => "vl_price", // Preço do produto
        "width" => "vl_width", // Largura do produto
        "height" => "vl_height", // Altura do produto
        "length" => "vl_length", // Tamanho do produto
        "weight" => "vl_weight", // Peso do produto
        "stockQuantity" => "num_quantity_stock", // Quantidade do produto em estoque
        "isNational" => "is_national", // Produto é nacional?
        "slug" => "des_slug", // Slug do produto
        "dateRegister" => "dt_product_created_at", // Data de cadastro do produto
        "dateLastChange" => "dt_product_changed_in" // Data da última alteração do produto
    ];


    /**
     * Construtor
     *
     * @param int $id ID do produto
     *
     * @return void
     */
    public function __construct(int $id = 0)
    {
        $id > 0 && ($product = self::loadFromId($id)) && self::loadFromData($product->array(), $this);
    }


    /**
     * Traduz os valores da tabela para o objeto
     *
     * @param object $row     Linha da tabela de produtos
     * @param ?self  $product Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    public static function translate(object $row, ?self $product = null): self
    {
        $product ??= new self();

        foreach (self::$_columns as $key => $value) {
            $product->$key = $row->$value;
        }

        $product->quantity = $product->stockQuantity;
        $product->totalPrice = $product->price;

        parent::cache($product->id, $product);
        return $product;
    }


    /**
     * Obtém o nome dos campos da tabela de produtos
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
        $query = "CALL sp_save_product (:pid_product, :pdes_product, :pdes_description, :pbin_image, :pvl_price,
                                        :pvl_width, :pvl_height, :pvl_length, :pvl_weight, :pnum_quantity_stock,
                                        :pis_national, :pdes_slug)";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_product", $this->id, \PDO::PARAM_INT);
        $stmt->bindValue("pdes_product", $this->name, \PDO::PARAM_STR);
        $stmt->bindValue("pdes_description", $this->description, \PDO::PARAM_STR);
        $stmt->bindValue("pbin_image", $this->image, \PDO::PARAM_LOB);
        $stmt->bindValue("pvl_price", $this->price, \PDO::PARAM_STR);
        $stmt->bindValue("pvl_width", $this->width, \PDO::PARAM_STR);
        $stmt->bindValue("pvl_height", $this->height, \PDO::PARAM_STR);
        $stmt->bindValue("pvl_length", $this->length, \PDO::PARAM_STR);
        $stmt->bindValue("pvl_weight", $this->weight, \PDO::PARAM_STR);
        $stmt->bindValue("pnum_quantity_stock", $this->stockQuantity, \PDO::PARAM_INT);
        $stmt->bindValue("pis_national", $this->isNational, \PDO::PARAM_BOOL);
        $stmt->bindValue("pdes_slug", $this->slug, \PDO::PARAM_STR);
        $stmt->execute();

        self::translate($stmt->fetch(), $this);
        return $this;
    }


    /**
     * Remove o objeto no banco de dados
     *
     * @return self
     */
    public function delete(): self
    {
        $query = "DELETE FROM tb_products WHERE id_product = :pid_product";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_product", $this->id, \PDO::PARAM_INT);
        $stmt->execute();

        return $this;
    }


    /**
     * Retorna todos os registros da tabela de produtos
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
        $query = "SELECT $fields FROM tb_products" .
            (in_array($sortBy, array_keys(self::$_columns))
                ? " ORDER BY " . self::$_columns[$sortBy]
                : "") .
            ($limit > 0 ? " LIMIT $limit" : "") .
            ($offset > 0 ? " OFFSET $offset" : "");

        return array_map(
            fn (object $row): self => self::translate($row),
            (SQL::get())->send($query)->fetchAll()
        );
    }


    /**
     * Retorna o produto a partir do ID fornecido
     *
     * @param int $id ID do produto
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromId(int $id): ?self
    {
        if ($product = parent::cache($id, __CLASS__)) {
            return $product;
        }

        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_products
                   WHERE id_product = :pid_product";

        $row = (SQL::get())->send($query, ["pid_product" => $id])->fetch();
        return $row ? self::translate($row) : null;
    }


    /**
     * Retorna o produto a partir do slug fornecido
     *
     * @param string $slug Slug do produto
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromSlug(string $slug): ?self
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_products
                   WHERE des_slug = :pdes_slug";

        $row = (SQL::get())->send($query, ["pdes_slug" => $slug])->fetch();
        return $row ? self::translate($row) : null;
    }


    /**
     * Retorna a quantidade de produtos cadastrados no banco de dados
     *
     * @static
     *
     * @return int
     */
    public static function count(): int
    {
        $query = "SELECT COUNT(*) quantity
                    FROM tb_products";

        $row = (SQL::get())->send($query)->fetch();
        return $row->quantity;
    }


    /**
     * Instancia a classe a partir de um vetor de argumentos
     *
     * @param array $arguments Vetor com os dados do produto
     * @param ?self $product   Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    public static function loadFromData(array $arguments, ?self $product = null): self
    {
        $product ??= new self();

        $product->id = (int) ($arguments["id"] ?? 0);
        $product->name = trim($arguments["name"] ?? "");
        $product->description = trim($arguments["description"] ?? "") ?: null;
        $product->image = $arguments["image"] ?? null;
        $product->price = (float) ($arguments["price"] ?? 0.0);
        $product->width = (float) ($arguments["width"] ?? 0.0);
        $product->height = (float) ($arguments["height"] ?? 0.0);
        $product->length = (float) ($arguments["length"] ?? 0.0);
        $product->weight = (float) ($arguments["weight"] ?? 0.0);
        $product->stockQuantity = (int) ($arguments["stockQuantity"] ?? 0);
        $product->isNational = (bool) in_array(($arguments["isNational"] ?? false), ["true", 1]);
        $product->slug = trim(strtolower($arguments["slug"] ?? ""));
        $product->dateRegister = $arguments["dateRegister"] ?? date("Y-m-d H:i:s");
        $product->dateLastChange = $arguments["dateLastChange"] ?? null;
        $product->quantity = $product->stockQuantity;
        $product->totalPrice = $product->price;

        return $product;
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

        (strlen($this->name) < 4 || strlen($this->name) > 64) &&
        array_push($errors, "nome possui tamanho inválido");

        $this->description !== null && (strlen($this->description) < 16 || strlen($this->description) > 65535) &&
        array_push($errors, "descrição possui tamanho inválido");

        $this->price <= 0.0 && array_push($errors, "preço inválido");

        $this->width <= 0.0 && array_push($errors, "largura inválida");

        $this->height <= 0.0 && array_push($errors, "altura inválida");

        $this->length <= 0.0 && array_push($errors, "comprimento inválido");

        $this->weight <= 0.0 && array_push($errors, "peso inválido");

        $this->stockQuantity < 0 && array_push($errors, "quantidade em estoque inválida");

        (strlen($this->slug) < 2 || strlen($this->slug) > 256) &&
        array_push($errors, "slug possui tamanho inválido");

        !preg_match("/^[a-zA-Z0-9\-]+$/", $this->slug) &&
        array_push($errors, "slug possui caracteres inválidos");

        ($id = (int) self::loadFromSlug($this->slug)?->id) &&
        $id !== $this->id &&
        array_push($errors, "slug já cadastrado");

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
            "description" => $this->description,
            "image" => $this->image ? base64_encode($this->image) : $this->image,
            "price" => (float) $this->price,
            "width" => (float) $this->width,
            "height" => (float) $this->height,
            "length" => (float) $this->length,
            "weight" => (float) $this->weight,
            "stockQuantity" => $this->stockQuantity,
            "isNational" => (bool) $this->isNational,
            "slug" => $this->slug,
            "dateRegister" => $this->dateRegister,
            "dateLastChange" => $this->dateLastChange,
            "quantity" => $this->quantity,
            "totalPrice" => $this->totalPrice ? (float) $this->totalPrice : $this->totalPrice
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
