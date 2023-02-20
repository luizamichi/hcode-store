<?php

/**
 * PHP version 8.1.2
 *
 * @category GlobalClass
 * @package  Amichi
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */

namespace Amichi;

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Classe para envio de e-mails
 *
 * @category GlobalClass
 * @package  Amichi
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */
class Mailer
{
    /**
     * Propriedade
     *
     * @var PHPMailer $_mail Objeto de transporte de e-mail
     */
    private PHPMailer $_mail;


    /**
     * Construtor
     *
     * @param string $to      E-mail do destinatário
     * @param string $name    Nome do destinatário
     * @param string $subject Assunto do e-mail
     * @param string $content Conteúdo do corpo do e-mail
     * @param array  $files   Arquivos para serem anexados no e-mail
     *
     * @return void
     */
    public function __construct(
        private string $to,
        private string $name,
        private string $subject,
        private string $content,
        private array $files = []
    ) {
        $this->_mail = new PHPMailer();

        $this->_mail->CharSet = "UTF-8";
        $this->_mail->isSMTP();
        $this->_mail->Mailer = "smtp";
        $this->_mail->SMTPDebug = getenv("SMTP_DEBUG");
        $this->_mail->Priority = 1;

        $this->_mail->Host = getenv("SMTP_EMAIL_HOSTNAME");
        $this->_mail->Port = (int) getenv("SMTP_PORT");
        $this->_mail->SMTPSecure = getenv("SMTP_SECURE");
        $this->_mail->SMTPAuth = getenv("SMTP_AUTH") === "true";
        $this->_mail->SMTPAutoTLS = getenv("SMTP_AUTH") === "true";

        $this->_mail->Username = getenv("SMTP_EMAIL_ADDRESS");
        $this->_mail->Password = getenv("SMTP_EMAIL_PASSWORD");

        $this->_mail->setFrom(getenv("SMTP_EMAIL_ADDRESS"), getenv("SMTP_EMAIL_NAME_FROM"));

        getenv("SMTP_EMAIL_REPLY") && $this->_mail->addReplyTo(getenv("SMTP_EMAIL_REPLY"), getenv("SMTP_EMAIL_NAME_FROM"));

        $this->_mail->addAddress($to, $name);

        $this->_mail->Subject = $subject;
        $this->_mail->Body = strip_tags($content);
        $this->_mail->msgHTML($content);
        $this->_mail->AltBody = "Não foi possível renderizar o conteúdo HTML do e-mail.";

        foreach ($files as $file) {
            $this->_mail->addAttachment($file);
        }
    }


    /**
     * Envia o e-mail
     *
     * @return bool
     */
    public function send(): bool
    {
        return $this->_mail->send();
    }
}
