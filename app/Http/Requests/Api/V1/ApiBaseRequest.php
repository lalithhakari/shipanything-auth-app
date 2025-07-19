<?php

namespace App\Http\Requests\Api\V1;

use App\Http\Traits\Responses\V1\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ApiBaseRequest extends FormRequest
{
    use ApiResponse;

    /**
     * Default response structure for validation errors.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::errorResponse(
                message: $validator->errors()->first(),
                errors: $validator->errors()->toArray()
            )
        );
    }
}
