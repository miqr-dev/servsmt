<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Stage 2 of the inv_items / inv_ab_items merge.
 *
 * Copies every column from inv_ab_items into inv_items, matched by id
 * (the two tables have always shared the same id for the same physical
 * item - see App\InvItems::invabitem()). Two cases:
 *
 *  - inv_ab_items.id has a matching inv_items.id: fill in the new
 *    columns on that existing row.
 *  - inv_ab_items.id has NO matching inv_items row (this is the normal
 *    case for every decommissioned/"ausgemustert" item - InvAbItemController
 *    ::invalid() deletes the inv_items row on decommission today, so the
 *    item only survives in inv_ab_items): insert a new inv_items row with
 *    the SAME id, carrying over the shared columns too (invnr/gname/sn/
 *    gart_id/gtyp), with room_id left null (it had no room at the time
 *    it was decommissioned/deleted).
 *
 * inv_ab_items itself is never modified or touched destructively - it is
 * left fully intact so nothing is at risk while the app is verified on
 * the merged table. Dropping it is a separate, later migration that only
 * runs once you're satisfied everything works.
 */
class BackfillInvAbItemsIntoInvItemsTable extends Migration
{
    public function up()
    {
        $updated = 0;
        $inserted = 0;

        DB::table('inv_ab_items')->orderBy('id')->chunk(200, function ($abItems) use (&$updated, &$inserted) {
            foreach ($abItems as $ab) {
                $extra = [
                    'andat' => $ab->andat,
                    'location_id' => $ab->location_id,
                    'kp' => $ab->kp,
                    'notes' => $ab->notes,
                    'path_to_rg' => $ab->path_to_rg,
                    'ausdat' => $ab->ausdat,
                    'amg_id' => $ab->amg_id,
                ];

                $existing = DB::table('inv_items')->where('id', $ab->id)->first();

                if ($existing) {
                    DB::table('inv_items')->where('id', $ab->id)->update($extra);
                    $updated++;
                } else {
                    DB::table('inv_items')->insert(array_merge($extra, [
                        'id' => $ab->id,
                        'dateupd' => $ab->andat,
                        'invnr' => $ab->invnr,
                        'room_id' => null,
                        'gname' => $ab->gname,
                        'sn' => $ab->sn,
                        'gart_id' => $ab->gart_id,
                        'gtyp' => $ab->gtyp,
                        'created_at' => $ab->created_at,
                        'updated_at' => $ab->updated_at,
                    ]));
                    $inserted++;
                }
            }
        });

        $abCount = DB::table('inv_ab_items')->count();
        $itemsCount = DB::table('inv_items')->count();

        echo "\n";
        echo "  inv_ab_items -> inv_items backfill complete.\n";
        echo "  inv_ab_items rows processed: matched/updated {$updated}, no match/inserted {$inserted}.\n";
        echo "  inv_ab_items total rows: {$abCount}. inv_items total rows now: {$itemsCount}.\n";
        echo "  inv_ab_items was NOT modified - please spot check the counts above before moving on.\n";
    }

    /**
     * Only clears the columns this migration filled in - never deletes a
     * row, and inv_ab_items (the original data) was never touched by up()
     * in the first place. Note this does NOT remove the rows up() inserted
     * for decommissioned/orphaned items (deleting rows is riskier than
     * leaving a few extra null-columned rows behind); if you need a full
     * rollback, restore from a backup instead.
     */
    public function down()
    {
        DB::table('inv_items')->update([
            'andat' => null,
            'location_id' => null,
            'kp' => null,
            'notes' => null,
            'path_to_rg' => null,
            'ausdat' => null,
            'amg_id' => null,
        ]);
    }
}
