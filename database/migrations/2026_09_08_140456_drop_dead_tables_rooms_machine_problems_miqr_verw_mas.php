<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Drops three tables confirmed dead end-to-end (no model, no controller,
 * no route, no view, no raw query referencing them anywhere in the
 * codebase) per the servsmt dead-code audit, section 1 "Fully dead
 * features":
 *
 *   - rooms            (was App\Room / RoomController)
 *   - machine_problems (was App\MachineProblems / MachineProblemsController)
 *   - miqr_verw_mas    (was App\MiqrVerwMa / MiqrVerwMaController)
 *
 * The model and controller files for all three have already been deleted.
 * This migration is NOT run automatically — verify row counts are zero
 * (or export any data first) before running `php74 artisan migrate`:
 *
 *   SELECT 'rooms' AS table_name, COUNT(*) AS row_count FROM rooms
 *   UNION ALL SELECT 'machine_problems', COUNT(*) FROM machine_problems
 *   UNION ALL SELECT 'miqr_verw_mas', COUNT(*) FROM miqr_verw_mas;
 */
class DropDeadTablesRoomsMachineProblemsMiqrVerwMas extends Migration
{
    public function up()
    {
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('machine_problems');
        Schema::dropIfExists('miqr_verw_mas');
    }

    /**
     * Intentionally not restorative. These tables belonged to dead,
     * never-used features, so recreating empty schemas on rollback would
     * be misleading (no code exists any more that reads or writes them).
     * If you ever need to roll back, restore from a DB backup instead.
     */
    public function down()
    {
        // Intentionally left blank — see class doc comment above.
    }
}
