<?php

/**
 * Wrapper class to send e-mails with
 * Version history:
 * 2.2 20120419
 * - Implements sendHtmlMail
 * @author Ids Klijnsma - Fur
 * @version 2.2
 * @package Bright
 * @subpackage utils
 */
class Mailer extends Permissions implements \Bright\interfaces\IMailer
{

    /**
     * @var \Bright\interfaces\IMailer
     */
    private $mailer;

    function __construct($forceCache = false) {
        parent::__construct();

        if(MAIL_MODE == BaseConstants::MAILER_MODE_SWIFT) {
            $this -> mailer = new \Bright\mailer\SwiftMailer();

        } else {
            $this -> mailer = new \Bright\mailer\MailjetMailer();
        }
    }

    /**
     * Sends an e-mail
     * @since 2.1 Added cc, bcc, attachments
     * @param string $from The senders e-mail address
     * @param mixed $to The receivers e-mail address. Can be string, or an array of key value pairs, where the key is the email address and the value is the name
     * @param string $subject The subject of the e-mail
     * @param string $message The message of the e-mail
     * @param mixed $cc The receivers e-mail address. Can be string, or an array of key value pairs, where the key is the email address and the value is the name
     * @param mixed $bcc The receivers e-mail address. Can be string, or an array of key value pairs, where the key is the email address and the value is the name
     * @param array $attachments An array of attachments
     * @return boolean True when successful
     */
    public function sendPlainMail($from, $to, $subject, $message, $cc = null, $bcc = null, $attachments = null)
    {
        return $this->mailer->sendPlainMail($from, $to, $subject, $message, $cc, $bcc, $attachments);
    }

    /**
     * Sends an e-mail
     * @since 2.1 Added cc, bcc, attachments
     * @param string $from The senders e-mail address
     * @param mixed $to The receivers e-mail address. Can be string, or an array of key value pairs, where the key is the email address and the value is the name
     * @param string $subject The subject of the e-mail
     * @param string $messageHtml
     * @param null $messagePlain
     * @param mixed $cc The receivers e-mail address. Can be string, or an array of key value pairs, where the key is the email address and the value is the name
     * @param mixed $bcc The receivers e-mail address. Can be string, or an array of key value pairs, where the key is the email address and the value is the name
     * @param array $attachments An array of attachments
     * @return boolean True when successful
     */
    public function sendHtmlMail($from, $to, $subject, $messageHtml, $messagePlain = null, $cc = null, $bcc = null, $attachments = null)
    {
        return $this->mailer->sendHtmlMail($from, $to, $subject, $messageHtml, $messagePlain, $cc, $bcc, $attachments);
    }

    /**
     * Sends a mailing to a list of email addresses
     *
     * @param string $from The sending email address
     * @param array $to An array of email addresses
     * @param string $subject The e-mails subject
     * @param string $message The message to send
     * @param array $replacements An array of replacements
     * @throws \Exception
     * @todo Describe replacements;
     */
    public function sendMassMail($from, $to, $subject, $message, $replacements = null)
    {
        return $this->mailer->sendMassMail($from, $to, $subject, $message, $replacements);
    }
}
