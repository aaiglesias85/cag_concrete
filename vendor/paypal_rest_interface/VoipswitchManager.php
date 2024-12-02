<?php
/**
 * File: VoipswitchManager.php.
 * Author: Yosvel Reyes Fonfria <yosvel.fonfria@gmail.com>
 * Created At: 5/5/15 2:50 PM
 *
 */

class VoipswitchManager
{

    private $connection;
    static private $instance = null;


    static public function newInstance()
    {
        if(null == self::$instance)
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->connection = new PDO(PDO_VOIPSWITCH_DSN, PDO_VOIPSWITCH_USERNAME, PDO_VOIPSWITCH_PASSWORD);
    }


    public function execDirectCardPayment($amount, $clientId, $transactionId)
    {

        $stmt = $this->connection->prepare('SELECT account_state FROM clientsshared WHERE id_client=:clientId');
        $stmt->execute(array('clientId' => $clientId));
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if($result && $actualAmount = $result['account_state'])
        {

            $description = "Saldo al #: " . $_SESSION['phone'] . " from IP: " . $_SESSION['remote_ip'] . " via credit card";

            $sql = sprintf(
                "INSERT INTO payments(id_client, money, data, actual_value, client_type, description)VALUES('%d', '%f', NOW(), '%f', '32', '%s')",
                $clientId,
                $amount,
                $actualAmount,
                $description . sprintf(" Paypal transaction (%s)", $transactionId)
            );

            $rows = $this->connection->exec($sql);

            $sql = sprintf(
                "UPDATE clientsshared SET account_state='%f' WHERE id_client='%d'",
                $amount + $actualAmount,
                $clientId

            );

            $rows = $this->connection->exec($sql);

            return true;
        }

        throw new \Exception('Unable to retrieve the current balance');

    }

    static public function getInfoRecord($clientId)
    {
        self::newInstance();

        $stmt = self::$instance->connection->prepare("SELECT * FROM invoiceclients WHERE IDClient=:id");
        $stmt->execute(array('id' => $clientId));
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }

    static public function getMainRecord($clientId)
    {
        self::newInstance();

        $stmt = self::$instance->connection->prepare("SELECT * FROM clientsshared WHERE id_client=:id AND type=531");
        $stmt->execute(array('id' => $clientId));
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
}