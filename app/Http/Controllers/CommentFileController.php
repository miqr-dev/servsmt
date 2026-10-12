<?php

namespace App\Http\Controllers;

use App\CommentFile;
use App\Support\CommentAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Pictures and attachments of ticket comments (2026-10-13).
 *
 *   POST   /comment-files                 upload while writing (pasted / dropped picture or attachment)
 *   GET    /comment-files/{id}/{name?}    open / download
 *   DELETE /comment-files/{id}            remove an upload that is not in a saved comment yet
 *
 * Max 10 MB per file; images, PDF, Office, TXT/CSV, ZIP, e-mails. Access:
 * whoever may open the ticket (CommentAccess); an upload that is not saved
 * in a comment yet only its uploader. Pictures and PDFs open in the browser,
 * everything else is downloaded (never rendered as a web page).
 */
class CommentFileController extends Controller
{
    public const MAX_KB = 10240;

    public const IMAGE_EXTENSIONS = ['png', 'jpg', 'jpeg', 'gif', 'webp'];

    public const EXTENSIONS = [
        'png', 'jpg', 'jpeg', 'gif', 'webp',
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        'txt', 'csv', 'zip', 'msg', 'eml',
    ];

    /** open uploads (not in a saved comment) per user - against filling the disk */
    public const MAX_PENDING = 50;

    public function store(Request $request)
    {
        abort_unless($request->user(), 401);
        $request->validate([
            'file' => ['required', 'file', 'max:'.self::MAX_KB, 'extensions:'.implode(',', self::EXTENSIONS)],
            'commentable_type' => ['required', 'string'],
            'commentable_id' => ['required', 'integer', 'min:1'],
            'inline' => ['nullable', 'boolean'],
        ], [
            'file.required' => 'Keine Datei empfangen.',
            'file.uploaded' => 'Die Datei konnte nicht hochgeladen werden (max. 10 MB).',
            'file.max' => 'Die Datei ist größer als 10 MB.',
            'file.extensions' => 'Dieser Dateityp ist nicht erlaubt. Erlaubt: Bilder, PDF, Word, Excel, PowerPoint, TXT, CSV, ZIP, E-Mails (.msg, .eml).',
        ]);

        $model = CommentAccess::model($request->input('commentable_type'), $request->input('commentable_id'));
        CommentAccess::authorize($model);
        abort_if(method_exists($model, 'trashed') && $model->trashed(), 422, 'Das Ticket ist erledigt.');
        if (CommentFile::whereNull('comment_id')->where('user_id', $request->user()->id)->count() >= self::MAX_PENDING) {
            throw ValidationException::withMessages(['file' => 'Zu viele hochgeladene Dateien, die noch in keinem Kommentar stehen. Bitte erst den Kommentar senden.']);
        }

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        $mime = (string) ($file->getMimeType() ?: 'application/octet-stream'); // from the content
        $isImage = in_array($mime, CommentFile::IMAGE_MIMES, true);
        $inline = $request->boolean('inline');

        if ($inline && ! $isImage) {
            throw ValidationException::withMessages(['file' => 'Nur Bilder (PNG, JPG, GIF, WebP) können in den Text eingefügt werden.']);
        }
        if (in_array($ext, self::IMAGE_EXTENSIONS, true) && ! $isImage) {
            throw ValidationException::withMessages(['file' => 'Die Datei ist kein gültiges Bild.']);
        }

        $path = $file->storeAs('comment-files/'.now()->format('Y/m'), Str::uuid().'.'.$ext, CommentFile::DISK);
        abort_unless($path, 500, 'Die Datei konnte nicht gespeichert werden.');

        $row = CommentFile::create([
            'commentable_type' => get_class($model),
            'commentable_id' => $model->getKey(),
            'user_id' => $request->user()->id,
            'disk_path' => $path,
            'original_name' => self::safeName($file->getClientOriginalName(), $ext),
            'mime' => $mime,
            'size' => (int) $file->getSize(),
            'inline' => $inline,
        ]);

        return response()->json($row, 201);
    }

    public function show(Request $request, CommentFile $commentFile)
    {
        abort_unless($request->user(), 401);
        if ($commentFile->comment_id === null) {
            abort_unless((int) $commentFile->user_id === (int) $request->user()->id, 403);
        }
        CommentAccess::authorize(CommentAccess::model($commentFile->commentable_type, $commentFile->commentable_id));

        $disk = Storage::disk(CommentFile::DISK);
        abort_unless($disk->exists($commentFile->disk_path), 404, 'Die Datei ist nicht mehr vorhanden.');

        $open = $commentFile->is_image || $commentFile->mime === 'application/pdf';
        $headers = [
            'Content-Type' => $open ? $commentFile->mime : 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=604800',
        ];

        return $disk->response($commentFile->disk_path, $commentFile->original_name, $headers, $open ? 'inline' : 'attachment');
    }

    public function destroy(Request $request, CommentFile $commentFile)
    {
        abort_unless($request->user(), 401);
        abort_unless($commentFile->comment_id === null && (int) $commentFile->user_id === (int) $request->user()->id, 403);
        $commentFile->deleteWithFile();

        return response()->noContent();
    }

    /** "Bericht 2026.pdf" - no paths, control characters or overlong names */
    private static function safeName(string $name, string $ext): string
    {
        $name = str_replace(['\\', '/'], '_', $name);
        $name = trim(preg_replace('/[\x00-\x1F\x7F"<>|:*?]+/u', '', $name) ?? '');
        $base = pathinfo($name, PATHINFO_FILENAME);
        $base = Str::limit($base !== '' ? $base : 'Datei', 120, '');

        return $base.'.'.$ext;
    }
}
