<?php
/**
 * File: PaypalManager.php.
 * Author: Yosvel Reyes Fonfria <yosvel.fonfria@gmail.com>
 * Created At: 4/29/15 10:37 AM
 *
 */

require_once __DIR__ . '/bootstrap.php';

use PayPal\Api\CreditCard;

class PaypalManager
{

    private $lastError;
    private $rawLastError;
    private $successCallable;
    private $failureCallable;

    /*public function saveCreditCard(array $cardDetails)
    {
        $card = new CreditCard();

        //check if the card is inserted already
        try{
            $conn = new PDO(PDO_MYSQL_DSN, PDO_MYSQL_USERNAME, PDO_MYSQL_PASSWORD);

            $sql = sprintf("SELECT COUNT(id) as count FROM credit_cards WHERE card_id=aes_encrypt(':number', UNHEX('%s'))", SKEY);
            $stmt = $conn->prepare($sql);
            $stmt->execute(array('number' => $cardDetails['number']));
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            //if empty add the credit card, otherwise ignore as already saved,
            if(0 == intval($result['count']))
            {
                $cardDetails = $this->normalize($cardDetails);

                $card->setType($cardDetails['type'])
                    ->setNumber($cardDetails['number'])
                    ->setExpireMonth($cardDetails['expire_month'])
                    ->setExpireYear($cardDetails['expire_year'])
                    ->setCvv2($cardDetails['cvv2'])
                    ->setFirstName($cardDetails['cc_name'])
                    ->setLastName($cardDetails['cc_surname']);

                $addr = new \PayPal\Api\Address();
                $addr->setLine1($cardDetails['street'])
                    ->setCity($cardDetails['city'])
                    ->setState($cardDetails['state'])
                    ->setCountryCode($cardDetails['country'])
                    ->setPostalCode($cardDetails['zipcode']);

                $card->setBillingAddress($addr);

                $card->create(getApiContext());

                $insertSql = sprintf("INSERT INTO credit_cards (`uid`, `card_id`, `card_number`) VALUES('%d', aes_encrypt('%s', UNHEX('%s')), aes_encrypt('%s', UNHEX('%s')))", $_SESSION['id_client'], $card->getId(), SKEY, $card->getNumber(), SKEY);
                $rows = $conn->exec($insertSql);
            }

        }
        catch(PDOException $e)
        {
            throw new Exception('Could not check the credit card against our db');
        }
        catch(\PayPal\Exception\PayPalConnectionException $e)
        {
            die($e);
        }

        return $card->getId();
    }*/


    public function __construct($successCallable, $failureCallable)
    {
        assert(is_callable($successCallable));
        assert(is_callable($failureCallable));

        $this->successCallable  = $successCallable;
        $this->failureCalllable = $failureCallable;
    }


    private function normalize($cardData)
    {
        $normalized = $cardData;
        unset($normalized['expiry']);

        if(isset($cardData['expiry']))
        {
            $normalized['expiry_month'] = $cardData['expiry']['month'];
            $normalized['expiry_year'] = $cardData['expiry']['year'];
        }


        return $normalized;

    }


    /**
     * Try to make a direct credit card payment via paypal.
     * @param array $paymentData array the credit card payment data
     * @return bool|mixed Return value is false if payment exception is received, otherwise the control of return value is passed to caller routines via callback functions.
     */
    public function directCardPayment(array $paymentData, $fee = 0)
    {

        $this->lastError = $this->rawLastError = null;
        $paymentData = $this->normalize($paymentData);

        $card = new CreditCard();
        $card
            ->setType($paymentData['card_type'])
            ->setNumber($paymentData['number'])
            ->setExpireMonth($paymentData['expiry_month'])
            ->setExpireYear($paymentData['expiry_year'])
            ->setCvv2($paymentData['cvv2'])
            ->setFirstName($paymentData['cc_name'])
            ->setLastName($paymentData['cc_surname']);


        $fi = new \PayPal\Api\FundingInstrument();
        $fi->setCreditCard($card);

        $payer = new \PayPal\Api\Payer();

        $payer->setPaymentMethod('credit_card')
            ->setFundingInstruments(array($fi));

        $item = new \PayPal\Api\Item();

        $item
            ->setName('Account top up')
            ->setDescription('Llama y ahorra account topup')
            ->setCurrency('USD')
            ->setQuantity(1)
            ->setTax($fee)
            ->setPrice(floatval($paymentData['amount']));

        $itemList = new \PayPal\Api\ItemList();
        $itemList->setItems(array($item));

        $details = new \PayPal\Api\Details();

        $details->setTax($fee)
            ->setShipping(0)
            ->setSubtotal(floatval($paymentData['amount']));

        $amount = new \PayPal\Api\Amount();


        $amount
            ->setCurrency('USD')
            ->setTotal(floatval($paymentData['amount']) + $fee)
            ->setDetails($details)
        ;

        $transaction = new \PayPal\Api\Transaction();
        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription('Account topup')
            ->setInvoiceNumber(uniqid());


        $payment = new \PayPal\Api\Payment();
        $payment->setIntent('sale')
            ->setPayer($payer)
            ->setTransactions(array($transaction));

        try
        {
            $payment->create(getApiContext());

            if('approved' == $payment->getState())
            {
                return call_user_func($this->successCallable, $payment);;
            }
            else{
                $this->lastError = 'the payment has failed';
                return call_user_func($this->failureCallable, $payment);
            }
        }
        catch(\PayPal\Exception\PayPalConnectionException $e)
        {
            $this->decodeError($e);
        }
        catch(Exception $e)
        {
            $this->lastError    = $e->getMessage();
        }

        return false;
    }

    private function decodeError(\PayPal\Exception\PayPalConnectionException $e)
    {
        $this->rawLastError = $e->getData();
        $raw = json_decode($e->getData(), true);
        $lastError  = "";

        if($raw)
        {
            if($raw['name'] != 'INTERNAL_SERVICE_ERROR')
            {
                if(isset($raw['details']))
                {
                    if(is_array($raw['details']))
                    {
                        foreach($raw['details'] as $cause)
                        {
                            if(isset($cause['issue']))
                                $lastError .= $cause['issue'] . "\n";
                        }
                    }
                }
                elseif(isset($raw['error_description']))
                        $lastError = $raw['error_description'];
                else
                    $lastError = 'Unknown error happened, please contact technical support';
            }
            else{

                $lastError .= "Unable to process payment, maybe your card number does not pass the Luhn check";
            }
        }
        else
            $lastError .= $e->getMessage();


        $this->lastError = $lastError;

    }

    public function getLastError()
    {
        return $this->lastError;
    }


    public function getRawError()
    {
        return $this->rawLastError;
    }
}