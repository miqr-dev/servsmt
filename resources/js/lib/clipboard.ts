/**
 * Copy text to the clipboard. navigator.clipboard only exists on HTTPS /
 * localhost - the intranet servers run on plain http - so fall back to the
 * classic hidden textarea + execCommand('copy'), which works there.
 * Returns false when the browser refused.
 */
export async function copyText(text: string): Promise<boolean> {
    if (navigator.clipboard && window.isSecureContext) {
        try {
            await navigator.clipboard.writeText(text);

            return true;
        } catch {
            // fall through to the fallback
        }
    }
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.setAttribute('readonly', '');
    ta.style.position = 'fixed';
    ta.style.top = '-1000px';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.select();
    let ok = false;
    try {
        ok = document.execCommand('copy');
    } catch {
        ok = false;
    }
    document.body.removeChild(ta);

    return ok;
}
