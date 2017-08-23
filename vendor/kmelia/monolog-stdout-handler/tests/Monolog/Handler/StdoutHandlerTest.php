<?php

namespace Monolog\Handler;

use Monolog\TestCase;
use Monolog\Logger;
use Monolog\Formatter\ColorLineFormatter;

class StdoutHandlerTest extends TestCase
{
    public function testDefaults()
    {
        $handler = new StdoutHandler();
        $this->assertSame(Logger::DEBUG, $handler->getLevel());
        $this->assertTrue($handler->getFormatter() instanceof ColorLineFormatter);
    }
}
