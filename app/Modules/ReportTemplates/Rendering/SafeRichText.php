<?php

namespace App\Modules\ReportTemplates\Rendering;

/** Formatting only: no attributes, links, images, scripts or styles. */
class SafeRichText
{
    public static function render(string $html): string
    {
        if ($html === '') { return ''; }
        $document = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"><div>'.$html.'</div>', LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors(); libxml_use_internal_errors($previous);
        $body = $document->getElementsByTagName('body')->item(0);

        return $body ? self::children($body) : '';
    }

    private static function children(\DOMNode $node): string
    {
        $result = '';
        foreach ($node->childNodes as $child) {
            if ($child instanceof \DOMText) { $result .= e($child->textContent); continue; }
            if (! $child instanceof \DOMElement) { continue; }
            $tag = strtolower($child->tagName);
            if (in_array($tag, ['script', 'style', 'iframe', 'object', 'svg', 'math'])) { continue; }
            $content = self::children($child);
            $result .= in_array($tag, ['p', 'b', 'strong', 'i', 'em', 'u', 'ul', 'ol', 'li', 'br'])
                ? ($tag === 'br' ? '<br>' : "<{$tag}>{$content}</{$tag}>") : $content;
        }

        return $result;
    }
}
