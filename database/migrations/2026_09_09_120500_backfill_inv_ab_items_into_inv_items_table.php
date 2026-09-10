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
 *
 * Legacy data safety: inv_ab_items has a handful of rows carrying
 * location_id/amg_id/gart_id values that were never valid foreign keys
 * (almost certainly written by a raw import with FOREIGN_KEY_CHECKS off
 * at some point - inv_ab_items has had a real FK on these columns since
 * 2020, so they couldn't have gotten in there through the app). Since
 * inv_items now enforces the same FKs, copying those values verbatim
 * would crash the migration (this happened once already, on location_id
 * = 0 for id 446, which has no matching row in locations). Rather than
 * fail or silently drop the row, any such value is set to NULL on the
 * inv_items side (inv_ab_items itself is never touched) and reported by
 * name below so nothing is lost silently - a location_id/amg_id/gart_id
 * that was already meaningless is not real data to lose.
 */
class BackfillInvAbItemsIntoInvItemsTable extends Migration
{
    public function up()
    {
        $updated = 0;
        $inserted = 0;
        $badLocations = [];
        $badAmgs = [];
        $badGarts = [];

        $validLocationIds = DB::table('locations')->pluck('id')->all();
        $validAmgIds = DB::table('amgs')->pluck('id')->all();
        $validGartIds = DB::table('garts')->pluck('id')->all();

        DB::transaction(function () use (
            &$updated, &$inserted, &$badLocations, &$badAmgs, &$badGarts,
            $validLocationIds, $validAmgIds, $validGartIds
        ) {
            DB::table('inv_ab_items')->orderBy('id')->chunk(200, function ($abItems) use (
                &$updated, &$inserted, &$badLocations, &$badAmgs, &$badGarts,
                $validLocationIds, $validAmgIds, $validGartIds
            ) {
                foreach ($abItems as $ab) {
                    $locationId = $ab->location_id;
                    if ($locationId !== null && !in_array($locationId, $validLocationIds)) {
                        $badLocations[] = "inv_ab_items.id={$ab->id} (location_id was {$locationId})";
                        $locationId = null;
                    }

                    $amgId = $ab->amg_id;
                    if ($amgId !== null && !in_array($amgId, $validAmgIds)) {
                        $badAmgs[] = "inv_ab_items.id={$ab->id} (amg_id was {$amgId})";
                        $amgId = null;
                    }

                    $extra = [
                        'andat' => $ab->andat,
                        'location_id' => $locationId,
                        'kp' => $ab->kp,
                        'notes' => $ab->notes,
                        'path_to_rg' => $ab->path_to_rg,
                        'ausdat' => $ab->ausdat,
                        'amg_id' => $amgId,
                    ];

                    $existing = DB::table('inv_items')->where('id', $ab->id)->first();

                    if ($existing) {
                        DB::table('inv_items')->where('id', $ab->id)->update($extra);
                        $updated++;
                    } else {
                        $gartId = $ab->gart_id;
                        if ($gartId !== null && !in_array($gartId, $validGartIds)) {
                            $badGarts[] = "inv_ab_items.id={$ab->id} (gart_id was {$gartId})";
                            $gartId = null;
                        }

                        DB::table('inv_items')->insert(array_merge($extra, [
                            'id' => $ab->id,
                            'dateupd' => $ab->andat,
                            'invnr' => $ab->invnr,
                            'room_id' => null,
                            'gname' => $ab->gname,
                            'sn' => $ab->sn,
                            'gart_id' => $gartId,
                            'gtyp' => $ab->gtyp,
                            'created_at' => $ab->created_at,
                            'updated_at' => $ab->updated_at,
                        ]));
                        $inserted++;
                    }
                }
            });
        });

        $abCount = DB::table('inv_ab_items')->count();
        $itemsCount = DB::table('inv_items')->count();

        echo "\n";
        echo "  inv_ab_items -> inv_items backfill complete.\n";
        echo "  inv_ab_items rows processed: matched/updated {$updated}, no match/inserted {$inserted}.\n";
        echo "  inv_ab_items total rows: {$abCount}. inv_items total rows now: {$itemsCount}.\n";
        echo "  inv_ab_items was NOT modified - please spot check the counts above before moving on.\n";

        if (!empty($badLocations)) {
            echo "\n  NOTE: " . count($badLocations) . " row(s) had a location_id with no matching row in locations - set to NULL on inv_items instead of crashing:\n";
            foreach ($badLocations as $line) {
                echo "    - {$line}\n";
            }
        }
        if (!empty($badAmgs)) {
            echo "\n  NOTE: " . count($badAmgs) . " row(s) had an amg_id with no matching row in amgs - set to NULL on inv_items instead of crashing:\n";
            foreach ($badAmgs as $line) {
                echo "    - {$line}\n";
            }
        }
        if (!empty($badGarts)) {
            echo "\n  NOTE: " . count($badGarts) . " newly-inserted row(s) had a gart_id with no matching row in garts - set to NULL on inv_items instead of crashing:\n";
            foreach ($badGarts as $line) {
                echo "    - {$line}\n";
            }
        }
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
