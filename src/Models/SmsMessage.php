<?php

declare(strict_types=1);

namespace KyaSms\Models;

use InvalidArgumentException;

/**
 * SMS Message model
 */
class SmsMessage
{
    public const TYPE_TEXT = 'text';
    public const TYPE_FLASH = 'flash';

    public const SOURCE_API = 2;
    public const SOURCE_WEB = 1;
    public const SOURCE_MOBILE = 3;

    private string $from;
    private string $to;
    private string $type;
    private ?string $message;
    private bool $isBulk;
    private bool $isTemplate;
    private ?string $wallet;
    private int $source;
    private ?array $template;
    private ?string $refCustom;

    /**
     * @param string $from Sender ID (max 11 characters)
     * @param string $to Recipient(s) - comma-separated for multiple
     */
    public function __construct(string $from, string $to)
    {
        $this->setFrom($from);
        $this->to = $to;
        $this->type = self::TYPE_TEXT;
        $this->message = null;
        $this->isBulk = false;
        $this->isTemplate = false;
        $this->wallet = null;
        $this->source = self::SOURCE_API;
        $this->template = null;
        $this->refCustom = null;
    }

    /**
     * Create a new SMS message
     *
     * @param string $from
     * @param string $to
     * @return static
     */
    public static function create(string $from, string $to): static
    {
        return new static($from, $to);
    }

    /**
     * Set sender ID
     *
     * @param string $from
     * @return $this
     */
    public function setFrom(string $from): static
    {
        if (strlen($from) > 11) {
            throw new InvalidArgumentException('Sender ID must be 11 characters or less');
        }
        $this->from = $from;
        return $this;
    }

    /**
     * Set recipient(s)
     *
     * @param string|array<string> $to
     * @return $this
     */
    public function setTo(string|array $to): static
    {
        $this->to = is_array($to) ? implode(',', $to) : $to;
        return $this;
    }

    /**
     * Set message content
     *
     * @param string $message
     * @return $this
     */
    public function setMessage(string $message): static
    {
        if (strlen($message) > 459) {
            throw new InvalidArgumentException('Message must be 459 characters or less');
        }
        $this->message = $message;
        $this->isTemplate = false;
        return $this;
    }

    /**
     * Set SMS type (text or flash)
     *
     * @param string $type
     * @return $this
     */
    public function setType(string $type): static
    {
        if (!in_array($type, [self::TYPE_TEXT, self::TYPE_FLASH], true)) {
            throw new InvalidArgumentException('Type must be "text" or "flash"');
        }
        $this->type = $type;
        return $this;
    }

    /**
     * Set as flash SMS
     *
     * @return $this
     */
    public function asFlash(): static
    {
        $this->type = self::TYPE_FLASH;
        return $this;
    }

    /**
     * Enable bulk sending mode (to contains group IDs)
     *
     * @param bool $isBulk
     * @return $this
     */
    public function setBulk(bool $isBulk = true): static
    {
        $this->isBulk = $isBulk;
        return $this;
    }

    /**
     * Set to use a template
     *
     * @param string $templateId Template API key
     * @param string $lang Language code (fr, en, es, de, default)
     * @return $this
     */
    public function useTemplate(string $templateId, string $lang = 'fr'): static
    {
        if (!in_array($lang, ['fr', 'en', 'es', 'de', 'default'], true)) {
            throw new InvalidArgumentException('Language must be one of: fr, en, es, de, default');
        }
        
        $this->isTemplate = true;
        $this->template = [
            'id' => $templateId,
            'lang' => $lang,
        ];
        $this->message = null;
        return $this;
    }

    /**
     * Set wallet ID
     *
     * @param string|null $wallet
     * @return $this
     */
    public function setWallet(?string $wallet): static
    {
        $this->wallet = $wallet;
        return $this;
    }

    /**
     * Set source identifier
     *
     * @param int $source
     * @return $this
     */
    public function setSource(int $source): static
    {
        if (!in_array($source, [1, 2, 3, 4, 5], true)) {
            throw new InvalidArgumentException('Source must be between 1 and 5');
        }
        $this->source = $source;
        return $this;
    }

    /**
     * Set custom reference
     *
     * @param string|null $refCustom
     * @return $this
     */
    public function setRefCustom(?string $refCustom): static
    {
        if ($refCustom !== null && strlen($refCustom) > 100) {
            throw new InvalidArgumentException('Custom reference must be 100 characters or less');
        }
        $this->refCustom = $refCustom;
        return $this;
    }

    /**
     * Convert to array for API request
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'from' => $this->from,
            'to' => $this->to,
            'type' => $this->type,
            'isBulk' => $this->isBulk,
            'isTemplate' => $this->isTemplate,
            'source' => $this->source,
        ];

        if ($this->message !== null) {
            $data['message'] = $this->message;
        }

        if ($this->wallet !== null) {
            $data['wallet'] = $this->wallet;
        }

        if ($this->template !== null) {
            $data['template'] = $this->template;
        }

        if ($this->refCustom !== null) {
            $data['ref_custom'] = $this->refCustom;
        }

        return $data;
    }

    // Getters

    public function getFrom(): string
    {
        return $this->from;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isBulk(): bool
    {
        return $this->isBulk;
    }

    public function isTemplate(): bool
    {
        return $this->isTemplate;
    }

    public function getWallet(): ?string
    {
        return $this->wallet;
    }

    public function getSource(): int
    {
        return $this->source;
    }

    public function getTemplate(): ?array
    {
        return $this->template;
    }

    public function getRefCustom(): ?string
    {
        return $this->refCustom;
    }
}
