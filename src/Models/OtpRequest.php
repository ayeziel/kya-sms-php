<?php

declare(strict_types=1);

namespace KyaSms\Models;

use InvalidArgumentException;

/**
 * OTP Request model
 */
class OtpRequest
{
    public const LANG_FR = 'fr';
    public const LANG_EN = 'en';
    public const LANG_ES = 'es';
    public const LANG_DE = 'de';

    private string $appId;
    private string $recipient;
    private string $lang;
    private ?string $code;
    private ?int $minutes;

    /**
     * @param string $appId Application ID
     * @param string $recipient Phone number or email
     * @param string $lang Language code
     */
    public function __construct(
        string $appId,
        string $recipient,
        string $lang = self::LANG_FR
    ) {
        $this->appId = $appId;
        $this->recipient = $recipient;
        $this->setLang($lang);
        $this->code = null;
        $this->minutes = null;
    }

    /**
     * Create a new OTP request
     *
     * @param string $appId
     * @param string $recipient
     * @param string $lang
     * @return static
     */
    public static function create(
        string $appId,
        string $recipient,
        string $lang = self::LANG_FR
    ): static {
        return new static($appId, $recipient, $lang);
    }

    /**
     * Set application ID
     *
     * @param string $appId
     * @return $this
     */
    public function setAppId(string $appId): static
    {
        $this->appId = $appId;
        return $this;
    }

    /**
     * Set recipient
     *
     * @param string $recipient
     * @return $this
     */
    public function setRecipient(string $recipient): static
    {
        $this->recipient = $recipient;
        return $this;
    }

    /**
     * Set language
     *
     * @param string $lang
     * @return $this
     */
    public function setLang(string $lang): static
    {
        $validLangs = [self::LANG_FR, self::LANG_EN, self::LANG_ES, self::LANG_DE];
        if (!in_array($lang, $validLangs, true)) {
            throw new InvalidArgumentException(
                'Language must be one of: ' . implode(', ', $validLangs)
            );
        }
        $this->lang = $lang;
        return $this;
    }

    /**
     * Set custom OTP code (for custom_config applications)
     *
     * @param string $code
     * @return $this
     */
    public function setCode(string $code): static
    {
        if (strlen($code) > 10) {
            throw new InvalidArgumentException('Code must be 10 characters or less');
        }
        $this->code = $code;
        return $this;
    }

    /**
     * Set expiration time in minutes
     *
     * @param int $minutes
     * @return $this
     */
    public function setMinutes(int $minutes): static
    {
        if ($minutes < 1) {
            throw new InvalidArgumentException('Minutes must be at least 1');
        }
        $this->minutes = $minutes;
        return $this;
    }

    /**
     * Set expiration to 5 minutes
     *
     * @return $this
     */
    public function expiresIn5Minutes(): static
    {
        return $this->setMinutes(5);
    }

    /**
     * Set expiration to 10 minutes
     *
     * @return $this
     */
    public function expiresIn10Minutes(): static
    {
        return $this->setMinutes(10);
    }

    /**
     * Set expiration to 15 minutes
     *
     * @return $this
     */
    public function expiresIn15Minutes(): static
    {
        return $this->setMinutes(15);
    }

    /**
     * Convert to array for API request
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'appId' => $this->appId,
            'recipient' => $this->recipient,
            'lang' => $this->lang,
        ];

        if ($this->code !== null) {
            $data['code'] = $this->code;
        }

        if ($this->minutes !== null) {
            $data['minutes'] = $this->minutes;
        }

        return $data;
    }

    // Getters

    public function getAppId(): string
    {
        return $this->appId;
    }

    public function getRecipient(): string
    {
        return $this->recipient;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getMinutes(): ?int
    {
        return $this->minutes;
    }
}
