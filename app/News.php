<?php

namespace App;

use App\Support\PublishedFlag;
use Illuminate\Database\Eloquent\Model;

/**
 * Popup on the IT ticket page (Ticketanfrage, Tickets/Index.vue): one row
 * (id 1) with `title`, `body` and `isPublished`. Edited under
 * Einstellungen > Popup (NewsController). The flag may be 'on'/NULL or 1/0,
 * see App\Support\PublishedFlag.
 */
class News extends Model
{
    protected $guarded = [];

    /** The row the app uses: id 1, else the first one. */
    public static function current(): ?self
    {
        return static::find(1) ?? static::query()->orderBy('id')->first();
    }

    public function isPublished(): bool
    {
        return PublishedFlag::isOn($this->isPublished);
    }

    /** Shown on the ticket page: published and not empty. */
    public function isActive(): bool
    {
        return $this->isPublished() && trim((string) $this->title . (string) $this->body) !== '';
    }

    public static function flagValue(bool $on)
    {
        return PublishedFlag::valueFor('news', 'isPublished', $on);
    }

    public static function maxLengths(): array
    {
        return [
            'title' => PublishedFlag::maxLength('news', 'title'),
            'body' => PublishedFlag::maxLength('news', 'body'),
        ];
    }
}
