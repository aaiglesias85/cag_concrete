<?php
/**
 * File: Mailer.php.
 * Author: Yosvel Reyes Fonfria <yosvel.fonfria@gmail.com>
 * Created At: 5/6/15 2:39 PM
 *
 */

require_once __DIR__ . '/../inc/class.phpmailer.php';
require_once __DIR__ . '/VoipswitchManager.php';
require_once __DIR__ . '/Logger.php';

class Mailer 
{
    private $transport;

    public function __construct()
    {
        $this->transport = new PHPMailer();
        $this->transport->IsSMTP();

        $this->transport->Host      = SMTP_SERVER;
        $this->transport->SMTPAuth  = true;
        $this->transport->Port      = SMTP_PORT;
        $this->transport->Username  = SMTP_USERNAME;
        $this->transport->Password  = SMTP_PASSWORD;
        $this->transport->From      = MAIL_FROM;
        $this->transport->FromName  = MAIL_FROM_NAME;
    }

    public function sendNotificationMail($idClient, $amount)
    {
        $clientRecord = VoipswitchManager::getInfoRecord($idClient);

        //do not throw exceptions if the record coud not be found simply ignore the email send
        if($clientRecord)
        {
            $email  = $clientRecord['EMail'];
            $name   = $clientRecord['Name'];
            $surname = $clientRecord['LastName'];

            //if any component missing then do not send the email
            if($email && $name && $surname)
            {
                $conn = new PDO(PDO_MYSQL_DSN, PDO_MYSQL_USERNAME, PDO_MYSQL_PASSWORD);
                $stmt = $conn->prepare("SELECT title, `value`, align FROM texts WHERE `key`=:key AND website=0");
                $stmt->execute(array('key' => 'chargeEmail'));
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if($result && !empty($result['title']))
                {
                    $date = new DateTime();
                    $fullname = sprintf("%s %s", trim($name), trim($surname));

                    $subj = str_replace("{fullname}", $fullname, $result['title']);
                    $body = str_replace("{datetime}", $date->format('Y-m-d'), $result['value']);
                    $body = str_replace("{amount}", $amount, $body);
                    $body = str_replace("{fullname}", $fullname, $body);

                    if(!$this->sendMail($subj, $body, $email, $fullname))
                    {
                        Logger::write(sprintf('Error sending email to %s for amount', $email, $amount));
                        return false;
                    }

                    return true;
                }
            }

        }

        return false;
    }

    public function notifyStaff($idClient, $amount)
    {
        $record  = VoipswitchManager::getMainRecord(idClient);

        $subject    = sprintf('nueva recarga al %s', $record['login']);
        $body       = sprintf('se recibio una nueva recarga al %s por un valor de %f', $record['login'], $amount);

        if(!$this->sendMail($subject, $body, 'info@llamayahorra.com', 'administrator'))
        {
            Logger::write(sprintf('Error sending email to %s for amount', 'info@llamayahorra.com', $amount));
            return false;
        }

        return true;
    }

    private function sendMail($subject, $body, $to, $name="")
    {

        //just in case the mailer object need to be different (wont study PHPMailer)
        //$transport = clone $this->transport;

        $bodyHTML = nl2br($body);
        $bodyHTML = str_replace("><br />", ">", $bodyHTML);

        $bodyTEXT = str_replace(array("<br />"), array("\n"), $body);
        $bodyTEXT = strip_tags($bodyTEXT);

        $this->transport->Subject    = utf8_decode( $subject );
        $this->transport->AltBody    = utf8_decode($bodyTEXT);

        $this->transport->MsgHTML(utf8_decode($bodyHTML));
        $this->transport->AddAddress($to, utf8_decode($name));

        return $this->transport->Send();
    }
}