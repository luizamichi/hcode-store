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
use Amichi\Trait\Validator;
use JsonSerializable;

/**
 * Classe que modela a entidade USUÁRIO
 *
 * @category Model
 * @package  Amichi/Model
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class User extends Person implements JsonSerializable
{
    use Validator;


    /**
     * Propriedade
     *
     * @var string SESSION Nome da sessão para armazenar os dados do usuário logado
     */
    public const SESSION = "User";


    /**
     * Propriedade
     *
     * @var array<string,string> $_columns Colunas de mapeamento objeto relacional
     */
    private static array $_columns = [
        "id" => "id_user", // ID do usuário
        "idPerson" => "id_person", // ID da pessoa
        "login" => "des_login", // Login do usuário
        "password" => "des_password", // Senha do usuário
        "isAdmin" => "is_admin", // Usuário é administrador?
        "dateRegister" => "dt_user_created_at", // Data de cadastro do usuário
        "dateLastChange" => "dt_user_changed_in" // Data da última alteração do usuário
    ];


    /**
     * Construtor
     *
     * @param int $id ID do usuário
     *
     * @return void
     */
    public function __construct(int $id = 0)
    {
        $id > 0 && ($user = self::loadFromId($id)) && self::loadFromData($user->array(), $this);
    }


    /**
     * Traduz os valores da tabela para o objeto
     *
     * @param object $row  Linha da tabela de usuários
     * @param ?self  $user Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    private static function _translate(object $row, ?self $user = null): self
    {
        $user ??= new self();

        foreach (self::$_columns as $key => $value) {
            $user->$key = $row->$value;
        }

        $user->loadPersonId($user->idPerson);

        parent::cache($user->id, $user);
        return $user;
    }


    /**
     * Obtém o nome dos campos da tabela de usuários
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
     * Salva o usuário na sessão do PHP
     *
     * @return self
     */
    public function saveInSession(): self
    {
        $_SESSION[self::SESSION] = $this->array();
        return $this;
    }


    /**
     * Limpa o usuário salvo na sessão do PHP
     *
     * @static
     *
     * @return ?self
     */
    public static function clearSession(): ?self
    {
        $array = $_SESSION[self::SESSION] ?? [];
        $_SESSION[self::SESSION] = null;

        return $array ? self::loadFromData($array) : null;
    }


    /**
     * Cria um novo objeto no banco de dados
     *
     * @return self
     */
    public function create(): self
    {
        $this->password = password_hash($this->password, PASSWORD_DEFAULT, ["cost" => 12]);

        $query = "CALL sp_create_user (:pdes_login, :pdes_password, :pis_admin, :pdes_person,
                                       :pdes_email, :pdes_cpf, :pnum_phone, :pbin_photo)";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pdes_login", $this->login, \PDO::PARAM_STR);
        $stmt->bindValue("pdes_password", $this->password, \PDO::PARAM_STR);
        $stmt->bindValue("pis_admin", $this->isAdmin, \PDO::PARAM_BOOL);
        $stmt->bindValue("pdes_person", $this->name, \PDO::PARAM_STR);
        $stmt->bindValue("pdes_email", $this->email, \PDO::PARAM_STR);
        $stmt->bindValue("pdes_cpf", $this->cpf, \PDO::PARAM_STR);
        $stmt->bindValue("pnum_phone", $this->phone, \PDO::PARAM_INT);
        $stmt->bindValue("pbin_photo", $this->photo, \PDO::PARAM_LOB);
        $stmt->execute();

        $this->id = $stmt->fetch()?->id_user;
        return $this;
    }


    /**
     * Altera um objeto no banco de dados
     *
     * @return self
     */
    public function update(): self
    {
        $query = "CALL sp_update_user (:pid_user, :pdes_login, :pis_admin, :pdes_person,
                                       :pdes_email, :pdes_cpf, :pnum_phone)";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_user", $this->id, \PDO::PARAM_INT);
        $stmt->bindValue("pdes_login", $this->login, \PDO::PARAM_STR);
        $stmt->bindValue("pis_admin", $this->isAdmin, \PDO::PARAM_BOOL);
        $stmt->bindValue("pdes_person", $this->name, \PDO::PARAM_STR);
        $stmt->bindValue("pdes_email", $this->email, \PDO::PARAM_STR);
        $stmt->bindValue("pdes_cpf", $this->cpf, \PDO::PARAM_STR);
        $stmt->bindValue("pnum_phone", $this->phone, \PDO::PARAM_INT);
        $stmt->execute();

        return $this;
    }


    /**
     * Altera a senha do usuário
     *
     * @param string $newPassword Nova senha
     *
     * @return self
     */
    public function updatePassword(string $newPassword): self
    {
        $query = "UPDATE tb_users SET des_password = :pdes_password WHERE id_user = :pid_user";
        $password = password_hash($newPassword, PASSWORD_DEFAULT, ["cost" => 12]);

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pdes_password", $password, \PDO::PARAM_STR);
        $stmt->bindValue("pid_user", $this->id, \PDO::PARAM_INT);
        $stmt->execute();

        $this->password = $password;
        return $this;
    }


    /**
     * Remove o objeto no banco de dados
     *
     * @return self
     */
    public function delete(): self
    {
        $query = "CALL sp_delete_user (:pid_user)";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_user", $this->id, \PDO::PARAM_INT);
        $stmt->execute();

        return $this;
    }


    /**
     * Retorna todos os registros da tabela de usuários
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
        $query = "SELECT $fields FROM tb_users INNER JOIN tb_persons USING (id_person)" .
            (in_array($sortBy, array_keys(self::$_columns)) || in_array($sortBy, array_keys(parent::$columns))
                ? " ORDER BY " . (self::$_columns[$sortBy] ?? parent::$columns[$sortBy])
                : "") .
            ($limit > 0 ? " LIMIT $limit" : "") .
            ($offset > 0 ? " OFFSET $offset" : "");

        return array_map(
            fn (object $row): self => self::_translate($row),
            (SQL::get())->send($query)->fetchAll()
        );
    }


    /**
     * Retorna o usuário a partir do ID fornecido
     *
     * @param int $id ID do usuário
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromId(int $id): ?self
    {
        if ($user = parent::cache($id, __CLASS__)) {
            return $user;
        }

        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_users
                   WHERE id_user = :pid_user";

        $row = (SQL::get())->send($query, ["pid_user" => $id])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Retorna o usuário a partir do ID da pessoa fornecido
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
                    FROM tb_users
                   WHERE id_person = :pid_person";

        $row = (SQL::get())->send($query, ["pid_person" => $idPerson])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Retorna o usuário a partir do login fornecido
     *
     * @param string $login Login do usuário
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromLogin(string $login): ?self
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_users
                   WHERE des_login = :pdes_login";

        $row = (SQL::get())->send($query, ["pdes_login" => $login])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Retorna o usuário a partir do e-mail fornecido
     *
     * @param string $email E-mail do usuário/pessoa
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromEmail(string $email): ?self
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_users
                   INNER JOIN tb_persons USING (id_person)
                   WHERE des_email = :pdes_email";

        $row = (SQL::get())->send($query, ["pdes_email" => $email])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Instancia a classe a partir de um vetor de argumentos
     *
     * @param array<mixed> $arguments Vetor com os dados do usuário
     * @param ?self        $user      Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    public static function loadFromData(array $arguments, ?self $user = null): self
    {
        $user ??= new self();
        $user->loadData($arguments);

        $user->id = (int) ($arguments["id"] ?? 0);
        $user->login = trim($arguments["login"] ?? "");
        $user->password = $arguments["password"] ?? "";
        $user->isAdmin = (bool) in_array(($arguments["isAdmin"] ?? false), ["true", 1]);
        $user->dateRegister = $arguments["dateRegister"] ?? date("Y-m-d H:i:s");
        $user->dateLastChange = $arguments["dateLastChange"] ?? null;

        return $user;
    }


    /**
     * Instancia a classe a partir da sessão
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromSession(): ?self
    {
        $user = self::loadFromData($_SESSION[self::SESSION] ?? []);
        return $user->id > 0 ? $user : null;
    }


    /**
     * Recalcula o frete do carrinho
     *
     * @return self
     */
    public function refresh(): self
    {
        $sessionUser = self::loadFromSession();
        if ($this->id === $sessionUser?->id) {
            $sessionUser = self::loadFromId($this->id)?->saveInSession();
        }

        return $sessionUser ?? $this;
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
        parent::validate($errors);

        $this->id < 0 && array_push($errors, "ID inválido");

        (strlen($this->login) < 6 || strlen($this->login) > 64) &&
        array_push($errors, "login possui tamanho inválido");

        ($id = (int) self::loadFromLogin($this->login)?->id) &&
        $id !== $this->id &&
        array_push($errors, "login já cadastrado");

        (strlen($this->password) < 6 || strlen($this->password) > 256) &&
        array_push($errors, "senha possui tamanho inválido");

        strlen($this->password) > 6 && !$this->_validatePasswordStrength($this->password) &&
        array_push($errors, "senha fraca");

        return empty($errors);
    }


    /**
     * Retorna a classe em formato de vetor
     *
     * @return array
     */
    public function array(): array
    {
        return array_merge(
            [
                "id" => $this->id,
                "login" => $this->login,
                "password" => $this->password,
                "isAdmin" => (bool) $this->isAdmin,
                "dateRegister" => $this->dateRegister,
                "dateLastChange" => $this->dateLastChange
            ],
            parent::array()
        );
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
