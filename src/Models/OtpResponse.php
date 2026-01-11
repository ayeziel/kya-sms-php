<?php

declare(strict_types=1);

namespace KyaSms\Models;

/**
 * OTP Response model
 */
class OtpResponse
{
    private bool $success;
    private ?string $key;
    private ?string $expireAt;
    private ?array $errors;

    /**
     * @param array<string, mixed> $response
     */
    public function __construct(array $response)
    {
        $this->success = ($response['reason'] ?? '') === 'success';
        $this->key = $response['key'] ?? null;
        $this->expireAt = $response['expire_at'] ?? null;
        $this->errors = $response['errors'] ?? null;
    }

    /**
     * Create from API response
     *
     * @param array<string, mixed> $response
     * @return static
     */
    public static function fromResponse(array $response): static
    {
        return new static($response);
    }

    /**
     * Check if the request was successful
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Get the OTP reference key
     * 
     * This key is used to verify the OTP later
     *
     * @return string|null
     */
    public function getKey(): ?string
    {
        return $this->key;
    }

    /**
     * Get expiration timestamp
     *
     * @return string|null
     */
    public function getExpireAt(): ?string
    {
        return $this->expireAt;
    }

    /**
     * Get errors if any
     *
     * @return array<string, mixed>|null
     */
    public function getErrors(): ?array
    {
        return $this->errors;
    }

    /**
     * Check if there are errors
     *
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Convert to array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'key' => $this->key,
            'expire_at' => $this->expireAt,
            'errors' => $this->errors,
        ];
    }
}
