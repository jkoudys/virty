<?php
namespace Qaribou\Templating;

use DOMDocument;
use DOMNode;

// TODO: allow switching between docs, one load could render multiple docs.
class Virty
{
    protected static $doc;

    public static function init(DOMDocument $targetDoc = null)
    {
        if ($targetDoc) {
            self::$doc = $targetDoc;
        } elseif (!self::$doc) {
            $doc = new DOMDocument();
            $doc->preserveWhiteSpace = false;
            $doc->formatOutput = false;

            self::$doc = $doc;
        }

        return self::$doc;
    }

    public static function getDoc()
    {
        return self::$doc;
    }

    public static function render(DOMNode $root): string
    {
        self::$doc->appendChild($root);
        return self::$doc->saveHTML();
    }
}

function createElement($el, array $props = null, ...$children): DOMNode
{
    $doc = Virty::init();

    $domNode = $doc->createElement($el);

    if ($props) {
        foreach ($props as $k => $v) {
            $domNode->setAttribute($k, $v);
        }
    }

    foreach ($children as $child) {
        if ($child instanceof DOMNode) {
            $domNode->appendChild($child);
        } elseif (is_string($child)) {
            $domNode->appendChild($doc->createTextNode($child));
        } else {
            throw new \InvalidArgumentException(
                'Invalid child node given: ' . json_encode($child)
            );
        }
    }

    return $domNode;
}
