<?php

namespace App\Exceptions;

use RuntimeException;

class PaymentNotFoundException extends RuntimeException {
    protected $message = 'Payment not found.';
}