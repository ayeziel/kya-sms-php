<?php

declare(strict_types=1);

namespace KyaSms\Tests\Unit;

use PHPUnit\Framework\TestCase;
use KyaSms\Models\SmsMessage;
use InvalidArgumentException;

class SmsMessageTest extends TestCase
{
    public function testCreateSimpleMessage(): void
    {
        $message = SmsMessage::create('MyApp', '22990123456')
            ->setMessage('Hello World!');

        $this->assertEquals('MyApp', $message->getFrom());
        $this->assertEquals('22990123456', $message->getTo());
        $this->assertEquals('Hello World!', $message->getMessage());
        $this->assertEquals('text', $message->getType());
        $this->assertFalse($message->isBulk());
        $this->assertFalse($message->isTemplate());
    }

    public function testFlashMessage(): void
    {
        $message = SmsMessage::create('Alert', '22990123456')
            ->setMessage('Flash!')
            ->asFlash();

        $this->assertEquals('flash', $message->getType());
    }

    public function testMultipleRecipients(): void
    {
        $message = SmsMessage::create('MyApp', '22990123456')
            ->setTo(['22990123456', '22991234567', '22992345678'])
            ->setMessage('Hello');

        $this->assertEquals('22990123456,22991234567,22992345678', $message->getTo());
    }

    public function testTemplateMessage(): void
    {
        $message = SmsMessage::create('MyApp', '22990123456')
            ->useTemplate('tpl_welcome', 'en');

        $this->assertTrue($message->isTemplate());
        $this->assertNull($message->getMessage());
        $this->assertEquals(['id' => 'tpl_welcome', 'lang' => 'en'], $message->getTemplate());
    }

    public function testBulkMessage(): void
    {
        $message = SmsMessage::create('MyApp', 'group1,group2')
            ->setMessage('Bulk message')
            ->setBulk(true);

        $this->assertTrue($message->isBulk());
    }

    public function testCustomReference(): void
    {
        $message = SmsMessage::create('MyApp', '22990123456')
            ->setMessage('Hello')
            ->setRefCustom('order_12345');

        $this->assertEquals('order_12345', $message->getRefCustom());
    }

    public function testWalletSelection(): void
    {
        $message = SmsMessage::create('MyApp', '22990123456')
            ->setMessage('Hello')
            ->setWallet('wallet_marketing');

        $this->assertEquals('wallet_marketing', $message->getWallet());
    }

    public function testSourceSetting(): void
    {
        $message = SmsMessage::create('MyApp', '22990123456')
            ->setMessage('Hello')
            ->setSource(SmsMessage::SOURCE_API);

        $this->assertEquals(2, $message->getSource());
    }

    public function testToArray(): void
    {
        $message = SmsMessage::create('MyApp', '22990123456')
            ->setMessage('Hello World!')
            ->setWallet('wallet_1')
            ->setRefCustom('ref_123');

        $array = $message->toArray();

        $this->assertEquals('MyApp', $array['from']);
        $this->assertEquals('22990123456', $array['to']);
        $this->assertEquals('Hello World!', $array['message']);
        $this->assertEquals('text', $array['type']);
        $this->assertEquals('wallet_1', $array['wallet']);
        $this->assertEquals('ref_123', $array['ref_custom']);
        $this->assertFalse($array['isBulk']);
        $this->assertFalse($array['isTemplate']);
    }

    public function testSenderIdMaxLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Sender ID must be 11 characters or less');

        SmsMessage::create('ThisIsTooLongForASenderId', '22990123456');
    }

    public function testMessageMaxLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Message must be 459 characters or less');

        $longMessage = str_repeat('a', 460);
        SmsMessage::create('MyApp', '22990123456')
            ->setMessage($longMessage);
    }

    public function testInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Type must be "text" or "flash"');

        SmsMessage::create('MyApp', '22990123456')
            ->setType('invalid');
    }

    public function testInvalidTemplateLang(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Language must be one of: fr, en, es, de, default');

        SmsMessage::create('MyApp', '22990123456')
            ->useTemplate('tpl_1', 'invalid');
    }

    public function testInvalidSource(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Source must be between 1 and 5');

        SmsMessage::create('MyApp', '22990123456')
            ->setSource(10);
    }

    public function testCustomRefMaxLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Custom reference must be 100 characters or less');

        $longRef = str_repeat('a', 101);
        SmsMessage::create('MyApp', '22990123456')
            ->setRefCustom($longRef);
    }
}
