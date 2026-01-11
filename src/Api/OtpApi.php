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
    private string $createEndpoint = 'otp/create';
    private string $verifyEndpoint = 'otp/verify';

    /**
     * @param HttpClient $client
     */
    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * Create and send OTP
     *
     * @param OtpRequest $request
     * @return OtpResponse
     * @throws ValidationException
     */
    public function create(OtpRequest $request): OtpResponse
    {
        $this->validate($request);
        
        $response = $this->client->post($this->createEndpoint, $request->toArray());
        
        return OtpResponse::fromResponse($response);
    }

    /**
     * Alias for create() - backward compatibility
     *
     * @param OtpRequest $request
     * @return OtpResponse
     */
    public function initiate(OtpRequest $request): OtpResponse
    {
        return $this->create($request);
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
        
        return $this->create($request);
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

        return $this->create($request);
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

        return $this->create($request);
    }

    /**
     * Verify OTP code
     *
     * @param string $appId Application ID
     * @param string $key Key returned from create()
     * @param string $code OTP code entered by user
     * @return array{reason: string, status: int, msg: string}
     */
    public function verify(string $appId, string $key, string $code): array
    {
        if (empty($appId)) {
            throw new ValidationException('Validation failed', ['appId' => 'Application ID is required']);
        }
        if (empty($key)) {
            throw new ValidationException('Validation failed', ['key' => 'Verification key is required']);
        }
        if (empty($code)) {
            throw new ValidationException('Validation failed', ['code' => 'OTP code is required']);
        }

        $response = $this->client->post($this->verifyEndpoint, [
            'appId' => $appId,
            'key' => $key,
            'code' => $code,
        ]);

        return [
            'reason' => $response['reason'] ?? '',
            'status' => (int) ($response['status'] ?? 0),
            'msg' => $response['msg'] ?? '',
        ];
    }

    /**
     * Check if OTP verification was successful
     *
     * @param array $verifyResponse Response from verify()
     * @return bool
     */
    public function isVerified(array $verifyResponse): bool
    {
        return ($verifyResponse['status'] ?? 0) === 200 
            && ($verifyResponse['msg'] ?? '') === 'checked';
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
