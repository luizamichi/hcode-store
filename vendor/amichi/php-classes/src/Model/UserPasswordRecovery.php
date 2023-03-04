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
use Amichi\Enumerated\UserPasswordRecoveryStatus;
use Amichi\Model;
use Amichi\Trait\Encoder;
use Amichi\Trait\Validator;
use JsonSerializable;

/**
 * Classe que modela a entidade RECUPERAÇÃO DE SENHA DE USUÁRIO
 *
 * @category Model
 * @package  Amichi/Model
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class UserPasswordRecovery extends Model implements JsonSerializable
{
    use Encoder;
    use Validator;


    /**
     * Propriedade
     *
     * @var array $_columns Colunas de mapeamento objeto relacional
     */
    private static array $_columns = [
        "id" => "id_recovery", // ID da recuperação de senha
        "idUser" => "id_user", // ID do usuário
        "ip" => "des_ip", // IP da máquina que solicitou a recuperação de senha
        "securityKey" => "des_security_key", // Chave de segurança
        "dateRegister" => "dt_recovery_created_at", // Data de cadastro
        "dateRecovery" => "dt_recovery" // Data de recuperação
    ];


    /**
     * Construtor
     *
     * @param int $id ID da recuperação de senha
     *
     * @return void
     */
    public function __construct(int $id = 0)
    {
        $id > 0 && ($userPasswordRecovery = self::loadFromId($id)) && self::loadFromData($userPasswordRecovery->array(), $this);
    }


    /**
     * Traduz os valores da tabela para o objeto
     *
     * @param object $row                  Linha da tabela de recuperação de senhas
     * @param ?self  $userPasswordRecovery Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    private static function _translate(object $row, ?self $userPasswordRecovery = null): self
    {
        $userPasswordRecovery ??= new self();

        foreach (self::$_columns as $key => $value) {
            $userPasswordRecovery->$key = $row->$value;
        }

        $userPasswordRecovery->user = new User($row->id_user);
        $userPasswordRecovery->password = $userPasswordRecovery->user->password;
        $userPasswordRecovery->code = self::crypt((string) $row->id_recovery);
        $userPasswordRecovery->sk = self::crypt($row->des_security_key);
        $userPasswordRecovery->_calculateStatus();

        parent::cache($userPasswordRecovery->id, $userPasswordRecovery);
        return $userPasswordRecovery;
    }


    /**
     * Obtém o nome dos campos da tabela de recuperação de senhas
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
     * Calcula o status da solicitação
     *
     * @return void
     */
    private function _calculateStatus(): void
    {
        $this->status = UserPasswordRecoveryStatus::REQUESTED;

        if ($this->dateRecovery !== null) {
            $this->status = UserPasswordRecoveryStatus::REALIZED;
            return;
        }

        $dateRegister = date_create($this->dateRegister);
        $now = date_create(date("Y-m-d H:i:s"));
        $difference = date_diff($dateRegister, $now);

        if (($difference->y * 12 * 30 * 24) + ($difference->m * 30 * 24) + ($difference->d * 24) + $difference->h >= 2) {
            $this->status = UserPasswordRecoveryStatus::EXPIRED;
        }
    }


    /**
     * Cria um novo objeto no banco de dados
     *
     * @return self
     */
    public function create(): self
    {
        $query = "CALL sp_create_user_password_recovery (:pid_user, :pdes_ip)";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_user", $this->idUser, \PDO::PARAM_INT);
        $stmt->bindValue("pdes_ip", $_SERVER["REMOTE_ADDR"], \PDO::PARAM_STR);
        $stmt->execute();

        $userPasswordRecovery = $stmt->fetch();
        $stmt->closeCursor();

        self::_translate($userPasswordRecovery, $this);
        return $this;
    }


    /**
     * Altera um objeto no banco de dados
     *
     * @return self
     */
    public function update(): self
    {
        $query = "UPDATE tb_users_passwords_recoveries SET dt_recovery = NOW() WHERE id_recovery = :pid_recovery";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_recovery", $this->id, \PDO::PARAM_INT);
        $stmt->execute();

        $user = User::loadFromId($this->idUser)?->updatePassword($this->password);

        $this->password = $user->password;
        return $this;
    }


    /**
     * Remove o objeto no banco de dados
     *
     * @return self
     */
    public function delete(): self
    {
        $query = "DELETE FROM tb_users_passwords_recoveries WHERE id_recovery = :pid_recovery";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_recovery", $this->id, \PDO::PARAM_INT);
        $stmt->execute();

        return $this;
    }


    /**
     * Retorna todos os registros da tabela de recuperação de senhas
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
        $query = "SELECT $fields FROM tb_users_passwords_recoveries" .
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
     * Retorna a recuperação de senha a partir do ID fornecido
     *
     * @param int $id ID da recuperação de senha
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromId(int $id): ?self
    {
        if ($userPasswordRecovery = parent::cache($id, __CLASS__)) {
            return $userPasswordRecovery;
        }

        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_users_passwords_recoveries
                   WHERE id_recovery = :pid_recovery";

        $row = (SQL::get())->send($query, ["pid_recovery" => $id])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Retorna as recuperações de senhas a partir do ID do usuário
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
                    FROM tb_users_passwords_recoveries
                   WHERE id_user = :pid_user";

        return array_map(
            fn (object $row): self => self::_translate($row),
            (SQL::get())->send($query, ["pid_user" => $idUser])->fetchAll()
        );
    }


    /**
     * Retorna a recuperação de senha a partir da chave de segurança fornecida
     *
     * @param string $securityKey Chave de segurança
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromSecurityKey(int $securityKey): ?self
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_users_passwords_recoveries
                   WHERE des_security_key = :pdes_security_key";

        $row = (SQL::get())->send($query, ["pdes_security_key" => $securityKey])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Retorna a recuperação de senha a partir das chaves de validação
     *
     * @param string $code        Código
     * @param string $securityKey Chave de segurança
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromValidationKeys(string $code, string $securityKey): ?self
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_users_passwords_recoveries
                   WHERE id_recovery = :pid_recovery
                     AND des_security_key = :pdes_security_key";

        $row = (SQL::get())->send(
            $query,
            [
                "pid_recovery" => (int) self::decrypt($code),
                "pdes_security_key" => mb_convert_encoding(self::decrypt($securityKey), "UTF-8", "ISO-8859-1")
            ]
        )->fetch();

        return $row ? self::_translate($row) : null;
    }


    /**
     * Instancia a classe a partir de um vetor de argumentos
     *
     * @param array $arguments            Vetor com os dados da recuperação de senha
     * @param ?self $userPasswordRecovery Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    public static function loadFromData(array $arguments, ?self $userPasswordRecovery = null): self
    {
        $userPasswordRecovery ??= new self();

        $userPasswordRecovery->id = (int) ($arguments["id"] ?? 0);
        $userPasswordRecovery->idUser = (int) ($arguments["idUser"] ?? 0);
        $userPasswordRecovery->user = ($arguments["user"] ?? []) ? User::loadFromData($arguments["user"]) : User::loadFromId($userPasswordRecovery->idUser);
        $userPasswordRecovery->ip = trim($arguments["ip"] ?? "") ?: null;
        $userPasswordRecovery->password = $arguments["password"] ?? null;
        $userPasswordRecovery->securityKey = trim($arguments["securityKey"] ?? "");
        $userPasswordRecovery->dateRegister = $arguments["dateRegister"] ?? date("Y-m-d H:i:s");
        $userPasswordRecovery->dateRecovery = $arguments["dateRecovery"] ?? null;
        $userPasswordRecovery->code = trim($arguments["code"] ?? "") ?: null;
        $userPasswordRecovery->sk = trim($arguments["sk"] ?? "") ?: null;

        if ($userPasswordRecovery->code) {
            $userPasswordRecovery->id = (int) self::decrypt($userPasswordRecovery->code);
        }
        if ($userPasswordRecovery->sk) {
            $userPasswordRecovery->securityKey = self::decrypt($userPasswordRecovery->sk);
        }

        $userPasswordRecovery->_calculateStatus();

        return $userPasswordRecovery;
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

        $this->idUser < 0 && array_push($errors, "usuário inválido");

        $this->idUser > 0 &&
        (int) User::loadFromId($this->idUser)?->id <= 0 &&
        array_push($errors, "usuário inexistente");

        $this->ip !== null && strlen($this->ip) > 0 &&
        !$this->_validateIp($this->ip) &&
        array_push($errors, "IP inválido");

        $this->id > 0 && strlen($this->password) < 6 &&
        array_push($errors, "senha possui tamanho inválido");

        $this->id > 0 && !$this->_validatePasswordStrength((string) $this->password) &&
        array_push($errors, "senha fraca");

        $this->id > 0 &&
        strlen($this->securityKey) !== 32 &&
        array_push($errors, "chave de segurança inválida");

        $this->_calculateStatus();

        $this->id > 0 &&
        $this->status === UserPasswordRecoveryStatus::EXPIRED &&
        array_push($errors, "solicitação expirada (prazo de 2 horas)");

        $this->id > 0 && $this->dateRecovery !== null &&
        array_push($errors, "alteração de senha já efetuada");

        $userPasswordRecovery = self::loadFromId($this->id);

        $this->id > 0 &&
        ($this->code !== self::crypt($userPasswordRecovery?->id) || $this->sk !== self::crypt($userPasswordRecovery?->securityKey)) &&
        array_push($errors, "chaves de validação inválidas");

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
            "idUser" => $this->idUser,
            "user" => $this->user->array(),
            "ip" => $this->ip,
            "securityKey" => $this->securityKey,
            "dateRegister" => $this->dateRegister,
            "dateRecovery" => $this->dateRecovery,
            "code" => $this->code,
            "sk" => $this->sk,
            "status" => $this->status->value
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
