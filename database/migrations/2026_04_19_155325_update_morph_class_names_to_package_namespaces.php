<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected array $classMap = [
        'App\\Models\\Media'                 => 'Type0\\Media\\Models\\Media',
        'App\\Models\\MediaReference'        => 'Type0\\Media\\Models\\MediaReference',
        'App\\Models\\SeoMetadata'           => 'Type0\\Seo\\Models\\SeoMetadata',
        'App\\Plugins\\Blog\\Models\\Post'     => 'Type0\\Blog\\Models\\Post',
        'App\\Plugins\\Blog\\Models\\Category' => 'Type0\\Blog\\Models\\Category',
        'App\\Plugins\\Blog\\Models\\Tag'      => 'Type0\\Blog\\Models\\Tag',
    ];

    public function up(): void
    {
        $tables = ['media_references', 'seo_metadata'];

        foreach ($tables as $table) {
            foreach ($this->classMap as $old => $new) {
                DB::table($table)
                    ->where('model_type', $old)
                    ->update(['model_type' => $new]);
            }
        }
    }

    public function down(): void
    {
        $tables = ['media_references', 'seo_metadata'];
        $reversed = array_flip($this->classMap);

        foreach ($tables as $table) {
            foreach ($reversed as $old => $new) {
                DB::table($table)
                    ->where('model_type', $old)
                    ->update(['model_type' => $new]);
            }
        }
    }
};
