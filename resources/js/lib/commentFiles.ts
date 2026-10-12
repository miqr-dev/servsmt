/** Picture / attachment of a ticket comment (App\CommentFile, 2026-10-13). */
export interface CommentFileItem {
    id: number;
    comment_id: number | null;
    original_name: string;
    mime: string;
    size: number;
    /** picture inside the text (not listed as attachment) */
    inline: boolean;
    is_image: boolean;
    /** /comment-files/{id}/{name} - opens pictures / PDFs, downloads the rest */
    url: string;
    created_at?: string;
}

export function formatFileSize(bytes: number): string {
    if (!bytes) return '0 KB';
    if (bytes < 1024 * 1024) return `${Math.max(1, Math.round(bytes / 1024))} KB`;

    return `${(bytes / 1024 / 1024).toFixed(1).replace('.', ',')} MB`;
}

export function fileExtension(name: string): string {
    const i = name.lastIndexOf('.');

    return i === -1 ? '' : name.slice(i + 1).toLowerCase();
}

/** something to send: text, a picture or an attachment */
export function hasContent(html: string, files: CommentFileItem[]): boolean {
    if (files.length || /<img\b/i.test(html)) return true;

    return html.replace(/<[^>]*>/g, '').replace(/&nbsp;|\u00a0/g, ' ').trim() !== '';
}
