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
 * Classe que modela a entidade CATEGORIA
 *
 * @category Model
 * @package  Amichi/Model
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class Category extends Model implements JsonSerializable
{
    /**
     * Propriedade
     *
     * @var array<string,string> $_columns Colunas de mapeamento objeto relacional
     */
    private static array $_columns = [
        "id" => "id_category", // ID da categoria
        "name" => "des_category", // Nome da categoria
        "slug" => "des_nickname", // Slug da categoria
        "idSuper" => "fk_category", // ID da categoria mãe da categoria
        "dateRegister" => "dt_category_created_at", // Data de cadastro da categoria
        "dateLastChange" => "dt_category_changed_in" // Data da última alteração da categoria
    ];


    /**
     * Construtor
     *
     * @param int $id ID da categoria
     *
     * @return void
     */
    public function __construct(int $id = 0)
    {
        $id > 0 && ($category = self::loadFromId($id)) && self::loadFromData($category->array(), $this);
    }


    /**
     * Traduz os valores da tabela para o objeto
     *
     * @param object $row      Linha da tabela de categorias
     * @param ?self  $category Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    private static function _translate(object $row, ?self $category = null): self
    {
        $category ??= new self();

        foreach (self::$_columns as $key => $value) {
            $category->$key = $row->$value;
        }

        $category->super = $category->idSuper ? new self($row->fk_category) : null;

        parent::cache($category->id, $category);
        return $category;
    }


    /**
     * Obtém o nome dos campos da tabela de categorias
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
        $query = "CALL sp_save_category (:pid_category, :pdes_category, :pdes_nickname, :pfk_category)";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_category", $this->id, \PDO::PARAM_INT);
        $stmt->bindValue("pdes_category", $this->name, \PDO::PARAM_STR);
        $stmt->bindValue("pdes_nickname", $this->slug, \PDO::PARAM_STR);
        $stmt->bindValue("pfk_category", $this->idSuper, \PDO::PARAM_INT);
        $stmt->execute();

        $category = $stmt->fetch();
        $stmt->closeCursor();

        self::_translate($category, $this);
        return $this;
    }


    /**
     * Salva o relacionamento do produto com a categoria
     *
     * @param int $idProduct ID do produto
     *
     * @return self
     */
    public function postProduct(int $idProduct): self
    {
        $query = "INSERT INTO tb_products_categories (id_product, id_category) VALUES (:pid_product, :pid_category)";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_product", $idProduct, \PDO::PARAM_INT);
        $stmt->bindValue("pid_category", $this->id, \PDO::PARAM_INT);
        $stmt->execute();

        return $this;
    }


    /**
     * Remove o objeto no banco de dados
     *
     * @return self
     */
    public function delete(): self
    {
        $query = "DELETE FROM tb_categories WHERE id_category = :pid_category";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_category", $this->id, \PDO::PARAM_INT);
        $stmt->execute();

        return $this;
    }


    /**
     * Remove o relacionamento do produto com a categoria
     *
     * @param int $idProduct ID do produto
     *
     * @return self
     */
    public function deleteProduct(int $idProduct): self
    {
        $query = "DELETE FROM tb_products_categories WHERE id_product = :pid_product AND id_category = :pid_category";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_product", $idProduct, \PDO::PARAM_INT);
        $stmt->bindValue("pid_category", $this->id, \PDO::PARAM_INT);
        $stmt->execute();

        return $this;
    }


    /**
     * Retorna todos os registros da tabela de categorias
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
        $query = "SELECT $fields FROM tb_categories" .
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
     * Retorna a categoria a partir do ID da categoria mãe fornecido
     *
     * @param int $idSuper ID da categoria mãe
     *
     * @static
     *
     * @return array<self>
     */
    public static function listFromSuperId(int $idSuper): array
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_categories
                   WHERE fk_category = :pfk_category";

        return array_map(
            fn (object $row): self => self::_translate($row),
            (SQL::get())->send($query, ["pfk_category" => $idSuper])->fetchAll()
        );
    }


    /**
     * Retorna a categoria a partir do ID fornecido
     *
     * @param int $id ID da categoria
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromId(int $id): ?self
    {
        if ($category = parent::cache($id, __CLASS__)) {
            return $category;
        }

        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_categories
                   WHERE id_category = :pid_category";

        $row = (SQL::get())->send($query, ["pid_category" => $id])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Retorna a categoria a partir do slug fornecido
     *
     * @param string $slug Slug da categoria
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromSlug(string $slug): ?self
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_categories
                   WHERE des_nickname = :pdes_nickname";

        $row = (SQL::get())->send($query, ["pdes_nickname" => $slug])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Retorna a categoria a partir do nome fornecido
     *
     * @param string $name Nome da categoria
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromName(string $name): ?self
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_categories
                   WHERE des_category = :pdes_category";

        $row = (SQL::get())->send($query, ["pdes_category" => $name])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Retorna a quantidade de categorias cadastradas no banco de dados
     *
     * @static
     *
     * @return int
     */
    public static function count(): int
    {
        $query = "SELECT COUNT(*) quantity
                    FROM tb_categories";

        $row = (SQL::get())->send($query)->fetch();
        return $row->quantity;
    }


    /**
     * Instancia a classe a partir de um vetor de argumentos
     *
     * @param array<mixed> $arguments Vetor com os dados da categoria
     * @param ?self        $category  Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    public static function loadFromData(array $arguments, ?self $category = null): self
    {
        $category ??= new self();

        $category->id = (int) ($arguments["id"] ?? 0);
        $category->name = trim($arguments["name"] ?? "");
        $category->slug = trim(strtolower($arguments["slug"] ?? ""));
        $category->idSuper = (int) ($arguments["idSuper"] ?? 0) ?: null;
        $category->super = ($arguments["super"] ?? []) ? self::loadFromData($arguments["super"]) : self::loadFromId((int) $category->idSuper);
        $category->dateRegister = $arguments["dateRegister"] ?? date("Y-m-d H:i:s");
        $category->dateLastChange = $arguments["dateLastChange"] ?? null;

        return $category;
    }


    /**
     * Retorna todos os produtos que estão ou não relacionados à categoria
     *
     * @param bool $related Relacionado ou não
     *
     * @return array<Product>
     */
    public function getProducts(bool $related = true): array
    {
        if ($related) {
            $query = "SELECT p.*
                        FROM tb_products p
                       INNER JOIN tb_products_categories pc ON p.id_product = pc.id_product
                       WHERE pc.id_category = :pid_category";
        } else {
            $query = "SELECT distinct p.*
                        FROM tb_products p
                        LEFT JOIN tb_products_categories pc ON p.id_product = pc.id_product
                       WHERE pc.id_category IS NULL
                          OR pc.id_category <> :pid_category
                         AND p.id_product NOT IN (SELECT c.id_product
                                                    FROM tb_products_categories c
                                                   WHERE c.id_category = :pid_category)";
        }

        return array_map(
            fn (object $row): Product => Product::translate($row),
            (SQL::get())->send($query, ["pid_category" => $this->id])->fetchAll()
        );
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

        (strlen($this->slug) < 2 || strlen($this->slug) > 64) &&
        array_push($errors, "slug possui tamanho inválido");

        ($id = (int) self::loadFromSlug($this->slug)?->id) &&
        $id !== $this->id &&
        array_push($errors, "slug já cadastrado");

        !preg_match("/^[a-zA-Z0-9\-]+$/", $this->slug) &&
        array_push($errors, "slug possui caracteres inválidos");

        $this->idSuper !== null && (int) self::loadFromId($this->idSuper)?->id <= 0 &&
        array_push($errors, "categoria mãe inexistente");

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
            "slug" => $this->slug,
            "idSuper" => $this->idSuper,
            "super" => $this->super?->array(),
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
