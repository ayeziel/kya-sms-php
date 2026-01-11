<?php

declare(strict_types=1);

namespace KyaSms\Tests\Unit;

use PHPUnit\Framework\TestCase;
use KyaSms\Models\OtpRequest;
use InvalidArgumentException;

class OtpRequestTest extends TestCase
{
    public function testCreateSimpleRequest(): void
    {
        $request = OtpRequest::create('app_123', '22990123456', 'fr');

        $this->assertEquals('app_123', $request->getAppId());
        $this->assertEquals('22990123456', $request->getRecipient());
        $this->assertEquals('fr', $request->getLang());
        $this->assertNull($request->getCode());
        $this->assertNull($request->getMinutes());
    }

    public function testDefaultLanguage(): void
    {
        $request = OtpRequest::create('app_123', '22990123456');

        $this->assertEquals('fr', $request->getLang());
    }

    public function testSetCustomCode(): void
    {
        $request = OtpRequest::create('app_123', '22990123456', 'en')
            ->setCode('ABC123');

        $this->assertEquals('ABC123', $request->getCode());
    }

    public function testSetMinutes(): void
    {
        $request = OtpRequest::create('app_123', '22990123456', 'fr')
            ->setMinutes(10);

        $this->assertEquals(10, $request->getMinutes());
    }

    public function testExpiresIn5Minutes(): void
    {
        $request = OtpRequest::create('app_123', '22990123456', 'fr')
            ->expiresIn5Minutes();

        $this->assertEquals(5, $request->getMinutes());
    }

    public function testExpiresIn10Minutes(): void
    {
        $request = OtpRequest::create('app_123', '22990123456', 'fr')
            ->expiresIn10Minutes();

        $this->assertEquals(10, $request->getMinutes());
    }

    public function testExpiresIn15Minutes(): void
    {
        $request = OtpRequest::create('app_123', '22990123456', 'fr')
            ->expiresIn15Minutes();

        $this->assertEquals(15, $request->getMinutes());
    }

    public function testAllSupportedLanguages(): void
    {
        $languages = ['fr', 'en', 'es', 'de'];

        foreach ($languages as $lang) {
            $request = OtpRequest::create('app_123', '22990123456', $lang);
            $this->assertEquals($lang, $request->getLang());
        }
    }

    public function testToArray(): void
    {
        $request = OtpRequest::create('app_123', '22990123456', 'en')
            ->setCode('123456')
            ->setMinutes(5);

        $array = $request->toArray();

        $this->assertEquals('app_123', $array['appId']);
        $this->assertEquals('22990123456', $array['recipient']);
        $this->assertEquals('en', $array['lang']);
        $this->assertEquals('123456', $array['code']);
        $this->assertEquals(5, $array['minutes']);
    }

    public function testToArrayWithoutOptionalFields(): void
    {
        $request = OtpRequest::create('app_123', '22990123456', 'fr');

        $array = $request->toArray();

        $this->assertArrayHasKey('appId', $array);
        $this->assertArrayHasKey('recipient', $array);
        $this->assertArrayHasKey('lang', $array);
        $this->assertArrayNotHasKey('code', $array);
        $this->assertArrayNotHasKey('minutes', $array);
    }

    public function testInvalidLanguage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Language must be one of: fr, en, es, de');

        OtpRequest::create('app_123', '22990123456', 'invalid');
    }

    public function testCodeMaxLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Code must be 10 characters or less');

        OtpRequest::create('app_123', '22990123456', 'fr')
            ->setCode('12345678901');
    }

    public function testMinutesMinValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Minutes must be at least 1');

        OtpRequest::create('app_123', '22990123456', 'fr')
            ->setMinutes(0);
    }

    public function testFluentInterface(): void
    {
        $request = OtpRequest::create('app_123', '22990123456', 'fr')
            ->setAppId('app_updated')
            ->setRecipient('22991234567')
            ->setLang('en')
            ->setCode('ABCD')
            ->setMinutes(15);

        $this->assertEquals('app_updated', $request->getAppId());
        $this->assertEquals('22991234567', $request->getRecipient());
        $this->assertEquals('en', $request->getLang());
        $this->assertEquals('ABCD', $request->getCode());
        $this->assertEquals(15, $request->getMinutes());
    }
}
