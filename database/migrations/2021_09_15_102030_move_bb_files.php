<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MoveBbFiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        set_time_limit(0);

        $bbHelper = new \App\Helpers\BBHelper();
        $files = \App\Models\File::all();
        foreach ($files as $file) {
            $fullName = $file->name . $file->ext;
            $newPath = \App\Helpers\BBHelper::VIDEOS_DIR . $fullName;
            $res = $bbHelper->moveFolderWithObjects($fullName, $newPath);
            $statusCode = $res['@metadata']['statusCode'] ?? null;
            $publicUrl = $res['@metadata']['effectiveUri'] ?? null;
            if ($statusCode === 200 && $publicUrl && $bbHelper->deleteFile($file)) {
                $file->public_url = $publicUrl;
                $file->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
