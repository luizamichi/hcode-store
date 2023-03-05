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
 * Classe que modela a entidade SUBTÓPICO
 *
 * @category Model
 * @package  Amichi/Model
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class Subtopic extends Model implements JsonSerializable
{
    /**
     * Propriedade
     *
     * @var array $_columns Colunas de mapeamento objeto relacional
     */
    private static array $_columns = [
        "id" => "id_subtopic", // ID do subtópico
        "idTopic" => "id_topic", // Tópico
        "idType" => "id_type", // Tipo do subtópico
        "title" => "des_subtopic", // Título do subtópico
        "text" => "des_text", // Texto do subtópico
        "dateRegister" => "dt_subtopic_created_at", // Data de cadastro do subtópico
        "dateLastChange" => "dt_subtopic_changed_in" // Data da última alteração do subtópico
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
        $id > 0 && ($subtopic = self::loadFromId($id)) && self::loadFromData($subtopic->array(), $this);
    }


    /**
     * Traduz os valores da tabela para o objeto
     *
     * @param object $row      Linha da tabela de tópicos
     * @param ?self  $subtopic Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    private static function _translate(object $row, ?self $subtopic = null): self
    {
        $subtopic ??= new self();

        foreach (self::$_columns as $key => $value) {
            $subtopic->$key = $row->$value;
        }

        $subtopic->topic = Topic::loadFromId($row->id_topic ?? 0);
        $subtopic->type = TopicType::loadFromId($row->id_type ?? 0);

        parent::cache($subtopic->id, $subtopic);
        return $subtopic;
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
        $query = "CALL sp_save_subtopic (:pid_subtopic, :pid_topic, :pid_type, :pdes_subtopic, :pdes_text)";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_subtopic", $this->id, \PDO::PARAM_INT);
        $stmt->bindValue("pid_topic", $this->idTopic, \PDO::PARAM_INT);
        $stmt->bindValue("pid_type", $this->idType, \PDO::PARAM_INT);
        $stmt->bindValue("pdes_subtopic", $this->title, \PDO::PARAM_STR);
        $stmt->bindValue("pdes_text", $this->text, \PDO::PARAM_STR);
        $stmt->execute();

        $subtopic = $stmt->fetch();
        $stmt->closeCursor();

        self::_translate($subtopic, $this);
        return $this;
    }


    /**
     * Remove o objeto no banco de dados
     *
     * @return self
     */
    public function delete(): self
    {
        $query = "DELETE FROM tb_subtopics WHERE id_subtopic = :pid_subtopic";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_subtopic", $this->id, \PDO::PARAM_INT);
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
        $query = "SELECT $fields FROM tb_subtopics" .
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
        if ($subtopic = parent::cache($id, __CLASS__)) {
            return $subtopic;
        }

        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_subtopics
                   WHERE id_subtopic = :pid_subtopic";

        $row = (SQL::get())->send($query, ["pid_subtopic" => $id])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Retorna os subtópicos a partir do ID do tópico fornecido
     *
     * @param int $idTopic ID do tópico
     *
     * @static
     *
     * @return array[self]
     */
    public static function listFromTopicId(int $idTopic): array
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_subtopics
                   WHERE id_topic = :pid_topic";

        return array_map(
            fn (object $row): self => self::_translate($row),
            (SQL::get())->send($query, ["pid_topic" => $idTopic])->fetchAll()
        );
    }


    /**
     * Retorna os subtópicos a partir do ID do tipo de tópico fornecido
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
                    FROM tb_subtopics
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
     * @param ?self $subtopic  Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    public static function loadFromData(array $arguments, ?self $subtopic = null): self
    {
        $subtopic ??= new self();

        $subtopic->id = (int) ($arguments["id"] ?? 0);
        $subtopic->idTopic = (int) ($arguments["idTopic"] ?? 0) ?: null;
        $subtopic->topic = ($arguments["topic"] ?? []) ? Topic::loadFromData($arguments["topic"]) : Topic::loadFromId((int) $subtopic->idTopic);
        $subtopic->idType = (int) ($arguments["idType"] ?? 0) ?: null;
        $subtopic->type = ($arguments["type"] ?? []) ? TopicType::loadFromData($arguments["type"]) : TopicType::loadFromId((int) $subtopic->idType);
        $subtopic->title = trim($arguments["title"] ?? "");
        $subtopic->text = trim($arguments["text"] ?? "");
        $subtopic->dateRegister = $arguments["dateRegister"] ?? date("Y-m-d H:i:s");
        $subtopic->dateLastChange = $arguments["dateLastChange"] ?? null;

        return $subtopic;
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

        ((!$this->idTopic && !$this->idType) || ($this->idTopic < 0 && $this->idType < 0) ||
        ($this->idTopic > 0 && $this->idType > 0)) &&
        array_push($errors, "tópico/tipo inválido");

        $this->idTopic !== null && (int) Topic::loadFromId($this->idTopic)?->id <= 0 &&
        $this->idType < 0 &&
        array_push($errors, "tópico inexistente");

        $this->idType !== null && (int) TopicType::loadFromId($this->idType)?->id <= 0 &&
        $this->idTopic < 0 &&
        array_push($errors, "tipo inexistente");

        (strlen($this->title) < 6 || strlen($this->title) > 64) &&
        array_push($errors, "título possui tamanho inválido");

        (strlen($this->text) < 16 || strlen($this->text) > 16777215) &&
        array_push($errors, "texto possui tamanho inválido");

        return empty($errors);
    }


    /**
     * Retorna a classe em formato de vetor
     *
     * @param bool $exportAllProperties Exporta todas as propriedades?
     *
     * @return array
     */
    public function array(bool $exportAllProperties = true): array
    {
        return [
            "id" => $this->id,
            "idTopic" => $this->idTopic,
            "topic" => $exportAllProperties ? $this->topic?->array() : null,
            "idType" => $this->idType,
            "type" => $exportAllProperties ? $this->type?->array() : null,
            "title" => $this->title,
            "text" => $this->text,
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
