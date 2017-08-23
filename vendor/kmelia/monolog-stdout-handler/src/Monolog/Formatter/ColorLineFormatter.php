<?php

namespace Monolog\Formatter;

/**
 * Formats incoming records into a one-line colored string
 */
class ColorLineFormatter extends LineFormatter
{
    const
        COLOR_PATTERN = '~\[(?<closingSlash>/?)c(?:=(?<valueParameter>[a-z]+))?\]~',
        NONE_COLOR    = 0;
    
    private $colors = array(
        'black'  => 30,
        'red'    => 31,
        'green'  => 32,
        'yellow' => 33,
        'blue'   => 34,
        'purple' => 35,
        'cyan'   => 36,
        'white'  => 37,
    );
    
    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        $output = parent::format($record);
        
        return preg_replace_callback(self::COLOR_PATTERN, array($this, 'applyMethods'), $output);
    }
    
    private function applyMethods(array $result)
    {
        // initialize value parameter
        $valueParameter = null;
        if( ! empty($result['valueParameter']) )
        {
            $valueParameter = $result['valueParameter'];
        }
        
        // case 'c' for color
        if( ! empty($result['closingSlash']) )
        {
            return $this->applyEndingColor();
        }
        
        return $this->applyBeginningColor($valueParameter);
    }
    
    private function applyBeginningColor($valueParameter)
    {
        return self::renderShellColor($this->getColorByName($valueParameter));
    }
    
    private function applyEndingColor()
    {
        return self::renderShellColor(self::NONE_COLOR);
    }
    
    /**
     * Returns the shell color id for a specified color name or the default none color
     * @param string $name
     * @return int
     */
    private function getColorByName($name)
    {
        if( isset($this->colors[$name]) )
        {
            return $this->colors[$name];
        }
        
        return self::NONE_COLOR;
    }
    
    /**
     * Render the shell color code
     * @param integer $id
     * @throws \LogicException
     */
    public static function renderShellColor($id)
    {
        if( ! is_int($id) )
        {
            throw new \LogicException('Unable to render the shell color.');
        }
        
        return sprintf(
            "\033[%dm",
            $id
        );
    }
}
