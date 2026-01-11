<?php

/**
 * KYA SMS SDK - OTP Examples
 * 
 * This file demonstrates various ways to send OTP using the KYA SMS SDK.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use KyaSms\KyaSms;
use KyaSms\Models\OtpRequest;
use KyaSms\Exceptions\ValidationException;
use KyaSms\Exceptions\ApiException;
use KyaSms\Exceptions\AuthenticationException;

// Initialize the client
$client = new KyaSms('your-api-key');

try {
    // ========================================
    // Example 1: Simple OTP
    // ========================================
    echo "Example 1: Simple OTP\n";
    echo "---------------------\n";

    $response = $client->otp()->send(
        appId: 'app_verification',
        recipient: '22990123456',
        lang: 'fr'
    );

    if ($response->isSuccess()) {
        echo "✅ OTP sent successfully!\n";
        echo "Reference Key: " . $response->getKey() . "\n";
        echo "Use this key to verify the OTP later.\n";
    }

    // ========================================
    // Example 2: OTP with English language
    // ========================================
    echo "\nExample 2: OTP in English\n";
    echo "-------------------------\n";

    $response = $client->otp()->send(
        appId: 'app_login',
        recipient: '22991234567',
        lang: 'en'
    );

    echo "OTP Key: " . $response->getKey() . "\n";

    // ========================================
    // Example 3: OTP with Custom Code
    // ========================================
    echo "\nExample 3: OTP with Custom Code\n";
    echo "-------------------------------\n";

    // Useful when your application generates its own OTP codes
    $customCode = (string) random_int(100000, 999999);

    $response = $client->otp()->sendWithCustomCode(
        appId: 'app_custom_otp',
        recipient: '22990123456',
        code: $customCode,
        lang: 'fr',
        minutes: 5 // Expires in 5 minutes
    );

    if ($response->isSuccess()) {
        echo "✅ Custom OTP sent!\n";
        echo "Code sent: {$customCode}\n";
        echo "Reference: " . $response->getKey() . "\n";
    }

    // ========================================
    // Example 4: OTP with Expiration Time
    // ========================================
    echo "\nExample 4: OTP with Expiration\n";
    echo "------------------------------\n";

    $response = $client->otp()->sendWithExpiration(
        appId: 'app_secure_login',
        recipient: '22990123456',
        minutes: 10, // 10 minutes validity
        lang: 'fr'
    );

    echo "OTP expires at: " . ($response->getExpireAt() ?? 'Not specified') . "\n";

    // ========================================
    // Example 5: Advanced OTP Request
    // ========================================
    echo "\nExample 5: Advanced OTP Request\n";
    echo "-------------------------------\n";

    $otpRequest = OtpRequest::create(
        appId: 'app_2fa',
        recipient: '22990123456',
        lang: OtpRequest::LANG_FR
    )
    ->expiresIn5Minutes(); // Convenience method

    $response = $client->otp()->initiate($otpRequest);

    if ($response->isSuccess()) {
        echo "✅ 2FA OTP sent!\n";
        echo "Key: " . $response->getKey() . "\n";
    }

    // ========================================
    // Example 6: OTP for Email
    // ========================================
    echo "\nExample 6: OTP for Email (if configured)\n";
    echo "-----------------------------------------\n";

    // Note: Your OTP application must be configured to support email
    $response = $client->otp()->send(
        appId: 'app_email_verification',
        recipient: 'user@example.com',
        lang: 'en'
    );

    echo "Email OTP Key: " . $response->getKey() . "\n";

    // ========================================
    // Example 7: OTP with All Languages
    // ========================================
    echo "\nExample 7: Multi-language OTP Support\n";
    echo "--------------------------------------\n";

    $languages = [
        OtpRequest::LANG_FR => 'French',
        OtpRequest::LANG_EN => 'English',
        OtpRequest::LANG_ES => 'Spanish',
        OtpRequest::LANG_DE => 'German',
    ];

    foreach ($languages as $langCode => $langName) {
        echo "Sending OTP in {$langName}...\n";
        // In production, you would only send to the user's preferred language
    }

    // ========================================
    // Typical Flow: Send and Store Reference
    // ========================================
    echo "\n=== Typical OTP Verification Flow ===\n";
    echo "-------------------------------------\n";

    // Step 1: User requests OTP
    $response = $client->otp()->send('app_login', '22990123456', 'fr');
    
    if ($response->isSuccess()) {
        $otpKey = $response->getKey();
        
        // Step 2: Store the key in your database/session
        // In production: 
        // $_SESSION['otp_key'] = $otpKey;
        // Or store in database: $user->otp_key = $otpKey;
        
        echo "1. OTP sent to user's phone\n";
        echo "2. Store reference key: {$otpKey}\n";
        echo "3. User enters the code they received\n";
        echo "4. Verify using your OTP verification endpoint\n";
    }

} catch (ValidationException $e) {
    echo "❌ Validation Error: " . $e->getMessage() . "\n";
    foreach ($e->getErrors() as $field => $error) {
        echo "  - {$field}: " . (is_array($error) ? implode(', ', $error) : $error) . "\n";
    }
} catch (AuthenticationException $e) {
    echo "❌ Authentication Error: " . $e->getMessage() . "\n";
} catch (ApiException $e) {
    echo "❌ API Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
