<?php
/**
 * File: CardManager.php.
 * Author: Yosvel Reyes Fonfria <yosvel.fonfria@gmail.com>
 * Created At: 4/30/15 10:27 AM
 *
 */

class CardManager
{

    private $connection;

    static public function hashCard($number, $expirationMonth, $expirationYear, $cvv2)
    {
        return hash('sha512', $number . $expirationMonth . $expirationYear . $cvv2);
    }

    public function __construct()
    {
        $this->connection = new PDO(PDO_MYSQL_DSN, PDO_MYSQL_USERNAME, PDO_MYSQL_PASSWORD);
    }

    /**
     * Saves the credit card data to local database for furthere procesing
     *
     * @param $cardData array the array expects to have to following indexes
     *
     *  number   the credit card number (in raw format)
     *
     *
     */
    public function saveCreditCard($cardData)
    {
        $sqlt = <<<EOS
INSERT INTO credit_cards (`uid`, `card_number`, `card_type`, `expiry_month`, `expiry_year`, `cvv2`, `name`, `surname`, `street`, `city`, `country_code`, `zipcode`, `hashed`)
VALUES('%d', aes_encrypt('%s', UNHEX('%s')), '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
EOS;

        $cardData = $this->normalize($cardData);

        $insertSql = sprintf(
            $sqlt,
            $_SESSION['id_client'],
            $cardData['number'],
            SKEY,
            $cardData['card_type'],
            $cardData['expiry']['month'],
            $cardData['expiry']['year'],
            $cardData['cvv2'],
            $cardData['cc_name'],
            $cardData['cc_surname'],
            $cardData['street'],
            $cardData['city'],
            $cardData['country_code'],
            $cardData['zipcode'],
            $cardData['hashed']
            );

        $rows = $this->connection->exec($insertSql);
        return $this->connection->lastInsertId();
    }


    public function retrieveCreditCard($uid)
    {
        $result = array();
        $sql = <<<EOS
SELECT id, uid, aes_decrypt(card_number, UNHEX('%s')) as number, card_type, expiry_month, expiry_year, cvv2, name, surname, street, city, country_code, zipcode, hashed from credit_cards where uid='%s'
EOS;


        $sql = sprintf($sql, SKEY, $uid);

        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        While($r = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            $result[] = $r;
        }


        return $result;

    }

    public function getById($id)
    {

        $sql = <<<EOS
SELECT id, uid, aes_decrypt(card_number, UNHEX('%s')) as number, card_type, expiry_month, expiry_year, cvv2, name, surname, street, city, country_code, zipcode, hashed from credit_cards where id='%d'
EOS;

        $sql = sprintf($sql, SKEY, $id);

        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;

    }

    public function isCreditCardSaved($cardData)
    {
        $cardData = $this->normalize($cardData);
        $hash = self::hashCard($cardData['number'], $cardData['expiry']['month'], $cardData['expiry']['year'], $cardData['cvv2']);

        $stmt = $this->connection->prepare('SELECT COUNT(id) as counter FROM credit_cards WHERE hashed=:hash');
        $stmt->execute(array('hash' => $hash));
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result && 0 != $result['counter'];
    }


    private function normalize($cardData)
    {
        $normalized = $cardData;
        //unset($normalized['expiry']);

        //$expiration = explode("/", $cardData['expiry']);

        //$normalized['expiry_month'] = $expiration[0];
        //$normalized['expiry_year'] = $expiration[1];
        $normalized['hashed'] = self::hashCard($normalized['number'], $normalized['expiry']['month'], $normalized['expiry']['year'], $normalized['cvv2']);

        return $normalized;

    }



}