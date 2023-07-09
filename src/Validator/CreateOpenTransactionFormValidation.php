<?php

namespace Nekoding\Tripay\Validator;

use Illuminate\Support\Facades\Validator;
use Nekoding\Tripay\Exceptions\TripayValidationException;

class CreateOpenTransactionFormValidation implements Validation
{
    /**
     * @param array $data
     * @return array
     * @throws TripayValidationException
     */
    public static function validate(array $data): array
    {
        $validator = Validator::make($data, [
            'method'                    => 'bail|required|string',
            'merchant_ref'              => 'bail|nullable|string',
            'customer_name'             => 'bail|nullable|string',
            'signature'                 => 'bail|required|string'
        ]);

        if ($validator->fails()) {
            throw new TripayValidationException($validator);
        }

        return  $validator->validate();
    }
}
