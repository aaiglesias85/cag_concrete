<?php
/**
 * File: Validator.php.
 * Author: Yosvel Reyes Fonfria <yosvel.fonfria@gmail.com>
 * Created At: 4/30/15 2:16 PM
 *
 */

class Validator
{

    //no instances allowed outside the class
    private function __construct(){}

    private function validateExistingCard(array $form, $cvv2)
    {
        $errors = array();

        //accept validation
        if(!isset($form['accept']) || false == $form['accept'])
        {
            $errors['accept'] = 'Debe aceptar el cargo por transacción de lo contrario no realice el pago';
        }

        //ccv  validation
        if(isset($form['cvv2']) && "" != $form['cvv2'])
        {
            $match = preg_match('/^(\d{3}|\d{4})$/', $form['cvv2']);

            if(0 === $match || false === $match)
            {
                $errors['cvv2'] = 'El ccv es incorrecto';
            }

            if($cvv2 != $form['cvv2'])
                $errors['cvv2'] = 'El ccv no corresponde con el guardado previamente';
        }
        else
            $errors['cvv2'] = 'Entre el cvv2';


        //amount  validation
        if(isset($form['amount']) && "" != $form['amount'])
        {
            //$match = preg_match('/^\d{2}(\.\d{2})?$/', $form['amount']);
            if(0 === $match || false === $match)
            {
                $errors['amount'] = 'El monto a pagar es incorrecto';
            }
            else{
                $val = floatval($form['amount']);
                if($val < 1 || $val > 30)
                    $errors['amount'] = 'Entre un valor entre 10.00 y 30.00';
            }
        }
        else
            $errors['amount'] = 'Entre el monto a pagar';


        return $errors;
    }

    private function validateNewCard(array $form)
    {
        $errors = array();

        //credit card number length & format validation
        if(isset($form['number']) && "" != $form['number'])
        {
            $match = preg_match('/^(\d{13}|\d{16}|\d{12}|\d{14}|\d{19}|\d{15})$/', $form['number']);
            if(0 === $match || false === $match)
            {
                $errors['number'] = 'El número de tarjeta es inválido';
            }
        }
        else
            $errors['number'] = 'Entre el número de la tarjeta';

        //expiry date validation.
        if(!empty($form['expiry']['month']) || !empty($form['expiry']['year']))
        {

            $match = preg_match('/^\d{4}$/', $form['expiry']['year']);
            if(0 === $match || false === $match)
            {
                $errors['expiry'] = 'La fecha de vencimiento es inválida, ej:  10/2014';
            }

            $v = intval($form['expiry']['month']);

            if($v < 1 || $v > 12 )
                $errors['expiry'] = 'La fecha de vencimiento es inválida, ej: 10/2014';

        }
        else
        {
            $errors['expiry'] = 'Entre la fecha de vencimiento';
        }




        //ccv  validation
        if(isset($form['cvv2']) && "" != $form['cvv2'])
        {
            $match = preg_match('/^(\d{3}|\d{4})$/', $form['cvv2']);
            if(0 === $match || false === $match)
            {
                $errors['cvv2'] = 'El ccv es incorrecto';
            }
        }
        else
            $errors['cvv2'] = 'Entre el cvv2';


        //cc_name  validation
        if(isset($form['cc_name']) && "" != $form['cc_name'])
        {
            $match = preg_match('/^([a-z|A-Z]* ?)*$/', $form['cc_name']);
            if(0 === $match || false === $match)
            {
                $errors['cc_name'] = 'El nombre que aparece en la tarjeta es incorrecto';
            }
        }
        else
            $errors['cc_name'] = 'Entre el nombre que aparece en la tarjeta';


        //cc_surname  validation
        if(isset($form['cc_surname']) && "" != $form['cc_surname'])
        {
            $match = preg_match('/^([a-z|A-Z]* ?)*$/', $form['cc_surname']);
            if(0 === $match || false === $match)
            {
                $errors['cc_surname'] = 'El nombre que aparece en la tarjeta es incorrecto';
            }
        }
        else
            $errors['cc_surname'] = 'Entre el nombre que aparece en la tarjeta';


        //street  validation
        if(isset($form['street']) && "" != $form['street'])
        {
            $match = preg_match('/^([a-zA-Z0-9|#|\/|\\|\.]* ?)*$/', $form['street']);
            if(0 === $match || false === $match)
            {
                $errors['street'] = 'La dirección es incorrecta';
            }
        }
        else
            $errors['street'] = 'Entre la dirección';

        //city validation
        if(isset($form['city']) && "" != $form['city'])
        {
            $match = preg_match('/^([a-z|A-Z]* ?)*$/', $form['city']);
            if(0 === $match || false === $match)
            {
                $errors['city'] = 'La ciudad es incorrecta';
            }
        }
        else
            $errors['city'] = 'Entre la ciudad';

        //state  validation
        if(isset($form['state']) && "" != $form['state'])
        {
            $match = preg_match('/^([a-z|A-Z]* ?)*$/', $form['state']);
            if(0 === $match || false === $match)
            {
                $errors['state'] = 'El estado es incorrecto';
            }
        }
        else
            $errors['state'] = 'Entre el estado';

        //zipcode validation
        if(isset($form['zipcode']) && "" != $form['zipcode'])
        {
            $match = preg_match('/^[0-9]*-?[0-9]*$/', $form['zipcode']);
            if(0 === $match || false === $match)
            {
                $errors['zipcode'] = 'El código postal es incorrecto';
            }
        }
        else
            $errors['zipcode'] = 'Entre el código postal';

        //accept validation
        if(!isset($form['accept']) || false == $form['accept'])
        {
                $errors['accept'] = 'Debe aceptar el cargo por transacción de lo contrario no realice el pago';
        }

        //amount  validation
        if(isset($form['amount']) && "" != $form['amount'])
        {
            //$match = preg_match('/^\d{2}(\.\d{2})?$/', $form['amount']);
            if(0 === $match || false === $match)
            {
                $errors['amount'] = 'El monto a pagar es incorrecto';
            }
            else{
                $val = floatval($form['amount']);
                if($val < 1 || $val > 30)
                    $errors['amount'] = 'Entre un valor entre 10.00 y 30.00';
            }
        }
        else
            $errors['amount'] = 'Entre el monto a pagar';


        return $errors;
    }

    public static function validate(array $form, $cvv2 = null)
    {
        $instance = new self();

        if("" == $form['card'])
            return $instance->validateNewCard($form);


        return $instance->validateExistingCard($form, $cvv2);


    }

    public static function validateCvv2($needle, $stored)
    {
        return $needle == $stored;
    }
}