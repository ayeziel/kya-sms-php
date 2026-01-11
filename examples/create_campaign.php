<?php

/**
 * KYA SMS SDK - Campaign Examples
 * 
 * This file demonstrates various ways to create and manage campaigns.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use KyaSms\KyaSms;
use KyaSms\Models\Campaign;
use KyaSms\Exceptions\ValidationException;
use KyaSms\Exceptions\ApiException;
use KyaSms\Exceptions\AuthenticationException;

// Initialize the client
$client = new KyaSms('your-api-key');

try {
    // ========================================
    // Example 1: Create an Immediate Campaign
    // ========================================
    echo "Example 1: Immediate Campaign\n";
    echo "-----------------------------\n";

    $response = $client->campaign()->createAutomatic(
        name: 'Flash Sale Announcement',
        groups: ['group_customers', 'group_subscribers'],
        senderId: 'MyStore',
        message: '🎉 Flash Sale! 50% off all items. Visit our store today!'
    );

    if ($response->isSuccess()) {
        echo "✅ Campaign created successfully!\n";
        echo "Campaign ID: " . $response->getCampaignId() . "\n";
        echo "Total contacts: " . $response->getTotalContacts() . "\n";
    }

    // ========================================
    // Example 2: Create a Scheduled Campaign
    // ========================================
    echo "\nExample 2: Scheduled Campaign\n";
    echo "-----------------------------\n";

    // Schedule for next Monday at 9:00 AM
    $scheduleDate = new DateTime('next monday 09:00:00');

    $response = $client->campaign()->createScheduled(
        name: 'Weekly Newsletter',
        groups: ['group_newsletter'],
        senderId: 'News',
        message: 'Good morning! Here is your weekly update...',
        scheduleDate: $scheduleDate,
        timezone: 'Africa/Porto-Novo'
    );

    if ($response->isSuccess()) {
        echo "✅ Scheduled campaign created!\n";
        echo "Campaign ID: " . $response->getCampaignId() . "\n";
        echo "Execution date: " . $response->getExecutionDate() . "\n";
    }

    // ========================================
    // Example 3: Create a Periodic Campaign
    // ========================================
    echo "\nExample 3: Periodic Campaign (Weekly)\n";
    echo "--------------------------------------\n";

    $response = $client->campaign()->createPeriodic(
        name: 'Weekend Reminder',
        groups: ['group_members'],
        senderId: 'Club',
        message: 'Happy weekend! Don\'t forget to check our latest offers.',
        periodic: Campaign::PERIODIC_WEEKLY_END,
        timezone: 'Africa/Porto-Novo'
    );

    if ($response->isSuccess()) {
        echo "✅ Periodic campaign created!\n";
        echo "Campaign ID: " . $response->getCampaignId() . "\n";
        echo "Schedule: Every weekend\n";
    }

    // ========================================
    // Example 4: Campaign with Template
    // ========================================
    echo "\nExample 4: Campaign with Template\n";
    echo "---------------------------------\n";

    $response = $client->campaign()->createWithTemplate(
        name: 'Birthday Wishes Campaign',
        groups: ['group_birthday_today'],
        senderId: 'Wishes',
        templateId: 'tpl_birthday',
        templateLang: 'fr'
    );

    echo "Template campaign status: " . ($response->isSuccess() ? 'Created' : 'Failed') . "\n";

    // ========================================
    // Example 5: Advanced Campaign
    // ========================================
    echo "\nExample 5: Advanced Campaign\n";
    echo "----------------------------\n";

    $campaign = Campaign::create(
        name: 'Christmas Special Offer',
        groups: ['group_vip', 'group_premium'],
        senderId: 'Santa'
    )
    ->asPeriodic(Campaign::PERIODIC_CHRISTMAS, 'Africa/Porto-Novo')
    ->setSmsType(Campaign::SMS_TYPE_TEXT)
    ->setMessage('🎄 Merry Christmas! Enjoy 30% off with code XMAS2024');

    $response = $client->campaign()->create($campaign);

    if ($response->isSuccess()) {
        echo "✅ Christmas campaign ready!\n";
        echo "Campaign ID: " . $response->getCampaignId() . "\n";
    }

    // ========================================
    // Example 6: Get Campaign Status
    // ========================================
    echo "\nExample 6: Campaign Status\n";
    echo "--------------------------\n";

    $campaignId = 123; // Replace with actual campaign ID

    $status = $client->campaign()->getStatus($campaignId);

    echo "Campaign Status: " . $status->getStatus() . "\n";
    
    if ($status->getProgress()) {
        echo "Progress:\n";
        echo "  - Total: " . ($status->getProgress()['total'] ?? 0) . "\n";
        echo "  - Sent: " . $status->getSentCount() . "\n";
        echo "  - Delivered: " . $status->getDeliveredCount() . "\n";
        echo "  - Failed: " . $status->getFailedCount() . "\n";
        echo "  - Pending: " . $status->getPendingCount() . "\n";
    }

    // ========================================
    // Example 7: Monitor Campaign Progress
    // ========================================
    echo "\nExample 7: Monitor Campaign\n";
    echo "---------------------------\n";

    $campaignId = 456; // Replace with actual campaign ID
    
    // Check progress percentage
    $progress = $client->campaign()->getProgress($campaignId);
    echo "Progress: " . round($progress, 2) . "%\n";

    // Check if completed
    $isCompleted = $client->campaign()->isCompleted($campaignId);
    echo "Status: " . ($isCompleted ? 'Completed' : 'In Progress') . "\n";

    // ========================================
    // Example 8: All Periodic Types
    // ========================================
    echo "\nExample 8: Available Periodic Types\n";
    echo "------------------------------------\n";

    $periodicTypes = [
        Campaign::PERIODIC_WEEKLY_START => 'Start of week (Monday)',
        Campaign::PERIODIC_WEEKLY_END => 'End of week (Friday/Saturday)',
        Campaign::PERIODIC_MONTHLY_START => 'Start of month (1st)',
        Campaign::PERIODIC_MONTHLY_END => 'End of month',
        Campaign::PERIODIC_SPECIFIC_DAY => 'Specific day of month',
        Campaign::PERIODIC_BEGINNING_YEAR => 'Beginning of year (January 1st)',
        Campaign::PERIODIC_CHRISTMAS => 'Christmas (December 25th)',
    ];

    foreach ($periodicTypes as $type => $description) {
        echo "  - {$type}: {$description}\n";
    }

    // ========================================
    // Example 9: Campaign Polling
    // ========================================
    echo "\nExample 9: Poll Campaign Until Complete\n";
    echo "----------------------------------------\n";

    $campaignId = 789; // Replace with actual campaign ID
    $maxAttempts = 10;
    $sleepSeconds = 5;

    echo "Polling campaign {$campaignId}...\n";

    /*
    // Uncomment in production:
    for ($i = 0; $i < $maxAttempts; $i++) {
        $status = $client->campaign()->getStatus($campaignId);
        $progress = $client->campaign()->getProgress($campaignId);
        
        echo "Attempt " . ($i + 1) . ": " . round($progress, 1) . "% complete\n";
        
        if ($client->campaign()->isCompleted($campaignId)) {
            echo "✅ Campaign completed!\n";
            echo "Delivered: " . $status->getDeliveredCount() . "\n";
            echo "Failed: " . $status->getFailedCount() . "\n";
            break;
        }
        
        sleep($sleepSeconds);
    }
    */

    echo "(Polling example - uncomment to run)\n";

} catch (ValidationException $e) {
    echo "❌ Validation Error: " . $e->getMessage() . "\n";
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
