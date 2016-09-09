<?php
namespace Qaribou\Templating;

use DOMDocument;
use Traversable;

class Virty
{
    public $doc;

    public function __construct(DOMDocument $doc = null)
    {
        if ($doc) {
            return $this->doc = $doc;
        }
        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = false;
        return $this->doc = $doc;
    }

    public function createNode(array $el)
    {
        list( $name, $attributes ) = $el;
        $childSets = array_slice($el, 2);
        $domNode = $this->doc->createElement($name);

        if ($attributes) {
            foreach ($attributes as $k => $v) {
                $domNode->setAttribute($k, $v);
            }
        }

        foreach ($childSets as $children) {
            if (! is_array($children) && ! $children instanceof Traversable) {
                $domNode->appendChild($this->doc->createTextNode($children));
            } else {
                foreach ($children as $child) {
                    if (is_array($child)) {
                        $domNode->appendChild($this->createNode($child));
                    } elseif (is_string(child)) {
                        $domNode->appendChild($this->doc->createTextNode($child));
                    } else {
                        throw new \RuntimeException(
                            'Invalid child node given: ' . json_encode($child) .
                            ' for child set ' . json_encode($children)
                        );
                    }
                }
            }
        }

        return $domNode;
    }
}
