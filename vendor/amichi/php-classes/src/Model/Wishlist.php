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
 * Classe que modela a entidade LISTA DE DESEJOS
 *
 * @category Model
 * @package  Amichi/Model
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class Wishlist extends Model implements JsonSerializable
{
    /**
     * Propriedade
     *
     * @var array<string,string> $_columns Colunas de mapeamento objeto relacional
     */
    private static array $_columns = [
        "idUser" => "id_user", // ID do usuário
        "idProduct" => "id_product", // ID do produto
        "dateRegister" => "dt_product_added_at" // Data de inserção do produto na lista de desejos
    ];


    /**
     * Traduz os valores da tabela para o objeto
     *
     * @param object $row      Linha da tabela de lista de desejos
     * @param ?self  $wishlist Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    private static function _translate(object $row, ?self $wishlist = null): self
    {
        $wishlist ??= new self();

        foreach (self::$_columns as $key => $value) {
            $wishlist->$key = $row->$value;
        }

        $wishlist->user = new User($row->id_user);
        $wishlist->product = new Product($row->id_product);

        return $wishlist;
    }


    /**
     * Obtém o nome dos campos da tabela de lista de desejos
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
    public function create(): self
    {
        $query = "INSERT INTO tb_wishlist (id_user, id_product) VALUES (:pid_user, :pid_product)";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_user", $this->idUser, \PDO::PARAM_INT);
        $stmt->bindValue("pid_product", $this->idProduct, \PDO::PARAM_INT);
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
        $query = "DELETE FROM tb_wishlist WHERE id_user = :pid_user AND id_product = :pid_product";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_user", $this->idUser, \PDO::PARAM_INT);
        $stmt->bindValue("pid_product", $this->idProduct, \PDO::PARAM_INT);
        $stmt->execute();

        return $this;
    }


    /**
     * Retorna todos os produtos que estão na lista de desejos do usuário
     *
     * @param int $idUser ID do usuário
     *
     * @return array<Product>
     */
    public static function getProducts(int $idUser): array
    {
        $query = "SELECT p.*
                    FROM tb_wishlist w
                   INNER JOIN tb_products p ON w.id_product = p.id_product
                   WHERE w.id_user = :pid_user";

        return array_map(
            fn (object $row): Product => Product::translate($row),
            (SQL::get())->send($query, ["pid_user" => $idUser])->fetchAll()
        );
    }


    /**
     * Retorna todos os registros da tabela de lista de desejos
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
        $query = "SELECT $fields FROM tb_wishlist" .
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
     * Retorna a lista de desejos a partir do ID do usuário e do produto fornecido
     *
     * @param int $idUser    ID do usuário
     * @param int $idProduct ID do produto
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromUserAndProductId(int $idUser, int $idProduct): ?self
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_wishlist
                   WHERE id_user = :pid_user
                     AND id_product = :pid_product";


        $row = (SQL::get())->send(
            $query,
            [
                "pid_user" => $idUser,
                "pid_product" => $idProduct
            ]
        )->fetch();

        return $row ? self::_translate($row) : null;
    }


    /**
     * Retorna a lista de desejos a partir do ID do usuário fornecido
     *
     * @param int $idUser ID do usuário
     *
     * @static
     *
     * @return array<self>
     */
    public static function listFromUserId(int $idUser): array
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_wishlist
                   WHERE id_user = :pid_user";

        return array_map(
            fn (object $row): self => self::_translate($row),
            (SQL::get())->send($query, ["pid_user" => $idUser])->fetchAll()
        );
    }


    /**
     * Retorna a lista de desejos a partir do ID do produto fornecido
     *
     * @param int $idProduct ID do produto
     *
     * @static
     *
     * @return array<self>
     */
    public static function listFromProductId(int $idProduct): array
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_wishlist
                   WHERE id_product = :pid_product";

        return array_map(
            fn (object $row): self => self::_translate($row),
            (SQL::get())->send($query, ["pid_product" => $idProduct])->fetchAll()
        );
    }


    /**
     * Instancia a classe a partir de um vetor de argumentos
     *
     * @param array<mixed> $arguments Vetor com os dados da lista de desejos
     * @param ?self        $wishlist  Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    public static function loadFromData(array $arguments, ?self $wishlist = null): self
    {
        $wishlist ??= new self();

        $wishlist->idUser = (int) ($arguments["idUser"] ?? 0);
        $wishlist->user = ($arguments["user"] ?? []) ? User::loadFromData($arguments["user"]) : User::loadFromId($wishlist->idUser);
        $wishlist->idProduct = (int) ($arguments["idProduct"] ?? 0);
        $wishlist->product = ($arguments["product"] ?? []) ? Product::loadFromData($arguments["product"]) : Product::loadFromId($wishlist->idProduct);
        $wishlist->dateRegister = $arguments["dateRegister"] ?? date("Y-m-d H:i:s");

        return $wishlist;
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
        $this->idUser < 0 && array_push($errors, "usuário inválido");

        (int) User::loadFromId($this->idUser)?->id <= 0 &&
        array_push($errors, "usuário inexistente");

        $this->idProduct < 0 && array_push($errors, "produto inválido");

        (int) Product::loadFromId($this->idProduct)?->id <= 0 &&
        array_push($errors, "produto inexistente");

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
            "idUser" => $this->idUser,
            "user" => $this->user->array(),
            "idProduct" => $this->idProduct,
            "product" => $this->product->array(),
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
