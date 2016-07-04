<?php
namespace Ackintosh\PHPSnippetCreator;

use Ackintosh\PHPSnippetCreator\SnippetPartInterface;

class SkippedSnippetPart implements SnippetPartInterface
{
    public function toString()
    {
        return '';
    }
}
