<?php

declare(strict_types=1);

namespace KyaSms\Exceptions;

/**
 * Exception thrown when API authentication fails
 */
class AuthenticationException extends KyaSmsException
{
    /**
     * Create exception for missing API key
     *
     * @return static
     */
    public static function missingApiKey(): static
    {
        return new static(
            message: 'API key is required. Please provide a valid API key.',
            code: 401
        );
    }

    /**
     * Create exception for invalid API key
     *
     * @return static
     */
    public static function invalidApiKey(): static
    {
        return new static(
            message: 'Invalid API key. Please check your credentials.',
            code: 401
        );
    }

    /**
     * Create exception for disabled account
     *
     * @return static
     */
    public static function accountDisabled(): static
    {
        return new static(
            message: 'Your account has been disabled. Please contact support.',
            code: 403
        );
    }
}
