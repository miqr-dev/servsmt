<?php

namespace App\Support;

/**
 * Ticket notes (notizen) for {!! !!} in Blade views and PDFs, made safe
 * (2026-10-12). Same rules as resources/js/lib/safeHtml.ts:
 *  - plain text: escaped, line breaks -> <br>;
 *  - HTML (old rich-text notes): rebuilt from an allowlist of tags and
 *    attributes; scripts, event handlers, styles, javascript: links and
 *    unknown tags are removed (unknown tags keep their text).
 */
class SafeHtml
{
    private const ALLOWED = [
        'P', 'BR', 'B', 'STRONG', 'I', 'EM', 'U', 'S', 'STRIKE', 'SUB', 'SUP', 'SMALL', 'MARK',
        'UL', 'OL', 'LI', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'BLOCKQUOTE', 'PRE', 'CODE', 'HR',
        'SPAN', 'DIV', 'A', 'IMG', 'TABLE', 'THEAD', 'TBODY', 'TFOOT', 'TR', 'TD', 'TH', 'CAPTION',
    ];

    private const DROP = [
        'SCRIPT', 'STYLE', 'IFRAME', 'FRAME', 'FRAMESET', 'OBJECT', 'EMBED', 'APPLET', 'SVG', 'MATH', 'TEMPLATE',
        'NOSCRIPT', 'FORM', 'INPUT', 'BUTTON', 'TEXTAREA', 'SELECT', 'OPTION', 'LINK', 'META', 'BASE', 'TITLE',
        'AUDIO', 'VIDEO', 'SOURCE', 'TRACK', 'CANVAS', 'DIALOG', 'PORTAL',
    ];

    private const ATTRS = [
        'A' => ['href', 'title'],
        'IMG' => ['src', 'alt', 'title', 'width', 'height'],
        'TD' => ['colspan', 'rowspan'],
        'TH' => ['colspan', 'rowspan'],
        'OL' => ['start'],
    ];

    public static function clean(?string $value): string
    {
        $input = (string) $value;
        if ($input === '') {
            return '';
        }
        if (! preg_match('/<\/?[a-z][^>]*>|&(#\d+|#x[0-9a-f]+|[a-z]+);/i', $input)) {
            return nl2br(htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), false);
        }

        $doc = new \DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><div id="safe-root">'.$input.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = null;
        foreach ($doc->childNodes as $child) {
            if ($child instanceof \DOMElement) {
                $root = $child;
                break;
            }
        }
        if (! $root) {
            return '';
        }
        self::walk($root);

        $html = '';
        foreach ($root->childNodes as $child) {
            $html .= $doc->saveHTML($child);
        }

        return $html;
    }

    private static function walk(\DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof \DOMText) {
                continue;
            }
            if (! $child instanceof \DOMElement) {
                $node->removeChild($child);

                continue;
            }
            $tag = strtoupper($child->tagName);
            if (in_array($tag, self::DROP, true)) {
                $node->removeChild($child);

                continue;
            }
            self::walk($child);
            if (! in_array($tag, self::ALLOWED, true)) {
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);

                continue;
            }
            $keep = self::ATTRS[$tag] ?? [];
            foreach (iterator_to_array($child->attributes) as $attr) {
                $name = strtolower($attr->name);
                $ok = in_array($name, $keep, true)
                    && (! in_array($name, ['href', 'src'], true) || self::safeUrl($attr->value, $name === 'src'));
                if (! $ok) {
                    $child->removeAttribute($attr->name);
                }
            }
            if ($tag === 'A' && $child->hasAttribute('href')) {
                $child->setAttribute('target', '_blank');
                $child->setAttribute('rel', 'noopener noreferrer');
            }
            if ($tag === 'IMG' && ! $child->hasAttribute('src')) {
                $node->removeChild($child);
            }
        }
    }

    private static function safeUrl(string $value, bool $image): bool
    {
        $v = preg_replace('/[\x00-\x20\x7F]+/', '', $value);
        if ($v === '') {
            return false;
        }
        if ($image && preg_match('/^data:image\/(png|jpe?g|gif|webp);base64,/i', $v)) {
            return true;
        }
        if (! preg_match('/^([a-z][a-z0-9+.-]*):/i', $v, $m)) {
            return true; // relative
        }
        $scheme = strtolower($m[1]);

        return $scheme === 'http' || $scheme === 'https' || (! $image && $scheme === 'mailto');
    }
}
