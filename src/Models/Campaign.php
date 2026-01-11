<?php

declare(strict_types=1);

namespace KyaSms\Models;

use InvalidArgumentException;
use DateTimeInterface;
use DateTime;

/**
 * Campaign model
 */
class Campaign
{
    public const TYPE_AUTO = 'auto';
    public const TYPE_CUSTOMIZE = 'customize';
    public const TYPE_PERIODIC = 'periodic';

    public const SMS_TYPE_TEXT = 'text';
    public const SMS_TYPE_FLASH = 'flash';
    public const SMS_TYPE_UNICODE = 'unicode';

    public const CONTENT_TYPE_MESSAGE = 'message';
    public const CONTENT_TYPE_TEMPLATE = 'template';

    public const PERIODIC_WEEKLY_START = 'weekly_start';
    public const PERIODIC_WEEKLY_END = 'weekly_end';
    public const PERIODIC_MONTHLY_START = 'monthly_start';
    public const PERIODIC_MONTHLY_END = 'monthly_end';
    public const PERIODIC_SPECIFIC_DAY = 'specific_day_of_month';
    public const PERIODIC_BEGINNING_YEAR = 'beginning_of_year';
    public const PERIODIC_CHRISTMAS = 'christmas';

    private string $name;
    private string $type;
    private array $groups;
    private string $senderId;
    private string $smsType;
    private array $content;
    private ?string $timezone;
    private ?string $scheduleDate;
    private ?string $campaignPeriodic;

    /**
     * @param string $name Campaign name
     * @param array<string> $groups Group IDs
     * @param string $senderId Sender ID
     */
    public function __construct(
        string $name,
        array $groups,
        string $senderId
    ) {
        $this->name = $name;
        $this->groups = $groups;
        $this->senderId = $senderId;
        $this->type = self::TYPE_AUTO;
        $this->smsType = self::SMS_TYPE_TEXT;
        $this->content = ['type' => self::CONTENT_TYPE_MESSAGE, 'message' => ''];
        $this->timezone = null;
        $this->scheduleDate = null;
        $this->campaignPeriodic = null;
    }

    /**
     * Create a new campaign
     *
     * @param string $name
     * @param array<string> $groups
     * @param string $senderId
     * @return static
     */
    public static function create(
        string $name,
        array $groups,
        string $senderId
    ): static {
        return new static($name, $groups, $senderId);
    }

    /**
     * Set campaign name
     *
     * @param string $name
     * @return $this
     */
    public function setName(string $name): static
    {
        if (strlen($name) > 255) {
            throw new InvalidArgumentException('Campaign name must be 255 characters or less');
        }
        $this->name = $name;
        return $this;
    }

    /**
     * Set campaign type
     *
     * @param string $type
     * @return $this
     */
    public function setType(string $type): static
    {
        $validTypes = [self::TYPE_AUTO, self::TYPE_CUSTOMIZE, self::TYPE_PERIODIC];
        if (!in_array($type, $validTypes, true)) {
            throw new InvalidArgumentException(
                'Type must be one of: ' . implode(', ', $validTypes)
            );
        }
        $this->type = $type;
        return $this;
    }

    /**
     * Set as automatic (immediate) campaign
     *
     * @return $this
     */
    public function asAutomatic(): static
    {
        $this->type = self::TYPE_AUTO;
        $this->scheduleDate = null;
        $this->campaignPeriodic = null;
        return $this;
    }

    /**
     * Set as scheduled campaign
     *
     * @param DateTimeInterface|string $scheduleDate
     * @param string|null $timezone
     * @return $this
     */
    public function asScheduled(DateTimeInterface|string $scheduleDate, ?string $timezone = null): static
    {
        $this->type = self::TYPE_CUSTOMIZE;
        
        if ($scheduleDate instanceof DateTimeInterface) {
            $this->scheduleDate = $scheduleDate->format('Y-m-d H:i:s');
        } else {
            // Validate format
            $dt = DateTime::createFromFormat('Y-m-d H:i:s', $scheduleDate);
            if (!$dt) {
                throw new InvalidArgumentException(
                    'Schedule date must be in format: Y-m-d H:i:s'
                );
            }
            $this->scheduleDate = $scheduleDate;
        }
        
        $this->timezone = $timezone;
        $this->campaignPeriodic = null;
        return $this;
    }

