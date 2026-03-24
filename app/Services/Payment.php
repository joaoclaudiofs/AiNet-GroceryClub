<?php

namespace App\Services;

class Payment
{







    public static function payWithVisa($card_number, $cvc_code)
    {
        $card_number = filter_var($card_number, FILTER_VALIDATE_INT);
        if (!$card_number) {
            return false;
        }
        if (($card_number<1000000000000000) || ($card_number>9999999999999999))  {
            return false;
        }
        $cvc_code = filter_var($cvc_code, FILTER_VALIDATE_INT);
        if (!$cvc_code) {
            return false;
        }
        if (($cvc_code<100) || ($cvc_code>999))  {
            return false;
        }
        return ($card_number % 10 != 2) && ($cvc_code % 10 != 2);
    }




    public static function payWithPaypal($email_address)
    {
        if (!filter_var($email_address, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        return str_ends_with($email_address, '.pt') || str_ends_with($email_address, '.com');
    }




    public static function payWithMBway($phone_number)
    {
        $phone_number = filter_var($phone_number, FILTER_VALIDATE_INT);
        if (!$phone_number) {
            return false;
        }
        if (($phone_number<900000000) || ($phone_number>999999999))  {
            return false;
        }
        return $phone_number % 10 != 2;
    }
}
