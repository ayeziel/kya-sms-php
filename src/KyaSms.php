<?php

declare(strict_types=1);

namespace KyaSms;

use KyaSms\Api\SmsApi;
use KyaSms\Api\OtpApi;
use KyaSms\Api\CampaignApi;
use KyaSms\Http\HttpClient;
use KyaSms\Exceptions\AuthenticationException;
use Psr\Log\LoggerInterface;

/**
 * KYA SMS SDK Client
 * 
 * Main entry point for the KYA SMS API SDK.
 * 
 * @example
 * ```php
 * use KyaSms\KyaSms;
 * 
 * $client = new KyaSms('your-api-key');
 * 
 * // Send SMS
 * $response = $client->sms()->sendSimple('MyApp', '22990000000', 'Hello World!');
 * 
 * // Send OTP
 * $otpResponse = $client->otp()->send('app-id', '22990000000', 'fr');
 * 
 * // Create Campaign
 * $campaignResponse = $client->campaign()->createAutomatic(
 *     'My Campaign',
 *     ['group-1', 'group-2'],
 *     'MyApp',
 *     'Hello everyone!'
 * );
 * ```
 */
class KyaSms
{
    public const VERSION = '1.0.0';
    public const DEFAULT_BASE_URL = 'https://route.kyasms.com/api/v3';

    private HttpClient $httpClient;
    private ?SmsApi $smsApi = null;
    private ?OtpApi $otpApi = null;
    private ?CampaignApi $campaignApi = null;

    /**
     * Create a new KYA SMS client
     *
     * @param string|array<string, mixed> $apiKeyOrConfig API key string or configuration array
     * @param string|array<string, mixed> $options Base URL string or configuration options
     *   - api_key: string - Your API key (required if first param is array)
     *   - base_url: string - API base URL (default: https://route.kyasms.com/api/v3)
     *   - timeout: int - Request timeout in seconds (default: 30)
     *   - connect_timeout: int - Connection timeout in seconds (default: 10)
     *   - debug: bool - Enable debug mode (default: false)
     *   - logger: LoggerInterface - PSR-3 logger instance
     * @throws AuthenticationException if API key is missing
     */
    public function __construct(string|array $apiKeyOrConfig, string|array $options = [])
    {
        // Handle different constructor signatures
        if (is_array($apiKeyOrConfig)) {
            // new KyaSms(['api_key' => '...', 'debug' => true])
            $config = $apiKeyOrConfig;
            $apiKey = $config['api_key'] ?? '';
            unset($config['api_key']);
            $options = $config;
        } else {
            // new KyaSms('api-key') or new KyaSms('api-key', 'base-url') or new KyaSms('api-key', [...])
            $apiKey = $apiKeyOrConfig;
            if (is_string($options)) {
                $options = ['base_url' => $options];
            }
        }

        if (empty($apiKey)) {
            throw AuthenticationException::missingApiKey();
        }

        $baseUrl = $options['base_url'] ?? self::DEFAULT_BASE_URL;
        unset($options['base_url']);

        $this->httpClient = new HttpClient($apiKey, $baseUrl, $options);
    }

    /**
     * Create client from environment variables
     *
     * Uses KYA_SMS_API_KEY and optionally KYA_SMS_BASE_URL
     *
     * @param array<string, mixed> $options Additional options
     * @return static
     * @throws AuthenticationException if API key is not set
     */
    public static function fromEnvironment(array $options = []): static
    {
        $apiKey = getenv('KYA_SMS_API_KEY') ?: '';
        $baseUrl = getenv('KYA_SMS_BASE_URL');

        if ($baseUrl) {
            $options['base_url'] = $baseUrl;
        }

        return new static($apiKey, $options);
    }

    /**
     * Get SMS API client
     *
     * @return SmsApi
     */
    public function sms(): SmsApi
    {
        if ($this->smsApi === null) {
            $this->smsApi = new SmsApi($this->httpClient);
        }

        return $this->smsApi;
    }

    /**
     * Get OTP API client
     *
     * @return OtpApi
     */
    public function otp(): OtpApi
    {
        if ($this->otpApi === null) {
            $this->otpApi = new OtpApi($this->httpClient);
        }

        return $this->otpApi;
    }

    /**
     * Get Campaign API client
     *
     * @return CampaignApi
     */
    public function campaign(): CampaignApi
    {
        if ($this->campaignApi === null) {
            $this->campaignApi = new CampaignApi($this->httpClient);
        }

        return $this->campaignApi;
    }

    /**
     * Get the HTTP client for advanced usage
     *
     * @return HttpClient
     */
    public function getHttpClient(): HttpClient
    {
        return $this->httpClient;
    }

    /**
     * Get SDK version
     *
     * @return string
     */
    public static function getVersion(): string
    {
        return self::VERSION;
    }
}
