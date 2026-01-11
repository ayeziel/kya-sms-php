<?php

declare(strict_types=1);

namespace KyaSms\Exceptions;

/**
 * Exception thrown for API-specific errors
 */
class ApiException extends KyaSmsException
{
    /**
     * @var int|null
     */
    protected ?int $httpStatusCode;

    /**
     * @param string $message
     * @param int $code
     * @param int|null $httpStatusCode
     * @param array<string, mixed> $context
     */
    public function __construct(
        string $message,
        int $code = 0,
        ?int $httpStatusCode = null,
        array $context = []
    ) {
        parent::__construct($message, $code, null, $context);
        $this->httpStatusCode = $httpStatusCode;
    }

    /**
     * Get HTTP status code
     *
     * @return int|null
     */
    public function getHttpStatusCode(): ?int
    {
        return $this->httpStatusCode;
    }

    /**
     * Get status code (alias for getHttpStatusCode)
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->httpStatusCode ?? $this->code;
    }

    /**
     * Create exception for rate limiting
     *
     * @param int $retryAfter Seconds to wait before retrying
     * @return static
     */
    public static function rateLimited(int $retryAfter = 60): static
    {
        return new static(
            message: "Rate limit exceeded. Please retry after {$retryAfter} seconds.",
            code: 429,
            httpStatusCode: 429,
            context: ['retry_after' => $retryAfter]
        );
    }

    /**
     * Create exception for insufficient balance
     *
     * @return static
     */
    public static function insufficientBalance(): static
    {
        return new static(
            message: 'Insufficient balance. Please recharge your wallet.',
            code: 400,
            httpStatusCode: 400
        );
    }

    /**
     * Create exception for invalid sender ID
     *
     * @param string $senderId
     * @return static
     */
    public static function invalidSenderId(string $senderId): static
    {
        return new static(
            message: "Invalid sender ID: {$senderId}. Please use an approved sender ID.",
            code: 400,
            httpStatusCode: 400,
            context: ['sender_id' => $senderId]
        );
    }

    /**
     * Create exception for server error
     *
     * @param string $message
     * @return static
     */
    public static function serverError(string $message = 'Internal server error'): static
    {
        return new static(
            message: $message,
            code: 500,
            httpStatusCode: 500
        );
    }

    /**
     * Create exception for network error
     *
     * @param string $message
     * @return static
     */
    public static function networkError(string $message): static
    {
        return new static(
            message: "Network error: {$message}",
            code: 0,
            httpStatusCode: null
        );
    }
}
