<?php

declare(strict_types=1);

namespace KyaSms\Exceptions;

use Exception;
use Throwable;

/**
 * Base exception for all KYA SMS SDK errors
 */
class KyaSmsException extends Exception
{
    /**
     * @var array<string, mixed>
     */
    protected array $context = [];

    /**
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @param array<string, mixed> $context
     */
    public function __construct(
        string $message = "",
        int $code = 0,
        ?Throwable $previous = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Get additional context for the exception
     *
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Create exception from API response
     *
     * @param array<string, mixed> $response
     * @param int $statusCode
     * @return static
     */
    public static function fromApiResponse(array $response, int $statusCode): static
    {
        $message = $response['errors']['general'] 
            ?? $response['message'] 
            ?? 'Unknown API error';
        
        return new static(
            message: is_array($message) ? implode(', ', $message) : (string) $message,
            code: $statusCode,
            context: $response['errors'] ?? $response
        );
    }
}
