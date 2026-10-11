<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

/**
 * User text in notification e-mails (2026-10-11).
 *
 * The mail templates print every line unescaped ({!! $line !!}) and Laravel
 * then runs the whole message through Markdown. User text (comments, ticket
 * notes) therefore rendered as HTML, lost its line breaks, and characters
 * like "#", "*" or four leading spaces changed the layout.
 *
 * block() turns the text into ONE line of escaped HTML inside a <div>:
 * - old rich-text content ("<p>mach ich</p>", "a&nbsp;b") becomes plain
 *   text first (same rules as resources/js/lib/htmlToText.ts);
 * - everything is escaped, line breaks become <br>;
 * - a line that starts with <div> is an HTML block for Markdown, so nothing
 *   inside it is read as Markdown.
 */
class MailText
{
    private const BLOCK = ['P', 'DIV', 'LI', 'TR', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'BLOCKQUOTE', 'PRE', 'UL', 'OL', 'TABLE'];

    private const QUOTE_STYLE = 'margin:0 0 16px;padding:10px 14px;border-left:3px solid #c9ced6;background:#f4f5f7;color:#2d3748;font-size:15px;line-height:1.5;';

    private const PLAIN_STYLE = 'margin:0 0 16px;color:#2d3748;font-size:15px;line-height:1.5;';

    /** Escaped text for use inside our own HTML. */
    public static function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /** User text as a safe, line-break-preserving HTML block ('' when empty). */
    public static function block(?string $value, bool $quote = false): HtmlString
    {
        $text = self::toText($value);
        if ($text === '') {
            return new HtmlString('');
        }

        $html = str_replace(["\r\n", "\r", "\n"], '<br>', self::e($text));

        return new HtmlString('<div style="'.($quote ? self::QUOTE_STYLE : self::PLAIN_STYLE).'">'.$html.'</div>');
    }

    /** A paragraph of our own wording; pass user parts through e() first. */
    public static function p(string $html): HtmlString
    {
        return new HtmlString('<p>'.$html.'</p>');
    }

    /**
     * Stored HTML (old rich-text editor) to plain text; plain text unchanged.
     * Images become "[Bild im Ticket]" - their relative /upload paths don't
     * work in a mail anyway.
     */
    public static function toText(?string $value): string
    {
        $input = (string) $value;
        if (! preg_match('/<\/?[a-z][^>]*>|&(#\d+|#x[0-9a-f]+|[a-z]+);/i', $input)) {
            return self::tidy($input);
        }

        $doc = new \DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><div>'.$input.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $out = '';
        $walk = function (\DOMNode $node) use (&$walk, &$out): void {
            if ($node instanceof \DOMText) {
                $out .= str_replace("\u{00A0}", ' ', $node->nodeValue);

                return;
            }
            if (! $node instanceof \DOMElement) {
                return;
            }
            $tag = strtoupper($node->tagName);
            if ($tag === 'BR') {
                $out .= "\n";

                return;
            }
            if (in_array($tag, ['SCRIPT', 'STYLE', 'HEAD', 'TITLE'], true)) {
                return;
            }
            if ($tag === 'IMG') {
                $out .= '[Bild im Ticket]';

                return;
            }
            $block = in_array($tag, self::BLOCK, true);
            if ($block && $out !== '' && ! str_ends_with($out, "\n")) {
                $out .= "\n";
            }
            if ($tag === 'LI') {
                $out .= '• ';
            }
            foreach ($node->childNodes as $child) {
                $walk($child);
            }
            if ($block && ! str_ends_with($out, "\n")) {
                $out .= "\n";
            }
        };
        foreach ($doc->childNodes as $child) {
            $walk($child);
        }

        return self::tidy($out);
    }

    /**
     * Our mail HTML as readable plain text, for the text part of the mails
     * (resources/views/vendor/mail/text/message.blade.php): line breaks for
     * paragraphs / rows / <br>, "Label: Wert" for the fact tables, tags removed.
     * Entities stay encoded - Laravel decodes the whole text part afterwards.
     */
    public static function plain(string $html): string
    {
        $s = preg_replace('/<br\s*\/?>(\r?\n)?/i', "\n", $html);
        $s = preg_replace('/<\/td>\s*<td[^>]*>/i', ': ', $s);
        $s = preg_replace('/<\/(p|div|tr|h[1-6]|li|table|blockquote)>/i', "\n", $s);
        $s = strip_tags($s);
        $s = implode("\n", array_map('trim', explode("\n", str_replace(["\r\n", "\r"], "\n", $s))));

        return trim(preg_replace("/\n{3,}/", "\n\n", $s));
    }

    private static function tidy(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[ \t]+\n/', "\n", $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }

    /** "IT", "Handwerk", "Korso" for the model class basename ('Ticket' = IT). */
    public static function system(?string $type): string
    {
        return match ($type) {
            'Handwerk', 'handwerk' => 'Handwerk',
            'Korso', 'korso' => 'Korso',
            default => 'IT',
        };
    }

    /** "IT-Ticket", "Handwerk-Ticket", "Korso-Ticket". */
    public static function ticketLabel(?string $type): string
    {
        return self::system($type).'-Ticket';
    }
}
