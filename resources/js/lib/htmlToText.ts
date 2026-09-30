/**
 * Old servsmt comments were saved from a rich-text editor, so the DB holds
 * HTML like "<p>mach ich</p>" or "a&nbsp;b". The new UI shows comments as
 * plain text, which printed those tags literally.
 *
 * This turns such HTML into readable plain text: paragraphs / <br> / list
 * items become line breaks, entities are decoded, tags dropped. DOMParser
 * builds an inert document (no scripts run, no images load), and the result
 * is rendered as text again - so there is no XSS risk, unlike v-html.
 * Plain-text comments (everything written in the new UI) pass through as is.
 */
const BLOCK = new Set(['P', 'DIV', 'LI', 'TR', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'BLOCKQUOTE', 'PRE', 'UL', 'OL', 'TABLE']);

export function htmlToText(value: string | null | undefined): string {
    const input = value ?? '';
    if (!/[<&]/.test(input) || typeof DOMParser === 'undefined') return input;

    const doc = new DOMParser().parseFromString(input, 'text/html');
    let out = '';

    const walk = (node: Node) => {
        if (node.nodeType === Node.TEXT_NODE) {
            out += (node.textContent ?? '').replace(/ /g, ' ');

            return;
        }
        if (node.nodeType !== Node.ELEMENT_NODE) return;
        const el = node as Element;
        if (el.tagName === 'BR') {
            out += '\n';

            return;
        }
        if (el.tagName === 'SCRIPT' || el.tagName === 'STYLE') return;
        const block = BLOCK.has(el.tagName);
        if (block && out && !out.endsWith('\n')) out += '\n';
        if (el.tagName === 'LI') out += '• ';
        el.childNodes.forEach(walk);
        if (block && !out.endsWith('\n')) out += '\n';
    };
    doc.body.childNodes.forEach(walk);

    return out
        .replace(/[ \t]+\n/g, '\n')
        .replace(/\n{3,}/g, '\n\n')
        .trim();
}
