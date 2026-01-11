<?php

declare(strict_types=1);

namespace KyaSms\Models;

/**
 * SMS Response model
 */
class SmsResponse
{
    private bool $success;
    private ?string $taskId;
    private ?string $status;
    private ?string $from;
    private ?string $wallet;
    private ?string $callbackUrl;
    private array $data;
    private ?array $queueProcessing;
    private ?int $totalContacts;
    private ?string $message;

    /**
     * @param array<string, mixed> $response
     */
    public function __construct(array $response)
    {
        $this->success = ($response['reason'] ?? '') === 'success';
        $this->taskId = $response['task_id'] ?? null;
        $this->status = $response['status'] ?? null;
        $this->from = $response['from'] ?? null;
        $this->wallet = $response['wallet'] ?? null;
        $this->callbackUrl = $response['callback_url'] ?? null;
        $this->data = $response['data'] ?? [];
        $this->queueProcessing = $response['queue_processing'] ?? null;
        $this->totalContacts = $response['total_contacts'] ?? null;
        $this->message = $response['message'] ?? null;
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
     * Check if the SMS is queued for processing
     *
     * @return bool
     */
    public function isQueued(): bool
    {
        return $this->status === 'queued_for_processing' || $this->queueProcessing !== null;
    }

    /**
     * Get task ID for tracking queued messages
     *
     * @return string|null
     */
    public function getTaskId(): ?string
    {
        return $this->taskId;
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
     * Get sender ID
     *
     * @return string|null
     */
    public function getFrom(): ?string
    {
        return $this->from;
    }

    /**
     * Get wallet used
     *
     * @return string|null
     */
    public function getWallet(): ?string
    {
        return $this->wallet;
    }

    /**
     * Get callback URL
     *
     * @return string|null
     */
    public function getCallbackUrl(): ?string
    {
        return $this->callbackUrl;
    }

    /**
     * Get processed data
     *
     * @return array<int, array<string, mixed>>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Get queue processing info
     *
     * @return array<string, mixed>|null
     */
    public function getQueueProcessing(): ?array
    {
        return $this->queueProcessing;
    }

    /**
     * Get total contacts count
     *
     * @return int|null
     */
    public function getTotalContacts(): ?int
    {
        return $this->totalContacts;
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
     * Get count of successfully processed messages
     *
     * @return int
     */
    public function getProcessedCount(): int
    {
        return count($this->data);
    }

    /**
     * Get queued contacts count
     *
     * @return int
     */
    public function getQueuedCount(): int
    {
        return $this->queueProcessing['queued_contacts'] ?? 0;
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
            'task_id' => $this->taskId,
            'status' => $this->status,
            'from' => $this->from,
            'wallet' => $this->wallet,
            'callback_url' => $this->callbackUrl,
            'data' => $this->data,
            'queue_processing' => $this->queueProcessing,
            'total_contacts' => $this->totalContacts,
            'message' => $this->message,
        ];
    }
}
