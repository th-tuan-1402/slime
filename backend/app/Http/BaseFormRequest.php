<?php

declare(strict_types=1);

namespace App\Http;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Normalizer;

/**
 * Base form request for all API requests.
 *
 * Provides standardized JSON error responses, input sanitization,
 * default authorization, and a toDto() pattern for typed data transfer.
 */
abstract class BaseFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Default: require authenticated user. Override in subclasses for
     * public endpoints or custom authorization logic.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Handle a failed validation attempt.
     * Throws a JSON response with standardized error format.
     *
     * @param Validator $validator The validator instance that failed.
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            new JsonResponse(
                data: [
                    'message' => 'Validation failed.',
                    'errors'  => $validator->errors()->toArray(),
                ],
                status: 422,
            )
        );
    }

    /**
     * Prepare the data for validation.
     * Trims whitespace from strings and normalizes Unicode to NFC form.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(
            $this->sanitizeInput($this->all())
        );
    }

    /**
     * Convert validated request data to a typed DTO.
     * Concrete form requests must implement this method.
     *
     * @return object The DTO built from validated data.
     */
    abstract public function toDto(): object;

    /**
     * Recursively sanitize input values.
     * - Strings: trim whitespace, normalize Unicode to NFC
     * - Arrays: recurse into nested values
     * - Other types: pass through unchanged
     *
     * @param array<string, mixed> $data Raw input data.
     * @return array<string, mixed> Sanitized data.
     */
    private function sanitizeInput(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $trimmed = trim($value);
                $sanitized[$key] = Normalizer::isNormalized($trimmed, Normalizer::FORM_C)
                    ? $trimmed
                    : Normalizer::normalize($trimmed, Normalizer::FORM_C);
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeInput($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}
