<?php


namespace App\Helpers;


use App\Models\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Class EventHelper
 * @package App\Helpers
 */
class EventHelper
{
    /**
     * @param $user
     * @param $entityId
     * @param $entityType
     * @param $message
     * @return bool
     */
    public static function sendNotification($user, $message, $entityId = null, $entityType = null)
    {
        return (new Notification([
            'user_id' => $user->id,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'status' => Notification::STATUS_UNREAD,
            'message' => $message,
        ]))->save();
    }

    public static function createWorkoutView($user, $workout)
    {
        DB::table('workout_view')->insert([
            [
                'user_id' => $user->id,
                'workout_id' => $workout->id,
                'created_at' => Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon::now()->toDateTimeString(),
            ]
        ]);
    }
}
