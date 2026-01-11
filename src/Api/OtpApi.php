<?php

declare(strict_types=1);

namespace KyaSms\Api;

use KyaSms\Http\HttpClient;
use KyaSms\Models\OtpRequest;
use KyaSms\Models\OtpResponse;
use KyaSms\Exceptions\ValidationException;

/**
 * OTP API client
 */
class OtpApi
{
    private HttpClient $client;
    private string $endpoint = '/api/v3/otp/initiate';

    /**
     * @param HttpClient $client
     */
    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * Initiate OTP verification
     *
     * @param OtpRequest $request
     * @return OtpResponse
     * @throws ValidationException
     */
    public function initiate(OtpRequest $request): OtpResponse
    {
        $this->validate($request);
        
        $response = $this->client->post($this->endpoint, $request->toArray());
        
        return OtpResponse::fromResponse($response);
    }

    /**
     * Send OTP with simple parameters
     *
     * @param string $appId Application ID
     * @param string $recipient Phone number or email
     * @param string $lang Language code (fr, en, es, de)
     * @return OtpResponse
     */
    public function send(string $appId, string $recipient, string $lang = 'fr'): OtpResponse
    {
        $request = OtpRequest::create($appId, $recipient, $lang);
        
        return $this->initiate($request);
    }

    /**
     * Send OTP with custom code
     *
     * @param string $appId Application ID
     * @param string $recipient Phone number or email
     * @param string $code Custom OTP code
     * @param string $lang Language code
     * @param int|null $minutes Expiration time in minutes
     * @return OtpResponse
     */
    public function sendWithCustomCode(
        string $appId,
        string $recipient,
        string $code,
        string $lang = 'fr',
        ?int $minutes = null
    ): OtpResponse {
        $request = OtpRequest::create($appId, $recipient, $lang)
            ->setCode($code);

        if ($minutes !== null) {
            $request->setMinutes($minutes);
        }

        return $this->initiate($request);
    }

    /**
     * Send OTP with expiration time
     *
     * @param string $appId Application ID
     * @param string $recipient Phone number or email
     * @param int $minutes Expiration time in minutes
     * @param string $lang Language code
     * @return OtpResponse
     */
    public function sendWithExpiration(
        string $appId,
        string $recipient,
        int $minutes,
        string $lang = 'fr'
    ): OtpResponse {
        $request = OtpRequest::create($appId, $recipient, $lang)
            ->setMinutes($minutes);

        return $this->initiate($request);
    }

    /**
     * Validate OTP request before sending
     *
     * @param OtpRequest $request
     * @throws ValidationException
     */
    private function validate(OtpRequest $request): void
    {
        $errors = [];

        if (empty($request->getAppId())) {
            $errors['appId'] = 'Application ID is required';
        }

        if (empty($request->getRecipient())) {
            $errors['recipient'] = 'Recipient is required';
        }

        if (empty($request->getLang())) {
            $errors['lang'] = 'Language is required';
        }

        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }
    }
}
