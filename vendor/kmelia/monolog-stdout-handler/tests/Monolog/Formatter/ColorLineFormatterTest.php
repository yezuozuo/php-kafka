<?php

namespace Monolog\Formatter;

use Monolog\TestCase;
use Monolog\Logger;
use Monolog\Handler\StdoutHandler;

class ColorLineFormatterTest extends TestCase
{
    private $formatter;
    
    public function setUp()
    {
        $this->formatter = new ColorLineFormatter(StdoutHandler::FORMAT);
    }
    
    private function getFormattedMessage($colorName)
    {
        $message = sprintf('[error][c=%s]core dumped[/c].', $colorName);
        
        return $this->formatter->format(
            $this->getRecord(Logger::ERROR, $message)
        );
    }
    
    /**
     * @dataProvider providerTestColor
     */
    public function testRealColor($colorName, $colorValue)
    {
        $expected = sprintf("[error]\033[%dmcore dumped\033[0m.\n", $colorValue);
        $this->assertSame($expected, $this->getFormattedMessage($colorName));
    }
    
    public function providerTestColor()
    {
        return array(
            array('none',   0),
            array('black',  30),
            array('red',    31),
            array('green',  32),
            array('yellow', 33),
            array('blue',   34),
            array('purple', 35),
            array('cyan',   36),
            array('white',  37),
        );
    }
    
    /**
     * @dataProvider providerTestUnknownColor
     */
    public function testUnknownColor($colorName)
    {
        $expected = "[error]\033[0mcore dumped\033[0m.\n";
        $this->assertSame($expected, $this->getFormattedMessage($colorName));
    }
    
    public function providerTestUnknownColor()
    {
        return array(
            array('foo'),
            array('bar'),
        );
    }
    
    /**
     * @dataProvider providerTestBadValue
     */
    public function testBadValue($colorName)
    {
        $expected = sprintf("[error][c=%s]core dumped\033[0m.\n", $colorName);
        $this->assertSame($expected, $this->getFormattedMessage($colorName));
    }
    
    public function providerTestBadValue()
    {
        return array(
            array(null),
            array(''),
            array(' '),
            array('red2'),
            array('red-light'),
            array('dark_red'),
        );
    }
    
    public function testOtherCode()
    {
        $message = '[[c=yellow]warning[/c]][c=green][b]huge[/b] [comment]packet[/comment][/c] [c=white]is coming[/c].';
        $expected = "[\033[33mwarning\033[0m]\033[32m[b]huge[/b] [comment]packet[/comment]\033[0m \033[37mis coming\033[0m.\n";
        
        $this->assertSame($expected, $this->formatter->format($this->getRecord(Logger::ERROR, $message)));
    }
    
    /**
     * @dataProvider providerTestInvalidRenderShellColor
     * @expectedException \LogicException
     */
    public function testInvalidRenderShellColor($id)
    {
        ColorLineFormatter::renderShellColor($id);
    }
    
    public function providerTestInvalidRenderShellColor()
    {
        return array(
            array(null),
            array(''),
            array(' '),
            array(true),
            array(false),
            array(new \StdClass()),
        );
    }
}
