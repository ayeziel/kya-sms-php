<?php

declare(strict_types=1);

namespace KyaSms\Api;

use KyaSms\Http\HttpClient;
use KyaSms\Models\Campaign;
use KyaSms\Models\CampaignResponse;
use KyaSms\Exceptions\ValidationException;
use DateTimeInterface;

/**
 * Campaign API client
 */
class CampaignApi
{
    private HttpClient $client;
    private string $createEndpoint = 'sms/campaign/create';
    private string $statusEndpoint = 'sms/campaign/status/{id}';
    private string $recordsEndpoint = 'sms/campaign/records';
    private string $calculateCostEndpoint = 'sms/campaign/calculate-cost';

    /**
     * @param HttpClient $client
     */
    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * Create a new campaign
     *
     * @param Campaign $campaign
     * @return CampaignResponse
     * @throws ValidationException
     */
    public function create(Campaign $campaign): CampaignResponse
    {
        $this->validate($campaign);
        
        $response = $this->client->post($this->createEndpoint, $campaign->toArray());
        
        return CampaignResponse::fromResponse($response);
    }

    /**
     * Create an automatic (immediate) campaign
     *
     * @param string $name Campaign name
     * @param array<string> $groups Group IDs
     * @param string $senderId Sender ID
     * @param string $message Message content
     * @return CampaignResponse
     */
    public function createAutomatic(
        string $name,
        array $groups,
        string $senderId,
        string $message
    ): CampaignResponse {
        $campaign = Campaign::create($name, $groups, $senderId)
            ->asAutomatic()
            ->setMessage($message);

        return $this->create($campaign);
    }

    /**
     * Create a scheduled campaign
     *
     * @param string $name Campaign name
     * @param array<string> $groups Group IDs
     * @param string $senderId Sender ID
     * @param string $message Message content
     * @param DateTimeInterface|string $scheduleDate Schedule date
     * @param string|null $timezone Timezone
     * @return CampaignResponse
     */
    public function createScheduled(
        string $name,
        array $groups,
        string $senderId,
        string $message,
        DateTimeInterface|string $scheduleDate,
        ?string $timezone = null
    ): CampaignResponse {
        $campaign = Campaign::create($name, $groups, $senderId)
            ->asScheduled($scheduleDate, $timezone)
            ->setMessage($message);

        return $this->create($campaign);
    }

    /**
     * Create a periodic campaign
     *
     * @param string $name Campaign name
     * @param array<string> $groups Group IDs
     * @param string $senderId Sender ID
     * @param string $message Message content
     * @param string $periodic Periodic type
     * @param string|null $timezone Timezone
     * @return CampaignResponse
     */
    public function createPeriodic(
        string $name,
        array $groups,
        string $senderId,
        string $message,
        string $periodic,
        ?string $timezone = null
    ): CampaignResponse {
        $campaign = Campaign::create($name, $groups, $senderId)
            ->asPeriodic($periodic, $timezone)
            ->setMessage($message);

        return $this->create($campaign);
    }

    /**
     * Create a campaign with template
     *
     * @param string $name Campaign name
     * @param array<string> $groups Group IDs
     * @param string $senderId Sender ID
     * @param string $templateId Template ID
     * @param string $templateLang Template language
     * @return CampaignResponse
     */
    public function createWithTemplate(
        string $name,
        array $groups,
        string $senderId,
        string $templateId,
        string $templateLang = 'fr'
    ): CampaignResponse {
        $campaign = Campaign::create($name, $groups, $senderId)
            ->asAutomatic()
            ->useTemplate($templateId, $templateLang);

        return $this->create($campaign);
    }

    /**
     * Get campaign status
     *
     * @param int|string $campaignId Campaign ID
     * @return CampaignResponse
     */
    public function getStatus(int|string $campaignId): CampaignResponse
    {
        $endpoint = str_replace('{id}', (string) $campaignId, $this->statusEndpoint);
        
        $response = $this->client->get($endpoint);
        
        return CampaignResponse::fromResponse($response);
    }

