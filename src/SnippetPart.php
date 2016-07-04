<?php
namespace Ackintosh\PHPSnippetCreator;

use Ackintosh\PHPSnippetCreator\SnippetPartInterface;

class SnippetPart implements SnippetPartInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * DOMList
     */
    private $paramList;

    public function __construct($name, $description, $paramList)
    {
        $this->name = $name;
        $this->description = $description;
        $this->paramList = $paramList;
    }

    /**
     * @return  string
     */
    public function toString()
    {
        $name           = trim($this->name);
        $description    = trim($this->description);
        $params = [];
        foreach ($this->paramList as $idx => $param) {
            $params[] = '${' . ($idx + 1) . ':' . $param . '}';
        }
        $paramsString = count($params) > 0 ? implode(', ', $params) : '';

        return <<<__EOS__
snippet {$name}
abbr {$description}
 {$this->name}({$paramsString})


__EOS__;
    }
}
