<?php


namespace App\Helpers;

use App\Models\Favorite;
use App\Models\User;
use App\Models\UserWorkout;
use App\Models\Workout;
use App\Models\WorkoutView;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use League\Fractal\Manager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


use DatePeriod;
use DateInterval;

class AnalyticHelper
{
    /** @var User $user */
    public $user;

    /** @var Workout|null $workout */
    public $workout = null;

    /** @var string $dateRangeFrom */
    public $dateRangeFrom;

    /** @var string $dateRangeTo */
    public $dateRangeTo;

    /** @var Manager $fractal */
    //protected Manager $fractal;

    /**
     * FavoriteTransformer constructor.
     */
    public function __construct($workout = null)
    {
        $this->fractal = new Manager();
        $this->workout = $workout;
    }

    /**
     * @param Request $request
     */
    public function listing(Request $request)
    {
        $this->populateProperties($request);

        Carbon::macro('datePeriod', static function ($startDate, $endDate) {
            return new DatePeriod($startDate, new DateInterval('P1D'), $endDate);
        });

        $dateArr = [];
        foreach (Carbon::datePeriod(Carbon::createFromDate(Carbon::parse($this->dateRangeFrom)->startOfDay()), Carbon::createMidnightDate(Carbon::parse($this->dateRangeTo))->endOfDay()) as $date) {
            $dateArr[] = [
                'type' => 'analytics',
                'attributes' => [
                    'date' => (string)$date->format('Y-m-d'), // (date Y-M-D),
                    'total_users_started_workouts' => (int)$this->calcUsersTotalWorkouts($date, UserWorkout::STATUS_IN_PROGRESS),
                    'total_users_completed_workouts' => (int)$this->calcUsersTotalWorkouts($date, UserWorkout::STATUS_COMPLETED),
                    'total_users_spent_time' => (int)$this->calcUsersTotalSpentTimeWorkouts($date),
                    'total_users_added_to_favorites' => (int)$this->calcUsersTotalAddedToFavorites($date),
                    'total_users_views' => (int)$this->calcUsersTotalView($date)
                ],
            ];
        }

        return $dateArr;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function info(Request $request)
    {
        return $this->listing($request);
    }

    protected function calcUsersTotalView($date)
    {
        $user = $this->user;
        $workout = $this->workout;
        return WorkoutView::whereHas('workout', function ($q) use ($user, $workout) {
            $q->where('user_id', $user->id);
            if ($workout) {
                $q->where('workout_id', $workout->id);
            }
        })->whereDay('created_at', $date)
            ->distinct(['user_id', 'workout_id'])
            ->count();
    }

    /**
     * @param string $date
     * @param string $status
     * @return int
     */
    protected function calcUsersTotalWorkouts($date, $status = null)
    {
        return $this->usersWorkoutByDayWithStatus($date, $status)
            ->count();
    }

    /**
     * @param $date
     * @param null $status
     * @return UserWorkout
     */
    protected function usersWorkoutByDayWithStatus($date, $status = null)
    {
        $user = $this->user;
        $workout = $this->workout;
        $userWorkouts = UserWorkout::whereHas('workout', function ($q) use ($user, $workout) {
            $q->where('workouts.user_id', $user->id);
            if ($workout) {
                $q->where('workout_id', $workout->id);
            }
        })
            ->whereDay('created_at', $date);

        if ($status) {
            $userWorkouts->where('status', $status);
        }

        return $userWorkouts;
    }

    /**
     * @param string $date
     * @return int
     */
    protected function calcUsersTotalSpentTimeWorkouts($date)
    {
        $user = $this->user;
        $workout = $this->workout;
        return UserWorkout
            ::whereHas('workout', function ($q) use ($user, $workout) {
                $q->where('workouts.user_id', $user->id);
                if ($workout) {
                    $q->where('workout_id', $workout->id);
                }
            })
            ->whereDay('created_at', $date)
            ->sum('total_time');
    }

    /**
     * @param string $date
     * @return int
     */
    protected function calcUsersTotalAddedToFavorites($date)
    {
        $user = $this->user;
        $workout = $this->workout;
        return Favorite::whereHas('workout', function ($q) use ($user, $workout) {
            $q->where('user_id', $user->id);
            if ($workout) {
                $q->where('workout_id', $workout->id);
            }
        })->whereDay('created_at', $date)
            ->count();
    }

    /**
     * @param Request $request
     */
    protected function populateProperties(Request $request)
    {
        $this->dateRangeFrom = $request->input('date_range_from');
        $this->dateRangeTo = $request->input('date_range_to');
        $this->user = User::find((int)$request->input('user_id', null)) ?? Auth::user();

        if (!$this->user) {
            throw new NotFoundHttpException('User not found');
        }
    }
}
