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
use Amichi\Enumerated\TopicType as EnumeratedTopicType;
use Amichi\Model;
use JsonSerializable;

/**
 * Classe que modela a entidade TIPO DE TÓPICO
 *
 * @category Model
 * @package  Amichi/Model
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class TopicType extends Model implements JsonSerializable
{
    /**
     * Propriedade
     *
     * @var array $_columns Colunas de mapeamento objeto relacional
     */
    private static array $_columns = [
        "id" => "id_type", // ID do tipo de tópico
        "title" => "des_type", // Título do tipo de tópico
        "summary" => "des_summary", // Sumário do tipo de tópico
        "slug" => "des_route", // Identificador único para acesso na URL
        "dateRegister" => "dt_type_created_at" // Data de cadastro do tipo de tópico
    ];


    /**
     * Construtor
     *
     * @param int $id ID do tipo de tópico
     *
     * @return void
     */
    public function __construct(int $id = 0)
    {
        $id > 0 && ($topicType = self::loadFromId($id)) && self::loadFromData($topicType->array(), $this);
    }


    /**
     * Traduz os valores da tabela para o objeto
     *
     * @param object $row       Linha da tabela de tipo de tópico
     * @param ?self  $topicType Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    private static function _translate(object $row, ?self $topicType = null): self
    {
        $topicType ??= new self();

        foreach (self::$_columns as $key => $value) {
            $topicType->$key = $row->$value;
        }

        $topicType->enum = EnumeratedTopicType::tryFrom($topicType->title);

        parent::cache($topicType->id, $topicType);
        return $topicType;
    }


    /**
     * Obtém o nome dos campos da tabela de tipo de tópico
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
        $query = "CALL sp_save_topic_type (:pid_type, :pdes_type, :pdes_summary, :pdes_route)";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_type", $this->id, \PDO::PARAM_INT);
        $stmt->bindValue("pdes_type", $this->title, \PDO::PARAM_STR);
        $stmt->bindValue("pdes_summary", $this->summary, \PDO::PARAM_STR);
        $stmt->bindValue("pdes_route", $this->slug, \PDO::PARAM_STR);
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
        $query = "DELETE FROM tb_topics_types WHERE id_type = :pid_type";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_type", $this->id, \PDO::PARAM_INT);
        $stmt->execute();

        return $this;
    }


    /**
     * Retorna todos os registros da tabela de tipo de tópico
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
        $query = "SELECT $fields FROM tb_topics_types" .
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
     * Retorna o tipo de tópico a partir do ID fornecido
     *
     * @param int $id ID do tipo de tópico
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromId(int $id): ?self
    {
        if ($topicType = parent::cache($id, __CLASS__)) {
            return $topicType;
        }

        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_topics_types
                   WHERE id_type = :pid_type";

        $row = (SQL::get())->send($query, ["pid_type" => $id])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Retorna o tipo de tópico a partir do título fornecido
     *
     * @param string $title Título do tipo de tópico
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromTitle(string $title): ?self
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_topics_types
                   WHERE des_type = :pdes_type";

        $row = (SQL::get())->send($query, ["pdes_type" => $title])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Retorna o tipo de tópico a partir do slug fornecido
     *
     * @param string $slug Slug do tipo de tópico
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromSlug(string $slug): ?self
    {
        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_topics_types
                   WHERE des_route = :pdes_route";

        $row = (SQL::get())->send($query, ["pdes_route" => $slug])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Retorna o tipo de tópico a partir do enumerado fornecido
     *
     * @param EnumeratedTopicType $enum Enumerado do tipo de tópico
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromEnum(EnumeratedTopicType $enum): ?self
    {
        return self::loadFromTitle((string) $enum->value);
    }


    /**
     * Instancia a classe a partir de um vetor de argumentos
     *
     * @param array $arguments Vetor com os dados do tipo de tópico
     * @param ?self $topicType Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    public static function loadFromData(array $arguments, ?self $topicType = null): self
    {
        $topicType ??= new self();

        $topicType->id = (int) ($arguments["id"] ?? 0);
        $topicType->title = trim($arguments["title"] ?? "");
        $topicType->summary = trim($arguments["summary"] ?? "") ?: null;
        $topicType->enum = EnumeratedTopicType::tryFrom($topicType->title);
        $topicType->slug = trim(strtolower($arguments["slug"] ?? ""));
        $topicType->dateRegister = $arguments["dateRegister"] ?? date("Y-m-d H:i:s");

        return $topicType;
    }


    /**
     * Retorna todos os tópicos/subtópicos que estão no tipo de tópico
     *
     * @param bool $getSubtopics Obtém os subtópicos?
     *
     * @return array[Topic]
     */
    public function getTopics(bool $getSubtopics = true): array
    {
        if ($getSubtopics) {
            return array_map(
                fn (Topic $topic): Topic => $topic->loadSubtopics(),
                Topic::listFromTopicTypeId($this->id)
            );
        } else {
            return Topic::listFromTopicTypeId($this->id);
        }
    }


    /**
     * Retorna todos os subtópicos que estão no tipo de tópico
     *
     * @return array[Topic]
     */
    public function getSubtopics(): array
    {
        return Subtopic::listFromTopicTypeId($this->id);
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

        (strlen($this->title) < 4 || strlen($this->title) > 32) &&
        array_push($errors, "título possui tamanho inválido");

        ($id = (int) self::loadFromTitle($this->title)?->id) &&
        $id !== $this->id &&
        array_push($errors, "título já cadastrado");

        $this->summary !== null && (strlen($this->summary) < 4 || strlen($this->summary) > 512) &&
        array_push($errors, "sumário possui tamanho inválido");

        (strlen($this->slug) < 2 || strlen($this->slug) > 64) &&
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
            "title" => $this->title,
            "summary" => $this->summary,
            "slug" => $this->slug,
            "enum" => $this->enum?->name,
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
