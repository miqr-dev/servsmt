<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

/**
 * Teilnehmer Liste Excel exports ("Excel" and "Excel Erweitert").
 *
 * Same sheet layout the old DataTables excel buttons produced: row 1 is the
 * title (DataTables used the page <title>, "MIQR | SMT"), row 2 the column
 * headings, data from row 3 on. The two buttons only differ in columns,
 * which the controller passes in.
 */
class ParticipantListExport implements FromArray, ShouldAutoSize
{
    public function __construct(
        private string $title,
        private array $headings,
        private array $rows,
    ) {
    }

    public function array(): array
    {
        return array_merge([[$this->title], $this->headings], $this->rows);
    }
}
