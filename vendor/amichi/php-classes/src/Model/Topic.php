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
 * Classe que modela a entidade TÓPICO
 *
 * @category Model
 * @package  Amichi/Model
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class Topic extends Model implements JsonSerializable
{
    /**
     * Propriedade
     *
     * @var array $_columns Colunas de mapeamento objeto relacional
     */
    private static array $_columns = [
        "id" => "id_topic", // ID do tópico
        "idType" => "id_type", // Tipo do tópico
        "title" => "des_topic", // Título do tópico
        "dateRegister" => "dt_topic_created_at" // Data de cadastro do tópico
    ];


    /**
     * Construtor
     *
     * @param int $id ID do tópico
     *
     * @return void
     */
    public function __construct(int $id = 0)
    {
        $id > 0 && ($topic = self::loadFromId($id)) && self::loadFromData($topic->array(), $this);
        $this->subtopics = [];
    }


    /**
     * Traduz os valores da tabela para o objeto
     *
     * @param object $row   Linha da tabela de tópicos
     * @param ?self  $topic Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    private static function _translate(object $row, ?self $topic = null): self
    {
        $topic ??= new self();

        foreach (self::$_columns as $key => $value) {
            $topic->$key = $row->$value;
        }

        $topic->type = new TopicType($row->id_type);

        parent::cache($topic->id, $topic);
        return $topic;
    }


    /**
     * Obtém o nome dos campos da tabela de tópicos
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
        $query = "CALL sp_save_topic (:pid_topic, :pid_type, :pdes_topic)";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_topic", $this->id, \PDO::PARAM_INT);
        $stmt->bindValue("pid_type", $this->idType, \PDO::PARAM_INT);
        $stmt->bindValue("pdes_topic", $this->title, \PDO::PARAM_STR);
        $stmt->execute();

        $topic = $stmt->fetch();
        $stmt->closeCursor();

        self::_translate($topic, $this);
        return $this;
    }


    /**
     * Remove o objeto no banco de dados
     *
     * @return self
     */
    public function delete(): self
    {
        $query = "DELETE FROM tb_topics WHERE id_topic = :pid_topic";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_topic", $this->id, \PDO::PARAM_INT);
        $stmt->execute();

        return $this;
    }


    /**
     * Retorna todos os registros da tabela de tópicos
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
        $query = "SELECT $fields FROM tb_topics" .
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
     * Retorna o tópico a partir do ID fornecido
     *
     * @param int $id ID do tópico
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromId(int $id): ?self
    {
        if ($topic = parent::cache($id, __CLASS__)) {
            return $topic;
        }

        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_topics
                   WHERE id_topic = :pid_topic";

        $row = (SQL::get())->send($query, ["pid_topic" => $id])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Retorna os tópicos a partir do ID do tipo de tópico fornecido
     *
     * @param int $idType ID do tipo de tópico
     *
     * @static
     *
     * @return array[self]
     */
    public static function listFromTopicTypeId(int $idType): array
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_topics
                   WHERE id_type = :pid_type";

        return array_map(
            fn (object $row): self => self::_translate($row),
            (SQL::get())->send($query, ["pid_type" => $idType])->fetchAll()
        );
    }



    /**
     * Instancia a classe a partir de um vetor de argumentos
     *
     * @param array $arguments Vetor com os dados do tópico
     * @param ?self $topic     Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    public static function loadFromData(array $arguments, ?self $topic = null): self
    {
        $topic ??= new self();

        $topic->id = (int) ($arguments["id"] ?? 0);
        $topic->idType = (int) ($arguments["idType"] ?? 0);
        $topic->type = ($arguments["type"] ?? []) ? TopicType::loadFromData($arguments["type"]) : TopicType::loadFromId($topic->idType);
        $topic->title = trim($arguments["title"] ?? "");
        $topic->dateRegister = $arguments["dateRegister"] ?? date("Y-m-d H:i:s");

        return $topic;
    }


    /**
     * Carrega os subtópicos do tópico
     *
     * @return self
     */
    public function loadSubtopics(): self
    {
        $this->subtopics = Subtopic::listFromTopicId($this->id);
        return $this;
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

        $this->idType < 0 && array_push($errors, "tipo inválido");

        (int) TopicType::loadFromId($this->idType)?->id <= 0 &&
        array_push($errors, "tipo inexistente");

        (strlen($this->title) < 6 || strlen($this->title) > 64) &&
        array_push($errors, "título possui tamanho inválido");

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
            "idType" => $this->idType,
            "type" => $this->type->array(),
            "title" => $this->title,
            "dateRegister" => $this->dateRegister,
            "subtopics" => array_map(
                fn (Subtopic $subtopic): array => $subtopic->array(false),
                $this->subtopics
            )
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
