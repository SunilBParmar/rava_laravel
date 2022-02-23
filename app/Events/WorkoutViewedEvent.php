<?php

namespace App\Events;

use App\Models\User;
use App\Models\Workout;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkoutViewedEvent extends Event
{
    use Dispatchable, SerializesModels;

    /** @var User $user */
    public $user;

    /** @var Workout $workout */
    public $workout;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param Workout $workout
     */
    public function __construct(User $user, Workout $workout)
    {
        $this->user = $user;
        $this->workout = $workout;
    }
}
