<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoPrivateIpRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $domain = parse_url($value, PHP_URL_HOST);

        if (filter_var($domain, FILTER_VALIDATE_IP) === false) {
            // Hostname is not an IP address
            return;
        }

        if (filter_var($domain, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            // Hostname contains an IP address from the private or reserved ranges
            $fail(trans('validation.no_private_ip'));
        }
    }
}
