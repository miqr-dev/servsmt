<?php

namespace App\Http\Controllers;

use App\News;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Einstellungen > Popup (2026-11-03: Blade form
 * settings/publish/popup.blade.php -> Settings/NewsPopup.vue). The popup
 * opens on the IT ticket page (Ticketanfrage, Tickets/Index.vue).
 * Same URLs as before: GET /settings/publish/popup/1 (route_access
 * 'settings/*') and PUT /popup_update/1 ('popup_update/1'), both Super_Admin.
 */
class NewsController extends Controller
{
    /**
     * JSON for Tickets/Index.vue (GET /news.popup.checks, any logged-in user).
     * Same shape as before - a list whose first entry is the popup, with
     * isPublished 'on' when it should open - but now only the row the
     * settings page edits. The old `News::find(1)->get()` returned every row.
     */
    public function news_check()
    {
        $news = News::current();
        if (! $news) {
            return [];
        }

        return [[
            'id' => $news->id,
            'title' => $news->title,
            'body' => $news->body,
            'isPublished' => $news->isActive() ? 'on' : null,
        ]];
    }

    public function show()
    {
        $news = News::current();

        return Inertia::render('Settings/NewsPopup', [
            'title' => (string) ($news->title ?? ''),
            'body' => (string) ($news->body ?? ''),
            'published' => $news ? $news->isPublished() : false,
            'active' => $news ? $news->isActive() : false,
            'updatedAt' => $news && $news->updated_at ? $news->updated_at->toIso8601String() : null,
            'maxLength' => News::maxLengths(),
        ]);
    }

    public function update(Request $request)
    {
        $max = News::maxLengths();
        $rules = fn (?int $len) => array_values(array_filter(['nullable', 'required_if:published,true,1', 'string', $len ? "max:{$len}" : null]));

        $data = $request->validate([
            'published' => ['required', 'boolean'],
            // Title and text are only required while the popup is published.
            'title' => $rules($max['title']),
            'body' => $rules($max['body']),
        ], [
            'title.required_if' => 'Bitte einen Titel eingeben, bevor das Popup veröffentlicht wird.',
            'body.required_if' => 'Bitte einen Text eingeben, bevor das Popup veröffentlicht wird.',
        ], ['title' => 'Titel', 'body' => 'Text']);

        $news = News::current() ?? new News(['id' => 1]);
        $published = $request->boolean('published');
        $news->title = trim((string) ($data['title'] ?? ''));
        $news->body = trim((string) ($data['body'] ?? ''));
        $news->isPublished = News::flagValue($published);
        $news->save();

        return back()->with('success', $published ? 'Popup gespeichert und veröffentlicht.' : 'Popup gespeichert (nicht veröffentlicht).');
    }
}