    /**
     * Set as periodic campaign
     *
     * @param string $periodic Periodic type
     * @param string|null $timezone
     * @return $this
     */
    public function asPeriodic(string $periodic, ?string $timezone = null): static
    {
        $validPeriodic = [
            self::PERIODIC_WEEKLY_START,
            self::PERIODIC_WEEKLY_END,
            self::PERIODIC_MONTHLY_START,
            self::PERIODIC_MONTHLY_END,
            self::PERIODIC_SPECIFIC_DAY,
            self::PERIODIC_BEGINNING_YEAR,
            self::PERIODIC_CHRISTMAS,
        ];
        
        if (!in_array($periodic, $validPeriodic, true)) {
            throw new InvalidArgumentException(
                'Periodic type must be one of: ' . implode(', ', $validPeriodic)
            );
        }
        
        $this->type = self::TYPE_PERIODIC;
        $this->campaignPeriodic = $periodic;
        $this->timezone = $timezone;
        $this->scheduleDate = null;
        return $this;
    }

    /**
     * Set groups
     *
     * @param array<string> $groups
     * @return $this
     */
    public function setGroups(array $groups): static
    {
        $this->groups = $groups;
        return $this;
    }

    /**
     * Add a group
     *
     * @param string $groupId
     * @return $this
     */
    public function addGroup(string $groupId): static
    {
        if (!in_array($groupId, $this->groups, true)) {
            $this->groups[] = $groupId;
        }
        return $this;
    }

    /**
     * Set sender ID
     *
     * @param string $senderId
     * @return $this
     */
    public function setSenderId(string $senderId): static
    {
        if (strlen($senderId) > 11) {
            throw new InvalidArgumentException('Sender ID must be 11 characters or less');
        }
        $this->senderId = $senderId;
        return $this;
    }

    /**
     * Set SMS type
     *
     * @param string $smsType
     * @return $this
     */
    public function setSmsType(string $smsType): static
    {
        $validTypes = [self::SMS_TYPE_TEXT, self::SMS_TYPE_FLASH, self::SMS_TYPE_UNICODE];
        if (!in_array($smsType, $validTypes, true)) {
            throw new InvalidArgumentException(
                'SMS type must be one of: ' . implode(', ', $validTypes)
            );
        }
        $this->smsType = $smsType;
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
        $this->content = [
            'type' => self::CONTENT_TYPE_MESSAGE,
            'message' => $message,
        ];
        return $this;
    }

    /**
     * Use template for content
     *
     * @param string $templateId
     * @param string $lang
     * @return $this
     */
    public function useTemplate(string $templateId, string $lang = 'fr'): static
    {
        $validLangs = ['fr', 'en', 'es', 'de'];
        if (!in_array($lang, $validLangs, true)) {
            throw new InvalidArgumentException(
                'Language must be one of: ' . implode(', ', $validLangs)
            );
        }
        
        $this->content = [
            'type' => self::CONTENT_TYPE_TEMPLATE,
            'template_id' => $templateId,
            'template_default_lang' => $lang,
        ];
        return $this;
    }

    /**
     * Set timezone
     *
     * @param string $timezone
     * @return $this
     */
    public function setTimezone(string $timezone): static
    {
        $this->timezone = $timezone;
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
            'name' => $this->name,
            'type' => $this->type,
            'groups' => $this->groups,
            'sender_id' => $this->senderId,
            'sms_type' => $this->smsType,
            'content' => $this->content,
        ];

        if ($this->timezone !== null) {
            $data['timezone'] = $this->timezone;
        }

        if ($this->scheduleDate !== null) {
            $data['schedule_date'] = $this->scheduleDate;
        }

        if ($this->campaignPeriodic !== null) {
            $data['campaign_periodic'] = $this->campaignPeriodic;
        }

        return $data;
    }

    // Getters

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getGroups(): array
    {
        return $this->groups;
    }

    public function getSenderId(): string
    {
        return $this->senderId;
    }

    public function getSmsType(): string
    {
        return $this->smsType;
    }

    public function getContent(): array
    {
        return $this->content;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function getScheduleDate(): ?string
    {
        return $this->scheduleDate;
    }

    public function getCampaignPeriodic(): ?string
    {
        return $this->campaignPeriodic;
    }
}
