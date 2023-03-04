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
 * Classe que modela a entidade CONTATO
 *
 * @category Model
 * @package  Amichi/Model
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class Contact extends Model implements JsonSerializable
{
    /**
     * Propriedade
     *
     * @var array $_columns Colunas de mapeamento objeto relacional
     */
    private static array $_columns = [
        "id" => "id_contact", // ID do contato
        "name" => "des_contact", // Nome do contato
        "email" => "des_contact_email", // E-mail do contato
        "subject" => "des_contact_subject", // Assunto do contato
        "message" => "des_message", // Mensagem do contato
        "dateRegister" => "dt_contact_created_at" // Data de cadastro do contato
    ];


    /**
     * Construtor
     *
     * @param int $id ID do contato
     *
     * @return void
     */
    public function __construct(int $id = 0)
    {
        $id > 0 && ($contact = self::loadFromId($id)) && self::loadFromData($contact->array(), $this);
    }


    /**
     * Traduz os valores da tabela para o objeto
     *
     * @param object $row     Linha da tabela de contatos
     * @param ?self  $contact Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    private static function _translate(object $row, ?self $contact = null): self
    {
        $contact ??= new self();

        foreach (self::$_columns as $key => $value) {
            $contact->$key = $row->$value;
        }

        parent::cache($contact->id, $contact);
        return $contact;
    }


    /**
     * Obtém o nome dos campos da tabela de contatos
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
        $query = "CALL sp_save_contact (:pdes_contact, :pdes_contact_email, :pdes_contact_subject, :pdes_message)";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pdes_contact", $this->name, \PDO::PARAM_STR);
        $stmt->bindValue("pdes_contact_email", $this->email, \PDO::PARAM_STR);
        $stmt->bindValue("pdes_contact_subject", $this->subject, \PDO::PARAM_STR);
        $stmt->bindValue("pdes_message", $this->message, \PDO::PARAM_STR);
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
        $query = "DELETE FROM tb_contacts WHERE id_contact = :pid_contact";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_contact", $this->id, \PDO::PARAM_INT);
        $stmt->execute();

        return $this;
    }


    /**
     * Retorna todos os registros da tabela de contatos
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
        $query = "SELECT $fields FROM tb_contacts" .
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
     * Retorna o contato a partir do ID fornecido
     *
     * @param int $id ID do contato
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromId(int $id): ?self
    {
        if ($contact = parent::cache($id, __CLASS__)) {
            return $contact;
        }

        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_contacts
                   WHERE id_contact = :pid_contact";

        $row = (SQL::get())->send($query, ["pid_contact" => $id])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Instancia a classe a partir de um vetor de argumentos
     *
     * @param array $arguments Vetor com os dados do contato
     * @param ?self $contact   Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    public static function loadFromData(array $arguments, ?self $contact = null): self
    {
        $contact ??= new self();

        $contact->id = (int) ($arguments["id"] ?? 0);
        $contact->name = $arguments["name"] ?? "";
        $contact->email = $arguments["email"] ?? "";
        $contact->subject = $arguments["subject"] ?? "";
        $contact->message = $arguments["message"] ?? "";
        $contact->dateRegister = $arguments["dateRegister"] ?? date("Y-m-d H:i:s");

        return $contact;
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

        (strlen($this->email) < 6 || strlen($this->email) > 128) &&
        array_push($errors, "e-mail possui tamanho inválido");

        !filter_var($this->email, FILTER_VALIDATE_EMAIL) &&
        array_push($errors, "e-mail inválido");

        (strlen($this->subject) < 6 || strlen($this->subject) > 256) &&
        array_push($errors, "assunto possui tamanho inválido");

        (strlen($this->message) < 12 || strlen($this->message) > 4294967295) &&
        array_push($errors, "mensagem possui tamanho inválido");

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
            "email" => $this->email,
            "subject" => $this->subject,
            "message" => $this->message,
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
