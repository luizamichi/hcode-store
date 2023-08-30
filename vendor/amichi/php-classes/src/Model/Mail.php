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
use Amichi\Mailer;
use Amichi\Model;
use JsonSerializable;

/**
 * Classe que modela a entidade E-MAIL
 *
 * @category Model
 * @package  Amichi/Model
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class Mail extends Model implements JsonSerializable
{
    /**
     * Propriedade
     *
     * @var array<string,string> $_columns Colunas de mapeamento objeto relacional
     */
    private static array $_columns = [
        "id" => "id_mail", // ID do e-mail
        "email" => "des_recipient_email", // Endereço de e-mail do destinatário
        "name" => "des_recipient_name", // Nome do destinatário do e-mail
        "subject" => "des_subject", // Assunto do e-mail
        "content" => "des_content", // Conteúdo do e-mail
        "files" => "des_files", // Arquivos anexados ao e-mail
        "isSent" => "is_sent", // E-mail foi enviado?
        "dateRegister" => "dt_mail_created_at", // Data de cadastro do e-mail
        "dateLastChange" => "dt_mail_changed_in" // Data da última alteração do e-mail
    ];


    /**
     * Construtor
     *
     * @param int $id ID do e-mail
     *
     * @return void
     */
    public function __construct(int $id = 0)
    {
        $id > 0 && ($mail = self::loadFromId($id)) && self::loadFromData($mail->array(), $this);
    }


    /**
     * Traduz os valores da tabela para o objeto
     *
     * @param object $row  Linha da tabela de e-mails
     * @param ?self  $mail Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    private static function _translate(object $row, ?self $mail = null): self
    {
        $mail ??= new self();

        foreach (self::$_columns as $key => $value) {
            $mail->$key = $row->$value;
        }

        $mail->files = array_filter(explode(";", $row->des_files));
        parent::cache($mail->id, $mail);
        return $mail;
    }


    /**
     * Obtém o nome dos campos da tabela de e-mails
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
     * Salva e envia o e-mail
     *
     * @return self
     */
    public function send(): self
    {
        $this->isSent = (new Mailer($this->email, $this->name, $this->subject, $this->content, $this->files))->send();
        $this->save();
        return $this;
    }


    /**
     * Salva o objeto no banco de dados
     *
     * @return self
     */
    public function save(): self
    {
        $query = "CALL sp_save_mail (:pid_mail, :pdes_recipient_email, :pdes_recipient_name,
                                     :pdes_subject, :pdes_content, :pdes_files, :pis_sent)";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_mail", $this->id, \PDO::PARAM_INT);
        $stmt->bindValue("pdes_recipient_email", $this->email, \PDO::PARAM_STR);
        $stmt->bindValue("pdes_recipient_name", $this->name, \PDO::PARAM_STR);
        $stmt->bindValue("pdes_subject", $this->subject, \PDO::PARAM_STR);
        $stmt->bindValue("pdes_content", $this->content, \PDO::PARAM_STR);
        $stmt->bindValue("pdes_files", $this->files ? implode(";", $this->files) : null, \PDO::PARAM_STR);
        $stmt->bindValue("pis_sent", $this->isSent, \PDO::PARAM_BOOL);
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
        $query = "DELETE FROM tb_mails WHERE id_mail = :pid_mail";

        $stmt = (SQL::get())->prepare($query);
        $stmt->bindValue("pid_mail", $this->id, \PDO::PARAM_INT);
        $stmt->execute();

        return $this;
    }


    /**
     * Retorna todos os registros da tabela de e-mails
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
        $query = "SELECT $fields FROM tb_mails" .
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
     * Retorna o e-mail a partir do ID fornecido
     *
     * @param int $id ID do e-mail
     *
     * @static
     *
     * @return ?self
     */
    public static function loadFromId(int $id): ?self
    {
        if ($mail = parent::cache($id, __CLASS__)) {
            return $mail;
        }

        $fields = self::_getSelectFields();
        $query = "SELECT $fields
                    FROM tb_mails
                   WHERE id_mail = :pid_mail";

        $row = (SQL::get())->send($query, ["pid_mail" => $id])->fetch();
        return $row ? self::_translate($row) : null;
    }


    /**
     * Instancia a classe a partir de um vetor de argumentos
     *
     * @param array<mixed> $arguments Vetor com os dados do e-mail
     * @param ?self        $mail      Objeto instanciado
     *
     * @static
     *
     * @return self
     */
    public static function loadFromData(array $arguments, ?self $mail = null): self
    {
        $mail ??= new self();

        $mail->id = (int) ($arguments["id"] ?? 0);
        $mail->email = trim(strtolower($arguments["email"] ?? ""));
        $mail->name = trim($arguments["name"] ?? "");
        $mail->subject = trim($arguments["subject"] ?? "");
        $mail->content = trim($arguments["content"] ?? "");
        $mail->files = (array) ($arguments["files"] ?? []);
        $mail->isSent = (bool) in_array(($arguments["isSent"] ?? false), ["true", 1]);
        $mail->dateRegister = $arguments["dateRegister"] ?? date("Y-m-d H:i:s");
        $mail->dateLastChange = $arguments["dateLastChange"] ?? null;

        return $mail;
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

        (strlen($this->email) < 6 || strlen($this->email) > 128) &&
        array_push($errors, "e-mail do destinatário possui tamanho inválido");

        !filter_var($this->email, FILTER_VALIDATE_EMAIL) &&
        array_push($errors, "e-mail do destinatário inválido");

        !User::loadFromEmail($this->email) &&
        array_push($errors, "e-mail do destinatário inexistente");

        (strlen($this->name) < 2 || strlen($this->name) > 64) &&
        array_push($errors, "nome do destinatário possui tamanho inválido");

        (strlen($this->subject) < 8 || strlen($this->subject) > 256) &&
        array_push($errors, "assunto possui tamanho inválido");

        (strlen($this->content) < 16 || strlen($this->content) > 4294967295) &&
        array_push($errors, "conteúdo possui tamanho inválido");

        $count = 0;
        foreach ($this->files as $file) {
            if (!file_exists(getenv("PHP_ROOT_DIR") . $file)) {
                ++$count;
            }
        }
        $count > 0 && array_push($errors, "há $count arquivo" . ($count === 1 ? "" : "s") . " inexistente" . ($count === 1 ? "" : "s"));

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
            "email" => $this->email,
            "name" => $this->name,
            "subject" => $this->subject,
            "content" => $this->content,
            "files" => $this->files,
            "isSent" => (bool) $this->isSent,
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
