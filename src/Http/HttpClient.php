<?php

declare(strict_types=1);

namespace KyaSms\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use KyaSms\Exceptions\ApiException;
use KyaSms\Exceptions\AuthenticationException;
use KyaSms\Exceptions\KyaSmsException;
use KyaSms\Exceptions\ValidationException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * HTTP Client for KYA SMS API
 */
class HttpClient
{
    private const DEFAULT_TIMEOUT = 30;
    private const DEFAULT_CONNECT_TIMEOUT = 10;
    private const MAX_RETRIES = 3;
    private const RETRY_DELAY_MS = 1000;

    private Client $client;
    private string $apiKey;
    private string $baseUrl;
    private LoggerInterface $logger;
    private bool $debug;

    /**
     * @param string $apiKey
     * @param string $baseUrl
     * @param array<string, mixed> $options
     */
    public function __construct(
        string $apiKey,
        string $baseUrl,
        array $options = []
    ) {
        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($baseUrl, '/') . '/';
        $this->logger = $options['logger'] ?? new NullLogger();
        $this->debug = $options['debug'] ?? false;

        $this->client = $this->createClient($options);
    }

    /**
     * Create Guzzle client with middleware
     *
     * @param array<string, mixed> $options
     * @return Client
     */
    private function createClient(array $options): Client
    {
        $stack = HandlerStack::create();

        // Add retry middleware
        $stack->push($this->createRetryMiddleware());

        // Add logging middleware in debug mode
        if ($this->debug) {
            $stack->push($this->createLoggingMiddleware());
        }

        return new Client([
            'base_uri' => $this->baseUrl,
            'handler' => $stack,
            'timeout' => $options['timeout'] ?? self::DEFAULT_TIMEOUT,
            'connect_timeout' => $options['connect_timeout'] ?? self::DEFAULT_CONNECT_TIMEOUT,
            'http_errors' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'APIKEY' => $this->apiKey,
                'User-Agent' => 'KyaSms-PHP-SDK/1.0.0',
            ],
        ]);
    }

    /**
     * Create retry middleware for transient failures
     *
     * @return callable
     */
    private function createRetryMiddleware(): callable
    {
        return Middleware::retry(
            function (
                int $retries,
                Request $request,
                ?Response $response = null,
                ?\Throwable $exception = null
            ): bool {
                // Don't retry if max retries exceeded
                if ($retries >= self::MAX_RETRIES) {
                    return false;
                }

                // Retry on connection errors
                if ($exception instanceof ConnectException) {
                    $this->logger->warning('Connection error, retrying...', [
                        'attempt' => $retries + 1,
                        'exception' => $exception->getMessage(),
                    ]);
                    return true;
                }

                // Retry on server errors (5xx)
                if ($response && $response->getStatusCode() >= 500) {
                    $this->logger->warning('Server error, retrying...', [
                        'attempt' => $retries + 1,
                        'status_code' => $response->getStatusCode(),
                    ]);
                    return true;
                }

                // Retry on rate limiting (429)
                if ($response && $response->getStatusCode() === 429) {
                    $this->logger->warning('Rate limited, retrying...', [
                        'attempt' => $retries + 1,
                    ]);
                    return true;
                }

                return false;
            },
            function (int $retries): int {
                // Exponential backoff
                return self::RETRY_DELAY_MS * (2 ** $retries);
            }
        );
    }

    /**
     * Create logging middleware for debugging
     *
     * @return callable
     */
    private function createLoggingMiddleware(): callable
    {
        return Middleware::tap(
            function (Request $request): void {
                $this->logger->debug('API Request', [
                    'method' => $request->getMethod(),
                    'uri' => (string) $request->getUri(),
                    'headers' => $this->sanitizeHeaders($request->getHeaders()),
                    'body' => (string) $request->getBody(),
                ]);
            },
            function (Request $request, array $options, \GuzzleHttp\Promise\PromiseInterface $promise): void {
                $promise->then(function (Response $response) use ($request): void {
                    $this->logger->debug('API Response', [
                        'status_code' => $response->getStatusCode(),
                        'body' => (string) $response->getBody(),
                    ]);
                    // Reset body stream position
                    $response->getBody()->rewind();
                });
            }
        );
    }

    /**
     * Sanitize headers to hide sensitive data in logs
     *
     * @param array<string, array<string>> $headers
     * @return array<string, string>
     */
    private function sanitizeHeaders(array $headers): array
    {
        $sanitized = [];
        foreach ($headers as $name => $values) {
            if (strtolower($name) === 'apikey') {
                $sanitized[$name] = '***REDACTED***';
            } else {
                $sanitized[$name] = implode(', ', $values);
            }
        }
        return $sanitized;
    }

    /**
     * Send a GET request
     *
     * @param string $endpoint
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     * @throws KyaSmsException
     */
    public function get(string $endpoint, array $query = []): array
    {
        return $this->request('GET', $endpoint, ['query' => $query]);
    }

    /**
     * Send a POST request
     *
     * @param string $endpoint
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     * @throws KyaSmsException
     */
    public function post(string $endpoint, array $data = []): array
    {
        return $this->request('POST', $endpoint, ['json' => $data]);
    }

    /**
     * Send a PUT request
     *
     * @param string $endpoint
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     * @throws KyaSmsException
     */
    public function put(string $endpoint, array $data = []): array
    {
        return $this->request('PUT', $endpoint, ['json' => $data]);
    }

    /**
     * Send a DELETE request
     *
     * @param string $endpoint
     * @return array<string, mixed>
     * @throws KyaSmsException
     */
    public function delete(string $endpoint): array
    {
        return $this->request('DELETE', $endpoint);
    }

    /**
     * Send HTTP request
     *
     * @param string $method
     * @param string $endpoint
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     * @throws KyaSmsException
     */
    private function request(string $method, string $endpoint, array $options = []): array
    {
        try {
            $response = $this->client->request($method, $endpoint, $options);
            $statusCode = $response->getStatusCode();
            $body = (string) $response->getBody();
            
            $data = json_decode($body, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw ApiException::serverError('Invalid JSON response from API');
            }

            return $this->handleResponse($data, $statusCode);

        } catch (ConnectException $e) {
            throw ApiException::networkError($e->getMessage());
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $body = (string) $response->getBody();
                $data = json_decode($body, true) ?? [];
                return $this->handleResponse($data, $response->getStatusCode());
            }
            throw ApiException::networkError($e->getMessage());
        }
    }

    /**
     * Handle API response and throw appropriate exceptions
     *
     * @param array<string, mixed> $data
     * @param int $statusCode
     * @return array<string, mixed>
     * @throws KyaSmsException
     */
    private function handleResponse(array $data, int $statusCode): array
    {
        // Success responses
        if ($statusCode >= 200 && $statusCode < 300) {
            return $data;
        }

        // Authentication errors
        if ($statusCode === 401) {
            $message = $data['errors']['api_key'] ?? $data['errors'] ?? 'Authentication failed';
            if (is_array($message)) {
                $message = implode(', ', array_values($message));
            }
            throw new AuthenticationException($message, 401);
        }

        // Forbidden
        if ($statusCode === 403) {
            throw AuthenticationException::accountDisabled();
        }

        // Not Found
        if ($statusCode === 404) {
            throw new ApiException('La ressource demandée n\'existe pas. Vérifiez l\'endpoint.', 404, 404);
        }

        // Validation errors
        if ($statusCode === 400 || $statusCode === 422) {
            throw ValidationException::fromApiResponse($data);
        }

        // Rate limiting
        if ($statusCode === 429) {
            $retryAfter = $data['retry_after'] ?? 60;
            throw ApiException::rateLimited((int) $retryAfter);
        }

        // Server errors
        if ($statusCode >= 500) {
            throw ApiException::serverError($data['message'] ?? 'Server error');
        }

        // Other errors
        throw KyaSmsException::fromApiResponse($data, $statusCode);
    }
}
