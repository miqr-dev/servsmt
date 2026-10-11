/**
 * Ticket notes (notizen) for v-html, made safe (2026-10-12).
 *
 * The notes were shown with plain v-html. New notes come from a textarea,
 * so any employee could put HTML / <script> / onerror=… into a ticket and it
 * ran in the browser of whoever opened it (IT, Handwerk, Korso). Old notes
 * are real HTML from the former rich-text editor (paragraphs, lists, images),
 * so they can't simply be shown as text.
 *
 * safeHtml():
 *  - plain text (no tags / entities): escaped, line breaks -> <br>;
 *  - HTML: parsed into an inert document (DOMParser runs no scripts and
 *    loads no images) and rebuilt from an allowlist: formatting tags, lists,
 *    tables, links (http/https/mailto/relative), images (http/https/relative,
 *    data:image); every other attribute (on*, style, …) is dropped, unknown
 *    tags are replaced by their content, script-like tags removed with content.
 * Same rules on the server: App\Support\SafeHtml (Blade views, PDFs).
 */

const ALLOWED = new Set([
    'P', 'BR', 'B', 'STRONG', 'I', 'EM', 'U', 'S', 'STRIKE', 'SUB', 'SUP', 'SMALL', 'MARK',
    'UL', 'OL', 'LI', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'BLOCKQUOTE', 'PRE', 'CODE', 'HR',
    'SPAN', 'DIV', 'A', 'IMG', 'TABLE', 'THEAD', 'TBODY', 'TFOOT', 'TR', 'TD', 'TH', 'CAPTION',
]);
/** removed together with their content */
const DROP = new Set([
    'SCRIPT', 'STYLE', 'IFRAME', 'FRAME', 'FRAMESET', 'OBJECT', 'EMBED', 'APPLET', 'SVG', 'MATH', 'TEMPLATE',
    'NOSCRIPT', 'FORM', 'INPUT', 'BUTTON', 'TEXTAREA', 'SELECT', 'OPTION', 'LINK', 'META', 'BASE', 'TITLE',
    'AUDIO', 'VIDEO', 'SOURCE', 'TRACK', 'CANVAS', 'DIALOG', 'PORTAL',
]);
const ATTRS: Record<string, string[]> = {
    A: ['href', 'title'],
    IMG: ['src', 'alt', 'title', 'width', 'height'],
    TD: ['colspan', 'rowspan'],
    TH: ['colspan', 'rowspan'],
    OL: ['start'],
};

const HTML_LIKE = /<\/?[a-z][^>]*>|&(#\d+|#x[0-9a-f]+|[a-z]+);/i;

function escapeText(text: string): string {
    return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

function safeUrl(value: string, image: boolean): boolean {
    const v = value.trim().replace(/[\u0000-\u001F\u007F\s]+/g, '');
    if (v === '') return false;
    if (image && /^data:image\/(png|jpe?g|gif|webp);base64,/i.test(v)) return true;
    const scheme = /^([a-z][a-z0-9+.-]*):/i.exec(v);
    if (!scheme) return true; // relative (/upload/…, …)
    const s = scheme[1].toLowerCase();

    return s === 'http' || s === 'https' || (!image && s === 'mailto');
}

function clean(node: Node): void {
    for (const child of Array.from(node.childNodes)) {
        if (child.nodeType === Node.TEXT_NODE) continue;
        if (child.nodeType !== Node.ELEMENT_NODE) {
            child.remove(); // comments, processing instructions
            continue;
        }
        const el = child as Element;
        const tag = el.tagName.toUpperCase();
        if (DROP.has(tag)) {
            el.remove();
            continue;
        }
        clean(el);
        if (!ALLOWED.has(tag)) {
            el.replaceWith(...Array.from(el.childNodes));
            continue;
        }
        const keep = ATTRS[tag] ?? [];
        for (const attr of Array.from(el.attributes)) {
            const name = attr.name.toLowerCase();
            const ok = keep.includes(name) && (name !== 'href' && name !== 'src' ? true : safeUrl(attr.value, name === 'src'));
            if (!ok) el.removeAttribute(attr.name);
        }
        if (tag === 'A' && el.hasAttribute('href')) {
            el.setAttribute('target', '_blank');
            el.setAttribute('rel', 'noopener noreferrer');
        }
        if (tag === 'IMG' && !el.hasAttribute('src')) el.remove();
    }
}

export function safeHtml(value: string | null | undefined): string {
    const input = value ?? '';
    if (input === '') return '';
    if (!HTML_LIKE.test(input) || typeof DOMParser === 'undefined') {
        return escapeText(input).replace(/\r\n|\r|\n/g, '<br>');
    }
    const doc = new DOMParser().parseFromString(`<body>${input}</body>`, 'text/html');
    clean(doc.body);

    return doc.body.innerHTML;
}
