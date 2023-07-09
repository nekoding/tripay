<?php

namespace Nekoding\Tripay\Validator;

use Illuminate\Support\Facades\Validator;
use Nekoding\Tripay\Exceptions\TripayValidationException;

class CreateCloseTransactionFormValidation implements Validation
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
            'merchant_ref'              => 'bail|required|string',
            'amount'                    => 'bail|required|int',
            'customer_name'             => 'bail|required|string',
            'customer_email'            => 'bail|required|string',
            'customer_phone'            => 'bail|required|string',
            'order_items'               => 'bail|required|array',
            'order_items.*.sku'         => 'bail|nullable|string',
            'order_items.*.price'       => 'bail|required|int',
            'order_items.*.name'        => 'bail|required|string',
            'order_items.*.quantity'    => 'bail|required|int',
            'order_items.*.product_url' => 'bail|nullable|string',
            'order_items.*.image_url'   => 'bail|nullable|string',
            'callback_url'              => 'bail|nullable|string',
            'return_url'                => 'bail|nullable|string',
            'expired_time'              => 'bail|nullable|int',
            'signature'                 => 'bail|required|string'
        ]);

        if ($validator->fails()) {
            throw new TripayValidationException($validator);
        }

        return  $validator->validate();
    }
}
