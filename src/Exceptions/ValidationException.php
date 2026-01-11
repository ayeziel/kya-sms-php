<?php

declare(strict_types=1);

namespace KyaSms\Exceptions;

/**
 * Exception thrown when request validation fails
 */
class ValidationException extends KyaSmsException
{
    /**
     * @var array<string, array<string>|string>
     */
    protected array $errors = [];

    /**
     * @param string $message
     * @param array<string, array<string>|string> $errors
     */
    public function __construct(string $message, array $errors = [])
    {
        parent::__construct($message, 422, null, $errors);
        $this->errors = $errors;
    }

    /**
     * Get validation errors
     *
     * @return array<string, array<string>|string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Check if a specific field has errors
     *
     * @param string $field
     * @return bool
     */
    public function hasError(string $field): bool
    {
        return isset($this->errors[$field]);
    }

    /**
     * Get error for a specific field
     *
     * @param string $field
     * @return array<string>|string|null
     */
    public function getError(string $field): array|string|null
    {
        return $this->errors[$field] ?? null;
    }

    /**
     * Create from API validation response
     *
     * @param array<string, mixed> $response
     * @return static
     */
    public static function fromApiResponse(array $response): static
    {
        $errors = $response['errors'] ?? [];
        $message = $response['message'] ?? 'Validation failed';

        return new static($message, $errors);
    }
}
