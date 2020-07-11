<?php

namespace Adnduweb\Ci4_customer\Exceptions;

class CustomerNotFoundException extends \RuntimeException implements ExceptionInterface
{
    public static function forCustomerID(int $id)
    {
        return new self(lang('Authcustomer.customerrNotFound', [$id]), 404);
    }
}
