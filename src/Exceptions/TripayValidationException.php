<?php

namespace Nekoding\Tripay\Exceptions;

use Illuminate\Validation\ValidationException;

class TripayValidationException extends ValidationException
{

    public function getErrorBags(): ?array
    {
        return $this->errors();
    }
}
