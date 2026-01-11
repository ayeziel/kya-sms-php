<?php

/**
 * KYA SMS SDK - OTP Examples
 * 
 * This file demonstrates various ways to send and verify OTP using the KYA SMS SDK.
 * 
 * Endpoints used:
 * - POST /otp/create - Send OTP
 * - POST /otp/verify - Verify OTP
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
    // Example 2: OTP in English
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

    $response = $client->otp()->create($otpRequest);

    if ($response->isSuccess()) {
        echo "✅ 2FA OTP sent!\n";
        echo "Key: " . $response->getKey() . "\n";
    }

    // ========================================
    // Example 6: Verify OTP Code
    // ========================================
    echo "\nExample 6: Verify OTP Code\n";
    echo "--------------------------\n";

    // First, send an OTP
    $sendResponse = $client->otp()->send('app_login', '22990123456', 'fr');
    $otpKey = $sendResponse->getKey();

    // Simulate user entering the code
    $userEnteredCode = '123456'; // In real app, this comes from user input

    // Verify the code
    $verifyResult = $client->otp()->verify(
        appId: 'app_login',
        key: $otpKey,
        code: $userEnteredCode
    );

    if ($client->otp()->isVerified($verifyResult)) {
        echo "✅ OTP verified successfully!\n";
    } else {
        echo "❌ Verification failed: {$verifyResult['msg']}\n";
        
        // Handle specific error codes
        switch ($verifyResult['status']) {
            case 100:
                echo "Invalid verification key\n";
                break;
            case 101:
                echo "Max attempts reached or IP changed\n";
                break;
            case 102:
                echo "Incorrect code - try again\n";
                break;
            case 103:
                echo "Code expired - request a new one\n";
                break;
        }
    }

    // ========================================
    // Example 7: Complete OTP Flow
    // ========================================
    echo "\n=== Complete OTP Verification Flow ===\n";
    echo "--------------------------------------\n";

    // Step 1: User requests OTP (e.g., for login)
    echo "Step 1: Sending OTP...\n";
    $response = $client->otp()->send('app_login', '22990123456', 'fr');
    
    if ($response->isSuccess()) {
        $otpKey = $response->getKey();
        
        // Step 2: Store the key (in session, database, etc.)
        echo "Step 2: Store key: {$otpKey}\n";
        // $_SESSION['otp_key'] = $otpKey;
        // $_SESSION['otp_app_id'] = 'app_login';
        
        // Step 3: Wait for user to enter the code
        echo "Step 3: Waiting for user to enter code...\n";
        // In real app: get code from form submission
        $codeFromUser = '123456';
        
        // Step 4: Verify the code
        echo "Step 4: Verifying code...\n";
        $result = $client->otp()->verify('app_login', $otpKey, $codeFromUser);
        
        if ($client->otp()->isVerified($result)) {
            echo "✅ User authenticated successfully!\n";
            // Allow login, complete action, etc.
        } else {
            echo "❌ Authentication failed\n";
        }
    }

    // ========================================
    // Example 8: Multi-language OTP Support
    // ========================================
    echo "\nExample 8: Multi-language OTP Support\n";
    echo "--------------------------------------\n";

    $languages = [
        OtpRequest::LANG_FR => 'French',
        OtpRequest::LANG_EN => 'English',
        OtpRequest::LANG_ES => 'Spanish',
        OtpRequest::LANG_DE => 'German',
    ];

    foreach ($languages as $langCode => $langName) {
        echo "Available language: {$langName} ({$langCode})\n";
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
