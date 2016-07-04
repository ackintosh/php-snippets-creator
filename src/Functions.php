<?php
namespace Ackintosh\PHPSnippetCreator;

use Ackintosh\Snidel;
use Ackintosh\Snidel\Result\Collection;

class Functions
{
    public function main($debug = false)
    {
        $dom = new \DOMDocument();
        @$dom->loadHTMLFile('http://php.net/manual/ja/indexes.functions.php');
        $xpath = new \DOMXPath($dom);

        $functionCount = 0;
        $indexCount = 0;
        $snidel = new Snidel(100);

        if ($debug) {
            $fp = fopen('php://stdout', 'w');
            $snidel->setLoggingDestination($fp);
        }

        foreach ($xpath->query('//div[@id="layout"]/section[@id="layout-content"]/div[@id="indexes.functions"]/ul[@class="gen-index index-for-refentry"]/li') as $node) {
            foreach ($xpath->query('.//ul/li/a', $node) as $element) {
                if ($this->shouldSkip($element)) {
                    continue;
                }
                $href = $element->getAttribute('href');
                $snidel->fork(array(__CLASS__, 'makeSnippetPart'), $href);

                if ($functionCount % 50 === 0) {
                    $this->outputSnippet($snidel->get());
                }

                $functionCount++;
            }
            $indexCount++;
            if ($indexCount % 5 === 0) {
                $this->outputSnippet($snidel->get());
            }
        }

        $this->outputSnippet($snidel->get());
    }

    /**
     * @param   \DOMElement
     * @return  bool
     */
    private function shouldSkip(\DOMElement $element)
    {
        if (
            strpos($element->nodeValue, ' ') !== false
            || strpos($element->nodeValue, ':') !== false
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param   Ackintosh\Snidel\Result\Collection
     */
    private function outputSnippet(Collection $collection)
    {
        foreach ($collection as $result) {
            echo $result->getReturn()->toString();
        }
    }

    /**
     * @param   string  $href
     * @return  Ackintosh\PHPSnippetCreator\SnippetPartInterface
     */
    public static function makeSnippetPart($href)
    {
        $baseUrl = 'http://php.net/manual/ja/';
        $dom = new \DOMDocument();
        @$dom->loadHTMLFile($baseUrl . $href);
        $xpath = new \DOMXPath($dom);

        $domNodeList = $xpath->query('//div[@id="layout"]/section[@id="layout-content"]/div[@class="refentry"]/div[@class="refnamediv"]/p[@class="refpurpose"]');
        if (count($domNodeList) !== 1) {
            throw new LogicException('unexpected number of elements');
        }

        $verinfo = $xpath->evaluate('string(..//p[@class="verinfo"])', $domNodeList[0]);
        if (strpos($verinfo, 'PECL') !== false) {
            return new SkippedSnippetPart();
        }

        $name           = $xpath->evaluate('string(.//span[@class="refname"])', $domNodeList[0]);
        $description    = $xpath->evaluate('string(.//span[@class="dc-title"])', $domNodeList[0]);
        $paramList      = $xpath->query('//div[@id="layout"]/section[@id="layout-content"]/div[@class="refentry"]/div[@class="refsect1 description"]/div[@class="methodsynopsis dc-description"]/span[@class="methodparam"]');
        $params = [];
        foreach ($paramList as $li) {
            $params[] = $xpath->evaluate('string(.//code[@class="parameter"])', $li);
        }

        return new SnippetPart($name, $description, $params);
    }
}
