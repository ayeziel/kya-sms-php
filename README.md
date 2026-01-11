# KYA SMS PHP SDK

[![Latest Version](https://img.shields.io/packagist/v/kyasms/kya-sms-php.svg)](https://packagist.org/packages/kyasms/kya-sms-php)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.0-8892BF.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

Official PHP SDK for the KYA SMS API. Send SMS, OTP, and manage campaigns with ease.

## 📋 Table of Contents

- [Installation](#installation)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
- [SMS API](#sms-api)
- [OTP API](#otp-api)
- [Campaign API](#campaign-api)
- [Error Handling](#error-handling)
- [Advanced Usage](#advanced-usage)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)

## Installation

Install via Composer:

```bash
composer require kyasms/kya-sms-php
```

### Requirements

- PHP 8.0 or higher
- Guzzle HTTP Client 7.0+
- JSON extension

## Quick Start

```php
<?php

use KyaSms\KyaSms;

// Initialize the client
$client = new KyaSms('your-api-key');

// Send a simple SMS
$response = $client->sms()->sendSimple(
    from: 'MyApp',
    to: '22990123456',
    message: 'Hello from KYA SMS!'
);

if ($response->isSuccess()) {
    echo "SMS sent successfully!";
}
```

## Configuration

### Basic Configuration

```php
use KyaSms\KyaSms;

$client = new KyaSms('your-api-key', [
    'base_url' => 'https://route.kyasms.com/api/v3',  // Custom API URL
    'timeout' => 30,                          // Request timeout (seconds)
    'connect_timeout' => 10,                  // Connection timeout (seconds)
    'debug' => false,                         // Enable debug logging
]);
```

### Using Environment Variables

```php
// Set environment variables:
// KYA_SMS_API_KEY=your-api-key
// KYA_SMS_BASE_URL=https://route.kyasms.com/api/v3 (optional)

$client = KyaSms::fromEnvironment();
```

### With PSR-3 Logger

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('kyasms');
$logger->pushHandler(new StreamHandler('logs/kyasms.log'));

$client = new KyaSms('your-api-key', [
    'logger' => $logger,
    'debug' => true,
]);
```

## SMS API

### Send Simple SMS

```php
$response = $client->sms()->sendSimple(
    from: 'MyApp',
    to: '22990123456',
    message: 'Hello World!'
);
```

### Send to Multiple Recipients

```php
$response = $client->sms()->sendSimple(
    from: 'MyApp',
    to: ['22990123456', '22991234567', '22992345678'],
    message: 'Hello everyone!'
);
```

### Send Flash SMS

Flash messages appear directly on the phone screen without being stored.

```php
$response = $client->sms()->sendFlash(
    from: 'Alert',
    to: '22990123456',
    message: 'URGENT: This is a flash message!'
);
```

### Send with Template

```php
$response = $client->sms()->sendWithTemplate(
    from: 'MyApp',
    to: '22990123456',
    templateId: 'tpl_welcome',
    lang: 'fr'  // fr, en, es, de
);
```

### Send Bulk SMS to Groups

```php
$response = $client->sms()->sendBulk(
    from: 'Newsletter',
    groupIds: ['group_customers', 'group_subscribers'],
    message: 'Special offer just for you!'
);

if ($response->isQueued()) {
    echo "Task ID: " . $response->getTaskId();
    echo "Total contacts: " . $response->getTotalContacts();
}
```

### Advanced SMS Options

```php
use KyaSms\Models\SmsMessage;

$message = SmsMessage::create('MyApp', '22990123456')
    ->setMessage('Your code is: 123456')
    ->setType(SmsMessage::TYPE_TEXT)     // TYPE_TEXT or TYPE_FLASH
    ->setWallet('wallet_marketing')       // Specific wallet
    ->setRefCustom('order_12345')         // Custom reference
    ->setSource(SmsMessage::SOURCE_API);  // Source identifier

$response = $client->sms()->send($message);
```

## OTP API

### Send Simple OTP

```php
$response = $client->otp()->send(
    appId: 'app_verification',
    recipient: '22990123456',
    lang: 'fr'
);

if ($response->isSuccess()) {
    $otpKey = $response->getKey();
    // Store $otpKey to verify the OTP later
}
```

### Send OTP with Custom Code

```php
$response = $client->otp()->sendWithCustomCode(
    appId: 'app_custom',
    recipient: '22990123456',
    code: '123456',
    lang: 'fr',
    minutes: 5  // Expires in 5 minutes
);
```

### Send OTP with Expiration

```php
$response = $client->otp()->sendWithExpiration(
    appId: 'app_secure',
    recipient: '22990123456',
    minutes: 10,
    lang: 'en'
);
```

### Advanced OTP Request

```php
use KyaSms\Models\OtpRequest;

$request = OtpRequest::create('app_2fa', '22990123456', 'fr')
    ->setCode('CUSTOM123')
    ->expiresIn5Minutes();

$response = $client->otp()->initiate($request);
```

## Campaign API

### Create Immediate Campaign

```php
$response = $client->campaign()->createAutomatic(
    name: 'Flash Sale',
    groups: ['group_customers'],
    senderId: 'MyStore',
    message: '🎉 50% off all items today!'
);

echo "Campaign ID: " . $response->getCampaignId();
```

### Create Scheduled Campaign

```php
$response = $client->campaign()->createScheduled(
    name: 'Weekly Newsletter',
    groups: ['group_newsletter'],
    senderId: 'News',
    message: 'Your weekly update...',
    scheduleDate: new DateTime('next monday 09:00:00'),
    timezone: 'Africa/Porto-Novo'
);
```

### Create Periodic Campaign

```php
use KyaSms\Models\Campaign;

$response = $client->campaign()->createPeriodic(
    name: 'Weekend Reminder',
    groups: ['group_members'],
    senderId: 'Club',
    message: 'Happy weekend!',
    periodic: Campaign::PERIODIC_WEEKLY_END,
    timezone: 'Africa/Porto-Novo'
);
```

Available periodic types:
- `Campaign::PERIODIC_WEEKLY_START` - Monday
- `Campaign::PERIODIC_WEEKLY_END` - Friday/Saturday
- `Campaign::PERIODIC_MONTHLY_START` - 1st of month
- `Campaign::PERIODIC_MONTHLY_END` - Last day of month
- `Campaign::PERIODIC_SPECIFIC_DAY` - Specific day
- `Campaign::PERIODIC_BEGINNING_YEAR` - January 1st
- `Campaign::PERIODIC_CHRISTMAS` - December 25th

### Create Campaign with Template

```php
$response = $client->campaign()->createWithTemplate(
    name: 'Birthday Wishes',
    groups: ['group_birthday'],
    senderId: 'Wishes',
    templateId: 'tpl_birthday',
    templateLang: 'fr'
);
```

### Get Campaign Status

```php
$status = $client->campaign()->getStatus($campaignId);

echo "Status: " . $status->getStatus();
echo "Sent: " . $status->getSentCount();
echo "Delivered: " . $status->getDeliveredCount();
echo "Failed: " . $status->getFailedCount();
echo "Pending: " . $status->getPendingCount();
```

### Monitor Campaign Progress

```php
// Get progress percentage
$progress = $client->campaign()->getProgress($campaignId);
echo "Progress: {$progress}%";

// Check if completed
if ($client->campaign()->isCompleted($campaignId)) {
    echo "Campaign completed!";
}
```

## Error Handling

The SDK provides specific exceptions for different error types:

```php
use KyaSms\Exceptions\ValidationException;
use KyaSms\Exceptions\AuthenticationException;
use KyaSms\Exceptions\ApiException;
use KyaSms\Exceptions\KyaSmsException;

try {
    $response = $client->sms()->sendSimple('MyApp', '22990123456', 'Hello!');
} catch (ValidationException $e) {
    // Validation errors
    echo "Validation failed: " . $e->getMessage();
    foreach ($e->getErrors() as $field => $error) {
        echo "{$field}: {$error}";
    }
} catch (AuthenticationException $e) {
    // API key issues
    echo "Auth error: " . $e->getMessage();
} catch (ApiException $e) {
    // API errors (rate limit, server errors, etc.)
    echo "API error: " . $e->getMessage();
    echo "HTTP Status: " . $e->getHttpStatusCode();
} catch (KyaSmsException $e) {
    // Base exception for all SDK errors
    echo "Error: " . $e->getMessage();
}
```

### Specific Error Types

```php
// Rate limiting
catch (ApiException $e) {
    if ($e->getCode() === 429) {
        $retryAfter = $e->getContext()['retry_after'] ?? 60;
        sleep($retryAfter);
        // Retry...
    }
}

// Insufficient balance
catch (ApiException $e) {
    if (str_contains($e->getMessage(), 'balance')) {
        echo "Please recharge your wallet";
    }
}
```

## Advanced Usage

### Custom HTTP Client Access

```php
$httpClient = $client->getHttpClient();

// Make custom API calls
$response = $httpClient->post('/api/v3/custom/endpoint', [
    'custom_param' => 'value',
]);
```

### Fluent Interface

All models support a fluent interface for building requests:

```php
use KyaSms\Models\SmsMessage;
use KyaSms\Models\Campaign;

// SMS
$message = SmsMessage::create('MyApp', '22990123456')
    ->setMessage('Hello!')
    ->asFlash()
    ->setWallet('marketing')
    ->setRefCustom('ref123');

// Campaign
$campaign = Campaign::create('My Campaign', ['group1'], 'MyApp')
    ->asScheduled(new DateTime('+1 day'), 'Africa/Porto-Novo')
    ->setMessage('Scheduled message')
    ->setSmsType(Campaign::SMS_TYPE_UNICODE);
```

## Testing

```bash
# Run tests
composer test

# Run static analysis
composer analyse

# Check coding standards
composer cs-check

# Fix coding standards
composer cs-fix
```

## Project Structure

```
kya-sms-php/
├── src/
│   ├── KyaSms.php              # Main client (facade)
│   ├── Api/
│   │   ├── SmsApi.php          # SMS operations
│   │   ├── OtpApi.php          # OTP operations
│   │   └── CampaignApi.php     # Campaign operations
│   ├── Models/
│   │   ├── SmsMessage.php      # SMS message model
│   │   ├── SmsResponse.php     # SMS response model
│   │   ├── OtpRequest.php      # OTP request model
│   │   ├── OtpResponse.php     # OTP response model
│   │   ├── Campaign.php        # Campaign model
│   │   └── CampaignResponse.php
│   ├── Http/
│   │   └── HttpClient.php      # HTTP client with retry
│   └── Exceptions/
│       ├── KyaSmsException.php
│       ├── AuthenticationException.php
│       ├── ValidationException.php
│       └── ApiException.php
├── examples/
│   ├── send_sms.php
│   ├── send_otp.php
│   └── create_campaign.php
├── tests/
├── composer.json
└── README.md
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

- 📧 Email: support@kyasms.com
- 📖 Documentation: https://docs.kyasms.com
- 🐛 Issues: https://github.com/kyasms/kya-sms-php/issues
