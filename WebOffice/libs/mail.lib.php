<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use WebOffice\Config;

include_once dirname(__DIR__) . '/libs/PHPMailer/src/Exception.php';
include_once dirname(__DIR__) . '/libs/PHPMailer/src/PHPMailer.php';
include_once dirname(__DIR__) . '/libs/PHPMailer/src/SMTP.php';

$config = new Config('config',dirname(__DIR__)."/configuration");
define('MAIL_HOST', (string)$config->read('mail','host'));
define('MAIL_USER', (string)$config->read('mail','username'));
define('MAIL_PSW', (string)$config->read('mail','password'));
define('MAIL_ENCRYPTION', (string)$config->read('mail','encryption')||PHPMailer::ENCRYPTION_SMTPS);
define('MAIL_PORT', (int)$config->read('mail','port')||465);

class Mail {
    private $mail;

    /**
     * Creates a mail object
     * @throws \InvalidArgumentException
     */
    public function __construct() {
        $this->mail = new PHPMailer(true);
        try {
            $this->mail->isSMTP();
            $this->mail->Host = MAIL_HOST;
            $this->mail->SMTPAuth = true;
            $this->mail->Username = MAIL_USER;
            $this->mail->Password = MAIL_PSW;
            $this->mail->SMTPSecure = MAIL_ENCRYPTION;
            $this->mail->Port = MAIL_PORT;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Sends an email with optional CC, BCC, and attachments.
     *
     * @param mixed $to Recipient(s). Can be:
     *                  - string: single email address
     *                  - array: list of addresses or associative arrays with 'address' and optional 'name'
     *                  Example:
     *                      'recipient@example.com'
     *                      [['address' => 'user1@example.com', 'name' => 'User One'], 'user2@example.com']
     * @param string $subject Subject of the email.
     * @param string $body HTML content of the email.
     * @param string|array|null $from Sender address. Can be:
     *                  - string: email address
     *                  - array: ['address' => 'email', 'name' => 'Name']
     *                  Defaults to the configured username if null.
     * @param string|array|null $cc CC recipient(s). Same format as `$to`.
     * @param string|array|null $bcc BCC recipient(s). Same format as `$to`.
     * @param string|array|null $attachments Files to attach. Can be:
     *                  - string: file path
     *                  - array: list of file paths or associative arrays with 'path' and optional 'fname'
     *                  Example:
     *                      '/path/to/file.pdf'
     *                      [['path' => '/path/to/file.pdf', 'fname' => 'Document.pdf'], '/path/to/image.jpg']
     * @param bool $isHTML Convert the message to HTML
     * @return bool Returns true if email was sent successfully, false otherwise.
     *
     * @example
     * // Send simple email
     * $mail->send('recipient@example.com', 'Hello', '<p>Hello World</p>');
     *
     * @example
     * // Send email with CC, BCC, and attachments
     * $mail->send(
     *   [['address' => 'user1@example.com', 'name' => 'User One']],
     *   'Subject',
     *   '<p>Body</p>',
     *   ['address' => 'sender@example.com', 'name' => 'Sender'],
     *   [['address' => 'cc@example.com']],
     *   'bcc@example.com',
     *   [['path' => '/tmp/file.pdf', 'fname' => 'Report.pdf']]
     * );
     */
    public function send(string|array $to, string $subject, string $body, string|array|null $from = null, string|array|null $cc = null, string|array|null $bcc = null, string|array|null $attachments = null, bool $isHTML=true): bool {
        try {
            // Set the sender address and name
            if ($from) {
                if (is_array($from)) $this->mail->setFrom($from['address'], $from['name'] ?? '');
                else $this->mail->setFrom($from);
            } else $this->mail->setFrom($this->mail->Username);
            // Add recipient(s)
            $this->addRecipients($to);
            // Add CC addresses if provided
            if ($cc) $this->addAddresses($cc, 'addCC');
            // Add BCC addresses if provided
            if ($bcc) $this->addAddresses($bcc, 'addBCC');
            // Add attachments if provided
            if ($attachments) $this->addAttachments($attachments);
            // Set email content
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;
            $this->mail->isHTML($isHTML);
            // Send email
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * Helper function to add recipient(s).
     */
    private function addRecipients(array $recipients): void {
        if (is_array($recipients)) {
            foreach ($recipients as $recipient) {
                if (is_array($recipient) && isset($recipient['address'])) {
                    $name = $recipient['name'] ?? '';
                    $this->mail->addAddress($recipient['address'], $name);
                } else $this->mail->addAddress($recipient);
            }
        } else $this->mail->addAddress($recipients);
    }

    /**
     * Helper function to add CC or BCC addresses.
     */
    private function addAddresses(array $addresses, string $method): void {
        if (is_array($addresses)) {
            foreach ($addresses as $address) {
                if (is_array($address) && isset($address['address'])) {
                    $name = $address['name'] ?? '';
                    $this->mail->$method($address['address'], $name);
                } else $this->mail->$method($address);
            }
        } else $this->mail->$method($addresses);
    }

    /**
     * Helper function to add attachments.
     */
    private function addAttachments(array $attachments): void {
        if (is_array($attachments)) {
            foreach ($attachments as $file) {
                if (is_array($file) && isset($file['path'])) {
                    $fname = $file['fname'] ?? '';
                    $this->mail->addAttachment($file['path'], $fname);
                } else $this->mail->addAttachment($file);
            }
        } else $this->mail->addAttachment($attachments);
    }
}