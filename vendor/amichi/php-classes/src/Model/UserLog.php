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
 * Classe que modela a entidade LOG DE USUÁRIO
 *
 * @category Model
 * @package  Amichi/Model
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class UserLog extends Model implements JsonSerializable
{
    /**
     * Propriedade
     *
     * @var array $_columns Colunas de mapeamento objeto relacional
     */
    private static array $_columns = [
        "id" => "id_log", // ID do log
        "idUser" => "id_user", // ID do usuário
        "description" => "des_log", // Descrição do log
        "device" => "des_device", // Identificador da máquina do usuário
        "userAgent" => "des_user_agent", // Navegador web
        "idSession" => "des_php_session_id", // ID da sessão PHP
        "sourceUrl" => "des_source_url", // URL de origem
        "url" => "des_url", // URL acessada pelo usuário
        "dateRegister" => "dt_log_created_at" // Data de cadastro do log
    ];


    /**
     * Construtor
     *
     * @param int $id ID do log
     *
     * @return void
     */
    public function __construct(int $id = 0)
    {
        $id > 0 && ($userLog = self::loadFromId($id)) && self::loadFromData($userLog->array(), $this);
    }


    /**
     * Traduz os valores da tabela para o objeto
     *
     * @param object $row     Linha da tabela de logs
     * @param ?self  $userLog Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    private static function _translate(object $row, ?self $userLog = null): self
    {
        $userLog ??= new self();

        foreach (self::$_columns as $key => $value) {
            $userLog->$key = $row->$value;
        }

        $userLog->user = new User($row->id_user);

        parent::cache($userLog->id, $userLog);
        return $userLog;
    }


    /**
     * Obtém o nome dos campos da tabela de log
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
     * Verifica o dispositivo
     *
     * @return void
     */
    private function _checkDevice(): void
    {
        $userAgent = strtolower($_SERVER["HTTP_USER_AGENT"]);
        $isMobible = is_numeric(strpos($userAgent, "mobile"));
        $isTablet = is_numeric(strpos($userAgent, "tablet"));
        $isDesktop = !$isMobible && !$isTablet;

        $this->device = $isDesktop ? "Desktop" : "Mobile";
    }


    /**
     * Salva o objeto no banco de dados
     *
     * @return self
     */
    public function save(): self
    {
        $this->_checkDevice();

        $query = "CALL sp_save_user_log (:pid_log, :pid_user, :pdes_log, :pdes_device, :pdes_user_agent,
                                         :pdes_php_session_id, :pdes_source_url, :pdes_url)";

        $stmt = (new SQL())->prepare($query);
        $stmt->bindValue("pid_log", $this->id, \PDO::PARAM_INT);
        $stmt->bindValue("pid_user", $this->idUser, \PDO::PARAM_INT);
        $stmt->bindValue("pdes_log", $this->description, \PDO::PARAM_STR);
        $stmt->bindValue("pdes_device", $this->device, \PDO::PARAM_STR);
        $stmt->bindValue("pdes_user_agent", $_SERVER["HTTP_USER_AGENT"], \PDO::PARAM_STR);
        $stmt->bindValue("pdes_php_session_id", session_id(), \PDO::PARAM_STR);
        $stmt->bindValue("pdes_source_url", $_SERVER["HTTP_REFERER"] ?? null, \PDO::PARAM_STR);
        $stmt->bindValue("pdes_url", $_SERVER["REQUEST_URI"], \PDO::PARAM_STR);
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
        $query = "DELETE FROM tb_users_logs WHERE id_recovery = :pid_recovery";

        $stmt = (new SQL())->prepare($query);
        $stmt->bindValue("pid_recovery", $this->id, \PDO::PARAM_INT);
        $stmt->execute();

        return $this;
    }


    /**
     * Retorna todos os registros da tabela de log
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
        $query = "SELECT $fields FROM tb_users_logs" .
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
     * Retorna o log a partir do ID fornecido
     *
     * @param int $id ID do log
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromId(int $id): ?self
    {
        if ($userLog = parent::cache($id, __CLASS__)) {
            return $userLog;
        }

        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_users_logs
                   WHERE id_recovery = :pid_recovery";

        $row = (new SQL())->send($query, ["pid_recovery" => $id])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Retorna os logs a partir do ID do usuário
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
                    FROM tb_users_logs
                   WHERE id_user = :pid_user";

        return array_map(
            fn (object $row): self => self::_translate($row),
            (new SQL())->send($query, ["pid_user" => $idUser])->fetchAll()
        );
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
        $userLog = self::loadFromData(
            [
                "user" => $_SESSION[User::SESSION] ?? []
            ]
        );

        $userLog->idUser = $userLog->user?->id;
        return $userLog->idUser > 0 ? $userLog : null;
    }


    /**
     * Instancia a classe a partir de um vetor de argumentos
     *
     * @param array $arguments Vetor com os dados do log
     * @param ?self $userLog   Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    public static function loadFromData(array $arguments, ?self $userLog = null): self
    {
        $userLog ??= new self();

        $userLog->id = (int) ($arguments["id"] ?? 0);
        $userLog->idUser = (int) ($arguments["idUser"] ?? 0);
        $userLog->user = ($arguments["user"] ?? []) ? User::loadFromData($arguments["user"]) : User::loadFromId($userLog->idUser);
        $userLog->description = trim($arguments["description"] ?? "");
        $userLog->device = trim($arguments["device"] ?? "") ?: null;
        $userLog->userAgent = trim($arguments["userAgent"] ?? "");
        $userLog->idSession = trim($arguments["idSession"] ?? "");
        $userLog->sourceUrl = trim($arguments["sourceUrl"] ?? "") ?: null;
        $userLog->url = trim($arguments["url"] ?? "");
        $userLog->dateRegister = $arguments["dateRegister"] ?? date("Y-m-d H:i:s");

        return $userLog;
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
            "description" => $this->description,
            "device" => $this->device,
            "userAgent" => $this->userAgent,
            "idSession" => $this->idSession,
            "sourceUrl" => $this->sourceUrl,
            "url" => $this->url,
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
