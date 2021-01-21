<?php declare(strict_types=1);

namespace johnnynotsolucky\RegexHandler;

use Monolog\Logger;
use Monolog\DateTimeImmutable;

class HandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return array Record
     */
    protected function getRecord($level = Logger::WARNING, $message = 'test', $channel = 'test', array $context = []): array
    {
        return [
            'message' => (string) $message,
            'context' => $context,
            'level' => $level,
            'level_name' => Logger::getLevelName($level),
            'channel' => $channel,
            'datetime' => new DateTimeImmutable(true),
            'extra' => [
                'nested' => [
                    'data' => 'Some nested data',
                ],
            ],
        ];
    }

    public function testNoPatternsBubblesRecords()
    {
        $handler = new Handler([]);
        $this->assertFalse($handler->handle($this->getRecord()));
    }

    public function testFiltersMessageByDefault()
    {
        $handler = new Handler([
            ['/^test$/'],
            '/^another test$/',
        ]);
        $this->assertTrue($handler->handle($this->getRecord()));
        $this->assertTrue($handler->handle($this->getRecord(Logger::INFO, 'another test')));
    }

    public function testFiltersByProperty()
    {
        $handler = new Handler([
            ['channel', '/^test$/'],
            ['level_name', '/^(INFO|DEBUG)$/'],
        ]);
        $this->assertTrue($handler->handle($this->getRecord()));
        $this->assertTrue($handler->handle($this->getRecord(Logger::DEBUG, 'test', 'testing')));
        $this->assertTrue($handler->handle($this->getRecord(Logger::INFO, 'test', 'testing')));
        $this->assertFalse($handler->handle($this->getRecord(Logger::WARNING, 'test', 'testing')));
    }

    public function testInvalidPropertyBubbles()
    {
        $handler = new Handler([
            ['invalid', '/^test$/'],
        ]);
        $this->assertFalse($handler->handle($this->getRecord()));
    }

    public function testMatchesValueFromPath()
    {
        $handler = new Handler([
            [['extra', 'nested', 'data'], '/^Some.*/'],
        ]);
        $this->assertTrue($handler->handle($this->getRecord()));
    }

    public function testMatchesRootValueFromPath()
    {
        $handler = new Handler([
            [['message'], '/^test$/'],
        ]);
        $this->assertTrue($handler->handle($this->getRecord()));
    }

    public function testInvalidPathBubbles()
    {
        $handler = new Handler([
            [['extra', 'nested', 'data', 'invalid'], '/.+/'],
            [['invalid'], '/.+/'],
        ]);
        $this->assertFalse($handler->handle($this->getRecord()));
    }
}
