<?php

namespace Bright\mailer;

use Bright\interfaces\IMailer;
use Bright\utils\ArrayUtils;

class MailjetMailer implements IMailer
{

    function __construct()
    {
        if (!class_exists('\Mailjet\Client')) {
            throw new \Exception('Mailjet Client not found');
        }

        if (!defined('MAILJET_PUBLIC')) {
            throw new \Exception('Mailjet: Public key not set');
        }

        if (!defined('MAILJET_SECRET')) {
            throw new \Exception('Mailjet: Secret key not set');
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
        return $this->_sendMail($from, $to, $subject, $message, null, $cc, $bcc, $attachments);
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
        return $this->sendMail($from, $to, $subject, $messagePlain, $messageHtml, $cc, $bcc, $attachments);
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
     */
    public function sendMassMail($from, $to, $subject, $message, $replacements = null)
    {
        throw new \Exception('Not implemented');
    }

    private function sendMail($from, $to, $subject, $messagePlain = null, $messageHtml = null, $cc = null, $bcc = null, $attachments = null)
    {
        $mj = new \Mailjet\Client(MAILJET_PUBLIC, MAILJET_SECRET);

        $body = [
            'FromEmail' => $from,
            'Subject' => $subject,
            'To' => $this->formatRecipients($to),
            'Cc' => $this->formatRecipients($cc),
            'Bcc' => $this->formatRecipients($bcc),
        ];

        if ($messagePlain) {
            $body['Text-part'] = $messagePlain;
        }

        if ($messageHtml) {
            $body['Html-part'] = $messageHtml;
        }

        if ($attachments) {
            $body['Attachments'] = $this->formatAttachments($attachments);
        }

        $response = $mj->post(\Mailjet\Resources::$Email, ['body' => $body]);

        return $response->success();
    }

    private function formatRecipients($to)
    {
        if ($to === null) {
            return null;
        }

        if (is_string($to)) {
            $to = filter_var($to, FILTER_VALIDATE_EMAIL);
            if (!$to) {
                throw new \Exception('Mailjet: Invalid email address specified');
            }
            return $to;
        }
        $recipients = [];
        foreach ($to as $email => $name) {
            $email = filter_var($email, FILTER_VALIDATE_EMAIL);
            if (!$email) {
                throw new \Exception('Mailjet: Invalid email address specified');
            }
            $recipients[] = sprintf('%s <%s>', $name, $email);
        }

        return join(',', $recipients);
    }

    private function formatAttachments($attachments)
    {
        if(ArrayUtils::IsAssoc($attachments)) {
            $attachments = [$attachments];
        }


        $formatted = [];

        foreach ($attachments as $att) {
            $this->checkAttachment($att);
        }

        return $formatted;
    }

    private function checkAttachment($attachment)
    {
        if(!array_key_exists('Content-type', $attachment)) {
            throw new \Exception('Attachment is missing key "Content-type"');
        }
        if(!array_key_exists('Filename', $attachment)) {
            throw new \Exception('Attachment is missing key "Filename"');
        }
        if(!array_key_exists('content', $attachment)) {
            throw new \Exception('Attachment is missing key "content"');
        }

        return true;
    }
}