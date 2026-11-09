<?php

namespace App\Http\Controllers;

use App\NewsBar;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Einstellungen > Nachrichtenleiste (2026-11-03: Blade form
 * settings/publish/newsbar.blade.php -> Settings/NewsBar.vue).
 * Same URLs as before: GET /settings/publish/newsbar/1 (route_access
 * 'settings/*') and PUT /newsbar_update/1 ('newsbar_update/1'), both Super_Admin.
 */
class NewsBarController extends Controller
{
    /**
     * Old JSON endpoint of the old IT ticket page's scrolling bar. No converted
     * page calls it any more; kept for the remaining Blade view.
     */
    public function news_bar_check()
    {
        $check = NewsBar::find(1)->get()->toArray();
        return $check;
    }

    public function show()
    {
        $bar = NewsBar::current();

        return Inertia::render('Settings/NewsBar', [
            'message' => (string) ($bar->name ?? ''),
            'published' => $bar ? $bar->isPublished() : false,
            'active' => $bar ? $bar->isActive() : false,
            'updatedAt' => $bar && $bar->updated_at ? $bar->updated_at->toIso8601String() : null,
            'maxLength' => NewsBar::messageMaxLength(),
        ]);
    }

    public function update(Request $request)
    {
        $max = NewsBar::messageMaxLength();
        $data = $request->validate([
            'published' => ['required', 'boolean'],
            // A message is only required while the bar is published.
            'message' => array_values(array_filter(['nullable', 'required_if:published,true,1', 'string', $max ? "max:{$max}" : null])),
        ], [
            'message.required_if' => 'Bitte eine Nachricht eingeben, bevor die Leiste veröffentlicht wird.',
        ], ['message' => 'Nachricht']);

        $bar = NewsBar::current() ?? new NewsBar(['id' => 1]);
        $published = $request->boolean('published');
        $bar->name = trim((string) ($data['message'] ?? ''));
        $bar->isNewsBar = NewsBar::flagValue($published);
        $bar->save();

        return back()->with('success', $published ? 'Nachrichtenleiste gespeichert und veröffentlicht.' : 'Nachrichtenleiste gespeichert (nicht veröffentlicht).');
    }
}
