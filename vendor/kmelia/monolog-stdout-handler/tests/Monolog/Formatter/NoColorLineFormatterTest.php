<?php

namespace Monolog\Formatter;

use Monolog\TestCase;
use Monolog\Logger;
use Monolog\Handler\StdoutHandler;

class NoColorLineFormatterTest extends TestCase
{
    private $formatter;
    
    public function setUp()
    {
        $this->formatter = new NoColorLineFormatter(StdoutHandler::FORMAT);
    }
    
    private function getFormattedMessage($colorName)
    {
        $message = sprintf('[info][c=%1$s]black[/c] and [c=%1$s]white[/c]!', $colorName);
        
        return $this->formatter->format(
            $this->getRecord(Logger::INFO, $message)
        );
    }
    
    /**
     * @dataProvider providerTestColor
     */
    public function testRealColor($colorName)
    {
        $expected = "[info]black and white!\n";
        $this->assertSame($expected, $this->getFormattedMessage($colorName));
    }
    
    public function providerTestColor()
    {
        return array(
            array('black'),
            array('red'),
            array('green'),
            array('yellow'),
            array('blue'),
            array('purple'),
            array('cyan'),
            array('white'),
            array('starwars'),
            array('color'),
            array('other'),
            array('null'),
        );
    }
    
    public function testOtherCode()
    {
        $message = '[[c=red]/!\[/c]]use [u][c=blue]no color[/c][/u] with care!';
        $expected = "[/!\]use [u]no color[/u] with care!\n";
        
        $this->assertSame($expected, $this->formatter->format($this->getRecord(Logger::ERROR, $message)));
    }
}
