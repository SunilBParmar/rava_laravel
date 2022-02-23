<?php

namespace App\Events;

use App\Models\User;
use App\Models\UserWorkout;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AddedWorkoutEvent extends Event
{
    use Dispatchable, SerializesModels;

    /** @var User $user */
    public $user;

    /** @var UserWorkout $userWorkout */
    public $userWorkout;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param UserWorkout $userWorkout
     */
    public function __construct(User $user, UserWorkout $userWorkout)
    {
        $this->user = $user;
        $this->userWorkout = $userWorkout;
    }
}
