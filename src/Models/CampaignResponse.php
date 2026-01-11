<?php

declare(strict_types=1);

namespace KyaSms\Models;

/**
 * Campaign Response model
 */
class CampaignResponse
{
    private bool $success;
    private ?int $campaignId;
    private ?string $status;
    private ?string $message;
    private ?array $details;
    private ?array $progress;
    private ?array $errors;

    /**
     * @param array<string, mixed> $response
     */
    public function __construct(array $response)
    {
        $this->success = ($response['status'] ?? $response['reason'] ?? '') === 'success';
        $this->campaignId = isset($response['campaign_id']) ? (int) $response['campaign_id'] : null;
        $this->status = $response['status'] ?? null;
        $this->message = $response['message'] ?? null;
        $this->details = $response['details'] ?? null;
        $this->progress = $response['progress'] ?? null;
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
     * Get campaign ID
     *
     * @return int|null
     */
    public function getCampaignId(): ?int
    {
        return $this->campaignId;
    }

    /**
     * Get status
     *
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * Get message
     *
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * Get details
     *
     * @return array<string, mixed>|null
     */
    public function getDetails(): ?array
    {
        return $this->details;
    }

    /**
     * Get execution date from details
     *
     * @return string|null
     */
    public function getExecutionDate(): ?string
    {
        return $this->details['execution_date'] ?? null;
    }

    /**
     * Get total contacts from details
     *
     * @return int|null
     */
    public function getTotalContacts(): ?int
    {
        return isset($this->details['total_contacts']) 
            ? (int) $this->details['total_contacts'] 
            : null;
    }

    /**
     * Get progress information
     *
     * @return array<string, mixed>|null
     */
    public function getProgress(): ?array
    {
        return $this->progress;
    }

    /**
     * Get sent count from progress
     *
     * @return int
     */
    public function getSentCount(): int
    {
        return (int) ($this->progress['sent'] ?? 0);
    }

    /**
     * Get delivered count from progress
     *
     * @return int
     */
    public function getDeliveredCount(): int
    {
        return (int) ($this->progress['delivered'] ?? 0);
    }

    /**
     * Get failed count from progress
     *
     * @return int
     */
    public function getFailedCount(): int
    {
        return (int) ($this->progress['failed'] ?? 0);
    }

    /**
     * Get pending count from progress
     *
     * @return int
     */
    public function getPendingCount(): int
    {
        return (int) ($this->progress['pending'] ?? 0);
    }

    /**
     * Get errors
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
            'campaign_id' => $this->campaignId,
            'status' => $this->status,
            'message' => $this->message,
            'details' => $this->details,
            'progress' => $this->progress,
            'errors' => $this->errors,
        ];
    }
}
