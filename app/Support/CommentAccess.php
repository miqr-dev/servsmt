<?php

namespace App\Support;

use App\Handwerk;
use App\Korso;
use App\Ticket;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Who may see a ticket's comment pictures and attachments (2026-10-13):
 * the same people who may open the ticket (App\Support\TicketAccess with the
 * staff roles of its system).
 */
class CommentAccess
{
    /** commentable class => staff roles of that ticket system */
    public const TYPES = [
        Ticket::class => TicketAccess::IT_STAFF,
        Handwerk::class => TicketAccess::HANDWERK_STAFF,
        Korso::class => TicketAccess::KORSO_STAFF,
    ];

    /** the ticket (also when it is done = soft-deleted); 422 for other classes */
    public static function model(?string $type, $id)
    {
        $type = ltrim((string) $type, '\\');
        abort_unless(isset(self::TYPES[$type]), 422, 'Ungültiger Kommentar-Typ.');
        $query = $type::query();
        if (in_array(SoftDeletes::class, class_uses_recursive($type), true)) {
            $query->withTrashed();
        }

        return $query->findOrFail((int) $id);
    }

    public static function authorize($model): void
    {
        TicketAccess::authorize($model, self::TYPES[get_class($model)] ?? []);
    }
}
