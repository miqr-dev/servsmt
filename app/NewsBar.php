<?php

namespace App;

use App\Support\PublishedFlag;
use Illuminate\Database\Eloquent\Model;

/**
 * Nachrichtenleiste: one row (id 1). `name` = the message, `isNewsBar` =
 * published. Shown on the Dashboard as "Aktuelle Information"; edited under
 * Einstellungen > Nachrichtenleiste (NewsBarController). The flag may be
 * 'on'/NULL or 1/0, see App\Support\PublishedFlag.
 */
class NewsBar extends Model
{
    protected $guarded = [];

    /** The row the app uses: id 1, else the first one. */
    public static function current(): ?self
    {
        return static::find(1) ?? static::query()->orderBy('id')->first();
    }

    public function isPublished(): bool
    {
        return PublishedFlag::isOn($this->isNewsBar);
    }

    /** Shown on the Dashboard: published and not empty. */
    public function isActive(): bool
    {
        return $this->isPublished() && trim((string) $this->name) !== '';
    }

    public static function flagValue(bool $on)
    {
        return PublishedFlag::valueFor('news_bars', 'isNewsBar', $on);
    }

    /** Max length of the message, null = no limit (text column). */
    public static function messageMaxLength(): ?int
    {
        return PublishedFlag::maxLength('news_bars', 'name');
    }
}
