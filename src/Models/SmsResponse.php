<?php

declare(strict_types=1);

namespace KyaSms\Models;

/**
 * SMS Response model
 */
class SmsResponse
{
    private bool $success;
    private ?string $reason;
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
        $this->reason = $response['reason'] ?? null;
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
     * Get reason (success or error reason)
     *
     * @return string|null
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * Get the first message ID (convenience method for single SMS)
     *
     * @return string|null
     */
    public function getMessageId(): ?string
    {
        return $this->data[0]['messageId'] ?? null;
    }

    /**
     * Get all message IDs
     *
     * @return array<string>
     */
    public function getMessageIds(): array
    {
        return array_column($this->data, 'messageId');
    }

    /**
     * Get the first message status (convenience method for single SMS)
     *
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->data[0]['status'] ?? $this->status;
    }

    /**
     * Get the route used for the first message
     *
     * @return string|null
     */
    public function getRoute(): ?string
    {
        return $this->data[0]['route'] ?? null;
    }

    /**
     * Get the price for the first message
     *
     * @return float|null
     */
    public function getPrice(): ?float
    {
        $price = $this->data[0]['price'] ?? null;
        return $price !== null ? (float) $price : null;
    }

    /**
     * Get total price for all messages
     *
     * @return float
     */
    public function getTotalPrice(): float
    {
        $total = 0;
        foreach ($this->data as $item) {
            $total += (float) ($item['price'] ?? 0);
        }
        return $total;
    }

    /**
     * Get SMS parts count for the first message
     *
     * @return int|null
     */
    public function getSmsPart(): ?int
    {
        $part = $this->data[0]['sms_part'] ?? null;
        return $part !== null ? (int) $part : null;
    }

    /**
     * Get recipient phone number for the first message
     *
     * @return string|null
     */
    public function getTo(): ?string
    {
        return $this->data[0]['to'] ?? null;
    }

    /**
     * Get created_at timestamp for the first message
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string
    {
        return $this->data[0]['created_at'] ?? null;
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
     * Get processed data (all messages)
     *
     * @return array<int, array<string, mixed>>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Get first message data
     *
     * @return array<string, mixed>|null
     */
    public function getFirstMessage(): ?array
    {
        return $this->data[0] ?? null;
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
     * Get message content
     *
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->data[0]['message'] ?? $this->message;
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
            'reason' => $this->reason,
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
