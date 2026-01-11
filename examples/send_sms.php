<?php

/**
 * KYA SMS SDK - SMS Examples
 * 
 * This file demonstrates various ways to send SMS using the KYA SMS SDK.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use KyaSms\KyaSms;
use KyaSms\Models\SmsMessage;
use KyaSms\Exceptions\ValidationException;
use KyaSms\Exceptions\ApiException;
use KyaSms\Exceptions\AuthenticationException;

// Initialize the client
$client = new KyaSms('your-api-key', [
    'base_url' => 'https://route.kyasms.com/api/v3', // Optional, this is the default
    'debug' => true, // Enable debug mode for development
]);

// Or use environment variables
// $client = KyaSms::fromEnvironment();

try {
    // ========================================
    // Example 1: Send a simple SMS
    // ========================================
    echo "Example 1: Simple SMS\n";
    echo "----------------------\n";

    $response = $client->sms()->sendSimple(
        from: 'MyApp',
        to: '22990123456',
        message: 'Hello! This is a test message from KYA SMS.'
    );

    if ($response->isSuccess()) {
        echo "✅ SMS sent successfully!\n";
        echo "Task ID: " . ($response->getTaskId() ?? 'N/A') . "\n";
    }

    // ========================================
    // Example 2: Send SMS to multiple recipients
    // ========================================
    echo "\nExample 2: Multiple Recipients\n";
    echo "------------------------------\n";

    $response = $client->sms()->sendSimple(
        from: 'MyApp',
        to: ['22990123456', '22991234567', '22992345678'],
        message: 'Hello everyone!'
    );

    echo "Processed: " . $response->getProcessedCount() . " messages\n";

    // ========================================
    // Example 3: Send a Flash SMS
    // ========================================
    echo "\nExample 3: Flash SMS\n";
    echo "--------------------\n";

    $response = $client->sms()->sendFlash(
        from: 'Alert',
        to: '22990123456',
        message: 'URGENT: This message will appear directly on screen!'
    );

    echo "Flash SMS status: " . ($response->isSuccess() ? 'Sent' : 'Failed') . "\n";

    // ========================================
    // Example 4: Send SMS with Template
    // ========================================
    echo "\nExample 4: Template SMS\n";
    echo "-----------------------\n";

    $response = $client->sms()->sendWithTemplate(
        from: 'MyApp',
        to: '22990123456',
        templateId: 'tpl_welcome_message',
        lang: 'fr'
    );

    echo "Template SMS status: " . ($response->isSuccess() ? 'Sent' : 'Failed') . "\n";

    // ========================================
    // Example 5: Send Bulk SMS to Groups
    // ========================================
    echo "\nExample 5: Bulk SMS\n";
    echo "-------------------\n";

    $response = $client->sms()->sendBulk(
        from: 'Newsletter',
        groupIds: ['group_customers', 'group_subscribers'],
        message: 'Special offer just for you! Visit our website for more details.'
    );

    if ($response->isQueued()) {
        echo "✅ Bulk SMS queued for processing\n";
        echo "Task ID: " . $response->getTaskId() . "\n";
        echo "Total contacts: " . $response->getTotalContacts() . "\n";
    }

    // ========================================
    // Example 6: Advanced SMS with all options
    // ========================================
    echo "\nExample 6: Advanced SMS\n";
    echo "-----------------------\n";

    $message = SmsMessage::create('MyApp', '22990123456')
        ->setMessage('Your verification code is: 123456')
        ->setType(SmsMessage::TYPE_TEXT)
        ->setWallet('wallet_marketing')
        ->setRefCustom('order_12345')
        ->setSource(SmsMessage::SOURCE_API);

    $response = $client->sms()->send($message);

    echo "SMS sent with custom reference: " . $message->getRefCustom() . "\n";

    // ========================================
    // Example 7: Bulk SMS with Template
    // ========================================
    echo "\nExample 7: Bulk SMS with Template\n";
    echo "---------------------------------\n";

    $response = $client->sms()->sendBulkWithTemplate(
        from: 'Promo',
        groupIds: ['group_vip_customers'],
        templateId: 'tpl_promo_december',
        lang: 'en'
    );

    if ($response->isQueued()) {
        $queue = $response->getQueueProcessing();
        echo "Queued: " . ($queue['queued_contacts'] ?? 0) . " contacts\n";
    }

} catch (ValidationException $e) {
    echo "❌ Validation Error: " . $e->getMessage() . "\n";
    echo "Errors:\n";
    foreach ($e->getErrors() as $field => $error) {
        echo "  - {$field}: " . (is_array($error) ? implode(', ', $error) : $error) . "\n";
    }
} catch (AuthenticationException $e) {
    echo "❌ Authentication Error: " . $e->getMessage() . "\n";
} catch (ApiException $e) {
    echo "❌ API Error: " . $e->getMessage() . "\n";
    echo "HTTP Status: " . $e->getHttpStatusCode() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
