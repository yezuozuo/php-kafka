<?php

namespace Monolog\Formatter;

/**
 * Formats incoming records into a one-line non-colored string
 */
class NoColorLineFormatter extends LineFormatter
{
    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        return preg_replace(
            ColorLineFormatter::COLOR_PATTERN,
            '',
            parent::format($record)
        );
    }
}
