<?php

namespace App\Imports;

use App\Models\Assignment;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class AssignmentImport implements ToModel, WithHeadingRow, WithUpserts
{
    /**
     * @return string|array
     */
    public function uniqueBy()
    {
        return 'level_5_full_code';
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Skip if level_5_full_code is empty
        if (empty($row['level_5_full_code'])) {
            return null;
        }

        return new Assignment([
            'level_5_full_code'      => $row['level_5_full_code'],
            'nama_sls'               => $row['nama_sls'] ?? null,
            'total_assignment_fasih' => $row['total_assignment_fasih'] ?? 0,
            'ppl'                    => $row['ppl'] ?? null,
            'pml'                    => $row['pml'] ?? null,
        ]);
    }
}