    /**
     * Get campaign records/history
     *
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array{campaigns: array, pagination: array}
     */
    public function getRecords(int $page = 1, int $perPage = 20): array
    {
        $response = $this->client->get($this->recordsEndpoint, [
            'page' => (string) $page,
            'per_page' => (string) $perPage,
        ]);

        return [
            'campaigns' => $response['data']['campaigns'] ?? [],
            'pagination' => $response['data']['pagination'] ?? [],
        ];
    }

    /**
     * Calculate campaign cost estimate
     *
     * @param array<string> $groups Group IDs
     * @param string $message Message content
     * @return array
     */
    public function calculateCost(array $groups, string $message): array
    {
        $response = $this->client->post($this->calculateCostEndpoint, [
            'groups' => $groups,
            'message' => $message,
        ]);

        $data = $response['data'] ?? $response;

        return [
            'estimated_cost' => (float) ($data['estimated_cost'] ?? 0),
            'total_recipients' => (int) ($data['total_recipients'] ?? 0),
            'valid_recipients' => (int) ($data['valid_recipients'] ?? $data['total_recipients'] ?? 0),
            'invalid_contacts' => (int) ($data['invalid_contacts'] ?? 0),
            'total_sms_parts' => (int) ($data['total_sms_parts'] ?? 0),
            'average_sms_parts' => (float) ($data['average_sms_parts'] ?? 0),
            'message_info' => $data['message_info'] ?? [],
            'country_breakdown' => $data['country_breakdown'] ?? [],
            'groups_info' => $data['groups_info'] ?? [],
        ];
    }

    /**
     * Check if campaign is completed
     *
     * @param int|string $campaignId Campaign ID
     * @return bool
     */
    public function isCompleted(int|string $campaignId): bool
    {
        $status = $this->getStatus($campaignId);
        return $status->getStatus() === 'completed';
    }

    /**
     * Get campaign progress percentage
     *
     * @param int|string $campaignId Campaign ID
     * @return float
     */
    public function getProgress(int|string $campaignId): float
    {
        $status = $this->getStatus($campaignId);
        $progress = $status->getProgress();
        
        if ($progress === null || !isset($progress['total']) || $progress['total'] === 0) {
            return 0.0;
        }

        $sent = $progress['sent'] ?? 0;
        return ($sent / $progress['total']) * 100;
    }

    /**
     * Validate campaign before creation
     *
     * @param Campaign $campaign
     * @throws ValidationException
     */
    private function validate(Campaign $campaign): void
    {
        $errors = [];

        if (empty($campaign->getName())) {
            $errors['name'] = 'Campaign name is required';
        }

        if (empty($campaign->getGroups())) {
            $errors['groups'] = 'At least one group is required';
        }

        if (empty($campaign->getSenderId())) {
            $errors['sender_id'] = 'Sender ID is required';
        }

        $content = $campaign->getContent();
        if ($content['type'] === Campaign::CONTENT_TYPE_MESSAGE && empty($content['message'] ?? '')) {
            $errors['content.message'] = 'Message is required when content type is message';
        }

        if ($content['type'] === Campaign::CONTENT_TYPE_TEMPLATE && empty($content['template_id'] ?? '')) {
            $errors['content.template_id'] = 'Template ID is required when content type is template';
        }

        if ($campaign->getType() === Campaign::TYPE_CUSTOMIZE && empty($campaign->getScheduleDate())) {
            $errors['schedule_date'] = 'Schedule date is required for scheduled campaigns';
        }

        if ($campaign->getType() === Campaign::TYPE_PERIODIC && empty($campaign->getCampaignPeriodic())) {
            $errors['campaign_periodic'] = 'Periodic type is required for periodic campaigns';
        }

        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }
    }
}
