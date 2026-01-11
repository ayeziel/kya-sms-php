<?php

declare(strict_types=1);

namespace KyaSms\Tests\Unit;

use PHPUnit\Framework\TestCase;
use KyaSms\Models\Campaign;
use InvalidArgumentException;
use DateTime;

class CampaignTest extends TestCase
{
    public function testCreateAutomaticCampaign(): void
    {
        $campaign = Campaign::create('Test Campaign', ['group1'], 'MyApp')
            ->asAutomatic()
            ->setMessage('Hello World!');

        $this->assertEquals('Test Campaign', $campaign->getName());
        $this->assertEquals(['group1'], $campaign->getGroups());
        $this->assertEquals('MyApp', $campaign->getSenderId());
        $this->assertEquals('auto', $campaign->getType());
        $this->assertNull($campaign->getScheduleDate());
        $this->assertNull($campaign->getCampaignPeriodic());
    }

    public function testCreateScheduledCampaign(): void
    {
        $scheduleDate = new DateTime('2025-02-01 10:00:00');
        
        $campaign = Campaign::create('Scheduled', ['group1'], 'MyApp')
            ->asScheduled($scheduleDate, 'Africa/Porto-Novo')
            ->setMessage('Scheduled message');

        $this->assertEquals('customize', $campaign->getType());
        $this->assertEquals('2025-02-01 10:00:00', $campaign->getScheduleDate());
        $this->assertEquals('Africa/Porto-Novo', $campaign->getTimezone());
    }

    public function testCreateScheduledCampaignWithStringDate(): void
    {
        $campaign = Campaign::create('Scheduled', ['group1'], 'MyApp')
            ->asScheduled('2025-03-15 14:30:00', 'UTC')
            ->setMessage('Message');

        $this->assertEquals('2025-03-15 14:30:00', $campaign->getScheduleDate());
    }

    public function testCreatePeriodicCampaign(): void
    {
        $campaign = Campaign::create('Weekly', ['group1'], 'MyApp')
            ->asPeriodic(Campaign::PERIODIC_WEEKLY_END, 'Africa/Porto-Novo')
            ->setMessage('Happy weekend!');

        $this->assertEquals('periodic', $campaign->getType());
        $this->assertEquals('weekly_end', $campaign->getCampaignPeriodic());
    }

    public function testAllPeriodicTypes(): void
    {
        $types = [
            Campaign::PERIODIC_WEEKLY_START,
            Campaign::PERIODIC_WEEKLY_END,
            Campaign::PERIODIC_MONTHLY_START,
            Campaign::PERIODIC_MONTHLY_END,
            Campaign::PERIODIC_SPECIFIC_DAY,
            Campaign::PERIODIC_BEGINNING_YEAR,
            Campaign::PERIODIC_CHRISTMAS,
        ];

        foreach ($types as $type) {
            $campaign = Campaign::create('Test', ['g1'], 'App')
                ->asPeriodic($type);
            
            $this->assertEquals($type, $campaign->getCampaignPeriodic());
        }
    }

    public function testSetMessage(): void
    {
        $campaign = Campaign::create('Test', ['group1'], 'MyApp')
            ->setMessage('Test message');

        $content = $campaign->getContent();
        
        $this->assertEquals('message', $content['type']);
        $this->assertEquals('Test message', $content['message']);
    }

    public function testUseTemplate(): void
    {
        $campaign = Campaign::create('Test', ['group1'], 'MyApp')
            ->useTemplate('tpl_welcome', 'en');

        $content = $campaign->getContent();

        $this->assertEquals('template', $content['type']);
        $this->assertEquals('tpl_welcome', $content['template_id']);
        $this->assertEquals('en', $content['template_default_lang']);
    }

    public function testSetSmsType(): void
    {
        $campaign = Campaign::create('Test', ['group1'], 'MyApp')
            ->setSmsType(Campaign::SMS_TYPE_FLASH);

        $this->assertEquals('flash', $campaign->getSmsType());
    }

    public function testAddGroup(): void
    {
        $campaign = Campaign::create('Test', ['group1'], 'MyApp')
            ->addGroup('group2')
            ->addGroup('group3')
            ->addGroup('group1'); // Duplicate should be ignored

        $this->assertEquals(['group1', 'group2', 'group3'], $campaign->getGroups());
    }

    public function testToArray(): void
    {
        $campaign = Campaign::create('Test Campaign', ['g1', 'g2'], 'Sender')
            ->asScheduled('2025-01-15 09:00:00', 'UTC')
            ->setMessage('Hello')
            ->setSmsType(Campaign::SMS_TYPE_TEXT);

        $array = $campaign->toArray();

        $this->assertEquals('Test Campaign', $array['name']);
        $this->assertEquals('customize', $array['type']);
        $this->assertEquals(['g1', 'g2'], $array['groups']);
        $this->assertEquals('Sender', $array['sender_id']);
        $this->assertEquals('text', $array['sms_type']);
        $this->assertEquals('2025-01-15 09:00:00', $array['schedule_date']);
        $this->assertEquals('UTC', $array['timezone']);
        $this->assertArrayHasKey('content', $array);
    }

    public function testInvalidSenderIdLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Sender ID must be 11 characters or less');

        Campaign::create('Test', ['g1'], 'ThisIsTooLong');
    }

    public function testInvalidCampaignType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Type must be one of');

        Campaign::create('Test', ['g1'], 'App')
            ->setType('invalid');
    }

    public function testInvalidSmsType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('SMS type must be one of');

        Campaign::create('Test', ['g1'], 'App')
            ->setSmsType('invalid');
    }

    public function testInvalidPeriodicType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Periodic type must be one of');

        Campaign::create('Test', ['g1'], 'App')
            ->asPeriodic('invalid');
    }

    public function testInvalidTemplateLang(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Language must be one of');

        Campaign::create('Test', ['g1'], 'App')
            ->useTemplate('tpl_1', 'invalid');
    }

    public function testInvalidScheduleDateFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Schedule date must be in format');

        Campaign::create('Test', ['g1'], 'App')
            ->asScheduled('invalid-date');
    }

    public function testCampaignNameMaxLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Campaign name must be 255 characters or less');

        $longName = str_repeat('a', 256);
        Campaign::create($longName, ['g1'], 'App');
    }

    public function testFluentInterface(): void
    {
        $campaign = Campaign::create('Initial', ['g1'], 'App')
            ->setName('Updated Name')
            ->setGroups(['g2', 'g3'])
            ->setSenderId('NewSender')
            ->asAutomatic()
            ->setMessage('New message')
            ->setSmsType(Campaign::SMS_TYPE_UNICODE)
            ->setTimezone('Europe/Paris');

        $this->assertEquals('Updated Name', $campaign->getName());
        $this->assertEquals(['g2', 'g3'], $campaign->getGroups());
        $this->assertEquals('NewSender', $campaign->getSenderId());
        $this->assertEquals('auto', $campaign->getType());
        $this->assertEquals('unicode', $campaign->getSmsType());
        $this->assertEquals('Europe/Paris', $campaign->getTimezone());
    }
}
