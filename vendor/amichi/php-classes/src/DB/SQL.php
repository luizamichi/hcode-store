<?php

/**
 * PHP version 8.1.2
 *
 * @category DB
 * @package  Amichi/DB
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */

namespace Amichi\DB;

use Amichi\HttpException;

/**
 * Classe que realiza a conexão com a base de dados
 *
 * @category DB
 * @package  Amichi/DB
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class SQL extends \PDO
{
    /**
     * Propriedade
     *
     * @var int $_id Identificadores das conexões abertas
     */
    private int $_id;


    /**
     * Propriedade
     *
     * @var array<self> $_connections Vetor estáticos das conexões abertas
     */
    private static array $_connections = [];


    /**
     * Construtor
     *
     * @param string           $schema   Nome da base de dados
     * @param string           $hostname URL ou IP do servidor
     * @param string           $username Usuário da base de dados
     * @param string           $password Senha de acesso
     * @param int              $port     Porta da conexão
     * @param string           $driver   SGBD
     * @param array<int,mixed> $options  Opções de configuração
     *
     * @throws \PDOException Se não conseguir se conectar ao banco de dados
     *
     * @return void
     */
    public function __construct(
        private string $schema = "",
        private string $hostname = "",
        private string $username = "",
        private string $password = "",
        private int $port = 3306,
        private string $driver = "",
        private array $options = [
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8; SET time_zone = '-3:00'"
        ]
    ) {
        $this->schema = $schema ?: (string) getenv("MYSQL_SCHEMA");
        $this->hostname = $hostname ?: (string) getenv("MYSQL_HOSTNAME");
        $this->username = $username ?: (string) getenv("MYSQL_USERNAME");
        $this->password = $password ?: (string) getenv("MYSQL_PASSWORD");
        $this->port = $port ?: (int) getenv("MYSQL_PORT");
        $this->driver = $driver ?: (string) getenv("MYSQL_DRIVER");
        $this->_id = $this->_check();

        try {
            $dns = $this->driver . ":host={$this->hostname};port={$this->port};dbname={$this->schema}";

            parent::__construct($dns, $this->username, $this->password, $this->options);

            if ($this->_id === 0) {
                self::$_connections[] = $this;
                $this->_id = count(self::$_connections);
            }
        } catch (\PDOException $e) {
            throw (new HttpException("Falha ao conectar-se na base de dados ({$e->getMessage()}).", 500))->json();
        }
    }


    /**
     * Encerra a conexão com o banco
     *
     * @return void
     */
    public function __destruct()
    {
        self::$_connections[$this->_id - 1] = null;
        array_splice(self::$_connections, $this->_id - 1, 1);
    }


    /**
     * Retorna o ID da conexão
     *
     * @return int
     */
    public function id(): int
    {
        return $this->_id;
    }


    /**
     * Cria uma conexão a partir das configurações informadas
     *
     * @param array $configurations Vetor com as configurações
     *
     * @static
     *
     * @return self
     */
    public static function set(array $configurations): self
    {
        return new self(
            $configurations["schema"] ?? "",
            $configurations["hostname"] ?? "",
            $configurations["username"] ?? "",
            $configurations["password"] ?? "",
            $configurations["port"] ?? 0,
            $configurations["driver"] ?? "",
            $configurations["options"] ?? [],
        );
    }


    /**
     * Retorna a conexão a partir do ID.
     * Caso não informado (zero), então retornará a última ou criará uma nova
     *
     * @param int $id ID da conexão (gerado na instanciação)
     *
     * @static
     *
     * @return self
     */
    public static function get(int $id = 0): self
    {
        return self::$_connections[$id - 1] ?? (empty(self::$_connections) ? new self() : end(self::$_connections));
    }


    /**
     * Retorna um vetor com as configurações da conexão com o banco de dados
     *
     * @return array<string,array<int,mixed>|int|string>
     */
    public function configurations(): array
    {
        return [
            "schema" => $this->schema,
            "hostname" => $this->hostname,
            "username" => $this->username,
            "password" => $this->password,
            "port" => $this->port,
            "driver" => $this->driver,
            "options" => $this->options
        ];
    }


    /**
     * Verifica se já possui uma conexão aberta com o banco de dados
     *
     * @return int
     */
    private function _check(): int
    {
        foreach (self::$_connections as $connection) {
            $difference = strcmp(json_encode($this->configurations()), json_encode($connection->configurations()));
            if ($difference === 0) {
                return $connection->id();
            }
        }

        return 0;
    }


    /**
     * Faz uma consulta na base de dados
     *
     * @param string              $query      Consulta
     * @param array<string,mixed> $parameters Parâmetros
     * @param bool                $param      Utilizará bindParam (true) ou bindValue (false)
     *
     * @throws \PDOException Se não conseguir realizar a consulta no banco de dados
     *
     * @return \PDOStatement
     */
    public function send(string $query, array $parameters = [], bool $param = false): \PDOStatement
    {
        $statement = $this->prepare($query);
        foreach ($parameters as $key => $value) {
            $param
            ? $statement->bindParam($key, $value)
            : $statement->bindValue($key, $value);
        }

        try {
            $statement->execute();
        } catch (\PDOException $e) {
            throw (new HttpException("Falha ao realizar a consulta na base de dados ({$e->getMessage()}).", 500))->json();
        }

        return $statement;
    }
}
