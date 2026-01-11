<?php

declare(strict_types=1);

namespace KyaSms\Api;

use KyaSms\Http\HttpClient;
use KyaSms\Models\SmsMessage;
use KyaSms\Models\SmsResponse;
use KyaSms\Exceptions\ValidationException;

/**
 * SMS API client
 */
class SmsApi
{
    private HttpClient $client;
    private string $sendEndpoint = 'sms/send';
    private string $statusEndpoint = 'message/status';
    private string $historyEndpoint = 'sms/history';

    /**
     * @param HttpClient $client
     */
    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * Send an SMS message
     *
     * @param SmsMessage $message
     * @return SmsResponse
     * @throws ValidationException
     */
    public function send(SmsMessage $message): SmsResponse
    {
        $this->validate($message);
        
        $response = $this->client->post($this->sendEndpoint, $message->toArray());
        
        return SmsResponse::fromResponse($response);
    }

    /**
     * Send a simple SMS
     *
     * @param string $from Sender ID
     * @param string|array<string> $to Recipient(s)
     * @param string $message Message content
     * @return SmsResponse
     */
    public function sendSimple(string $from, string|array $to, string $message): SmsResponse
    {
        $sms = SmsMessage::create($from, is_array($to) ? implode(',', $to) : $to)
            ->setMessage($message);

        return $this->send($sms);
    }

    /**
     * Send a flash SMS
     *
     * @param string $from Sender ID
     * @param string|array<string> $to Recipient(s)
     * @param string $message Message content
     * @return SmsResponse
     */
    public function sendFlash(string $from, string|array $to, string $message): SmsResponse
    {
        $sms = SmsMessage::create($from, is_array($to) ? implode(',', $to) : $to)
            ->setMessage($message)
            ->asFlash();

        return $this->send($sms);
    }

    /**
     * Send SMS using a template
     *
     * @param string $from Sender ID
     * @param string|array<string> $to Recipient(s)
     * @param string $templateId Template API key
     * @param string $lang Language code
     * @return SmsResponse
     */
    public function sendWithTemplate(
        string $from,
        string|array $to,
        string $templateId,
        string $lang = 'fr'
    ): SmsResponse {
        $sms = SmsMessage::create($from, is_array($to) ? implode(',', $to) : $to)
            ->useTemplate($templateId, $lang);

        return $this->send($sms);
    }

    /**
     * Send bulk SMS to contact groups
     *
     * @param string $from Sender ID
     * @param array<string> $groupIds Group IDs
     * @param string $message Message content
     * @return SmsResponse
     */
    public function sendBulk(string $from, array $groupIds, string $message): SmsResponse
    {
        $sms = SmsMessage::create($from, implode(',', $groupIds))
            ->setMessage($message)
            ->setBulk(true);

        return $this->send($sms);
    }

    /**
     * Send bulk SMS with template
     *
     * @param string $from Sender ID
     * @param array<string> $groupIds Group IDs
     * @param string $templateId Template API key
     * @param string $lang Language code
     * @return SmsResponse
     */
    public function sendBulkWithTemplate(
        string $from,
        array $groupIds,
        string $templateId,
        string $lang = 'fr'
    ): SmsResponse {
        $sms = SmsMessage::create($from, implode(',', $groupIds))
            ->useTemplate($templateId, $lang)
            ->setBulk(true);

        return $this->send($sms);
    }

    /**
     * Get status of multiple messages
     *
     * @param array<string> $messageIds Array of message IDs (max 100)
     * @return array<string, array{phone: string, status: string, route: string, updated_at: string}>
     * @throws ValidationException
     */
    public function getStatus(array $messageIds): array
    {
        if (empty($messageIds)) {
            throw new ValidationException('Validation failed', ['message_ids' => 'At least one message ID is required']);
        }

        if (count($messageIds) > 100) {
            throw new ValidationException('Validation failed', ['message_ids' => 'Maximum 100 message IDs per request']);
        }

        $response = $this->client->post($this->statusEndpoint, [
            'message_ids' => $messageIds,
        ]);

        return $response['data'] ?? [];
    }

    /**
     * Get single message status
     *
     * @param string $messageId Message ID
     * @return array{phone: string, status: string, route: string, updated_at: string}|null
     */
    public function getMessageStatus(string $messageId): ?array
    {
        $statuses = $this->getStatus([$messageId]);
        return $statuses[$messageId] ?? null;
    }

    /**
     * Check if message was delivered
     *
     * @param string $messageId Message ID
     * @return bool
     */
    public function isDelivered(string $messageId): bool
    {
        $status = $this->getMessageStatus($messageId);
        return $status !== null && ($status['status'] ?? '') === 'DELIVERED';
    }

    /**
     * Get SMS history with optional filters
     *
     * @param string|null $startDate Start date (YYYY-MM-DD)
     * @param string|null $endDate End date (YYYY-MM-DD)
     * @param int $page Page number
     * @param int $perPage Items per page (max 100)
     * @return array{messages: array, pagination: array}
     */
    public function getHistory(
        ?string $startDate = null,
        ?string $endDate = null,
        int $page = 1,
        int $perPage = 50
    ): array {
        $data = [
            'page' => $page,
            'per_page' => min($perPage, 100),
        ];

        if ($startDate !== null) {
            $data['start_date'] = $startDate;
        }

        if ($endDate !== null) {
            $data['end_date'] = $endDate;
        }

        $response = $this->client->post($this->historyEndpoint, $data);

        return [
            'messages' => $response['data']['messages'] ?? [],
            'pagination' => $response['data']['pagination'] ?? [],
        ];
    }

    /**
     * Validate SMS message before sending
     *
     * @param SmsMessage $message
     * @throws ValidationException
     */
    private function validate(SmsMessage $message): void
    {
        $errors = [];

        if (empty($message->getFrom())) {
            $errors['from'] = 'Sender ID is required';
        }

        if (empty($message->getTo())) {
            $errors['to'] = 'Recipient is required';
        }

        if (!$message->isTemplate() && empty($message->getMessage())) {
            $errors['message'] = 'Message is required when not using a template';
        }

        if ($message->isTemplate() && empty($message->getTemplate())) {
            $errors['template'] = 'Template configuration is required when using template mode';
        }

        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }
    }
}
