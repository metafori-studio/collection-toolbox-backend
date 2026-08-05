<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('etno_items')
            ->whereNotNull('document_overrides')
            ->orderBy('id')
            ->chunkById(100, function ($items) {
                foreach ($items as $item) {
                    $overrides = json_decode($item->document_overrides, true) ?? [];
                    if (empty($overrides)) {
                        continue;
                    }

                    $updated = false;
                    $newOverrides = [];

                    foreach ($overrides as $override) {
                        if (in_array($override, ['time_period_start', 'time_period_end', 'time_period_settings'], true)) {
                            $newOverrides[] = 'time_period';
                            $updated = true;
                        } elseif (in_array($override, ['submission_date_start', 'submission_date_end', 'submission_date_settings'], true)) {
                            $newOverrides[] = 'submission_date';
                            $updated = true;
                        } elseif (in_array($override, ['publication_date_start', 'publication_date_end', 'publication_date_settings'], true)) {
                            $newOverrides[] = 'publication_date';
                            $updated = true;
                        } else {
                            $newOverrides[] = $override;
                        }
                    }

                    if ($updated) {
                        $newOverrides = array_values(array_unique($newOverrides));
                        DB::table('etno_items')
                            ->where('id', $item->id)
                            ->update(['document_overrides' => json_encode($newOverrides)]);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('etno_items')
            ->whereNotNull('document_overrides')
            ->orderBy('id')
            ->chunkById(100, function ($items) {
                foreach ($items as $item) {
                    $overrides = json_decode($item->document_overrides, true) ?? [];
                    if (empty($overrides)) {
                        continue;
                    }

                    $updated = false;
                    $newOverrides = [];

                    foreach ($overrides as $override) {
                        if ($override === 'time_period') {
                            array_push($newOverrides, 'time_period_start', 'time_period_end', 'time_period_settings');
                            $updated = true;
                        } elseif ($override === 'submission_date') {
                            array_push($newOverrides, 'submission_date_start', 'submission_date_end', 'submission_date_settings');
                            $updated = true;
                        } elseif ($override === 'publication_date') {
                            array_push($newOverrides, 'publication_date_start', 'publication_date_end', 'publication_date_settings');
                            $updated = true;
                        } else {
                            $newOverrides[] = $override;
                        }
                    }

                    if ($updated) {
                        $newOverrides = array_values(array_unique($newOverrides));
                        DB::table('etno_items')
                            ->where('id', $item->id)
                            ->update(['document_overrides' => json_encode($newOverrides)]);
                    }
                }
            });
    }
};
