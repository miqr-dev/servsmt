<?php

namespace App\Support;

/**
 * Comment text from the editor (HTML, 2026-10-13), made safe before saving.
 *
 * - App\Support\SafeHtml::clean(): allowlist of tags and attributes (no
 *   scripts, event handlers, styles, javascript: links).
 * - Pictures: only our own uploads, <img src="/comment-files/{id}">, and
 *   only the ids the caller allows (the writer's own uploads for this ticket,
 *   or files already in the comment being edited). Any other <img> (pasted
 *   from a web page, data: URLs, other tickets' files) is removed.
 */
class CommentHtml
{
    private const SRC = '#^/comment-files/(\d+)$#';

    /** ids of /comment-files/{id} pictures in the (raw or clean) HTML */
    public static function imageIds(?string $html): array
    {
        preg_match_all('#<img\b[^>]*\bsrc\s*=\s*["\']?/comment-files/(\d+)["\'\s/>]#i', (string) $html, $m);

        return array_values(array_unique(array_map('intval', $m[1] ?? [])));
    }

    /** @param int[] $allowedIds */
    public static function clean(?string $html, array $allowedIds): string
    {
        $safe = SafeHtml::clean($html);
        if ($safe === '' || stripos($safe, '<img') === false) {
            return self::trimEmpty($safe);
        }

        $doc = new \DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><div>'.$safe.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
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
        foreach (iterator_to_array($root->getElementsByTagName('img')) as $img) {
            $src = trim($img->getAttribute('src'));
            if (! preg_match(self::SRC, $src, $m) || ! in_array((int) $m[1], $allowedIds, true)) {
                $img->parentNode?->removeChild($img);

                continue;
            }
            foreach (iterator_to_array($img->attributes) as $attr) {
                if (! in_array(strtolower($attr->name), ['src', 'alt'], true)) {
                    $img->removeAttribute($attr->name);
                }
            }
        }

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        return self::trimEmpty($out);
    }

    /** nothing to read and no picture */
    public static function isEmpty(?string $html): bool
    {
        $html = (string) $html;
        if (stripos($html, '<img') !== false) {
            return false;
        }
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(str_replace("\u{00A0}", ' ', $text)) === '';
    }

    /** drops empty paragraphs at the start and end ("<p></p>" from the editor) */
    private static function trimEmpty(string $html): string
    {
        $empty = '(?:\s|\x{00A0}|<p>(?:\s|\x{00A0}|&nbsp;|<br\s*/?>)*</p>|<br\s*/?>)*';

        return trim(preg_replace(['#^'.$empty.'#iu', '#'.$empty.'$#iu'], '', $html) ?? $html);
    }
}
