<?php


namespace App\Helpers;

use App\Models\User;
use App\Models\UserActivity;
use App\Models\UserWorkout;
use App\Transformers\UserActivityTransformer;
use App\Transformers\UserWorkoutTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use League\Fractal\Manager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


use DatePeriod;
use DateInterval;

class ReportHelper
{
    /** @var User $user */
    public $user;

    /** @var string $dateRangeFrom */
    public $dateRangeFrom;

    /** @var string $dateRangeTo */
    public $dateRangeTo;

    /** @var Manager $fractal */
    //protected Manager $fractal;

    /**
     * FavoriteTransformer constructor.
     */
    public function __construct()
    {
        $this->fractal = new Manager();
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
                'type' => 'report',
                'attributes' => [
                    'date' => (string)$date->format('Y-m-d'), // (date Y-M-D),
                    'total_started_workouts' => (int)$this->calcTotalWorkouts($date, UserWorkout::STATUS_IN_PROGRESS), // (int),
                    'total_completed_workouts' => (int)$this->calcTotalWorkouts($date, UserWorkout::STATUS_COMPLETED), // (int),
                    'total_spent_time' => (int)$this->calcTotalSpentTimeWorkouts($date), // (int, in seconds) - по всем воркаутам в этот день,
                    'started_workouts' => (array)$this->transformWorkouts($date, UserWorkout::STATUS_IN_PROGRESS), // (collection of models),
                    'completed_workouts' => (array)$this->transformWorkouts($date, UserWorkout::STATUS_COMPLETED), // (collection of models),
                    'started_activities' => (array)$this->transformActivities($date, UserActivity::STATUS_STARTED), // (collection of models),
                    'completed_activities' => (array)$this->transformActivities($date, UserActivity::STATUS_COMPLETED), // (collection of models),
                ],
            ];
        }

        return $dateArr;
    }

    /**
     * @param string $date
     * @param string $status
     * @return int
     */
    protected function calcTotalWorkouts($date, $status = null)
    {
        return $this->workoutByDayWithStatus($date, $status)
            ->count();
    }

    /**
     * @param $date
     * @param null $status
     * @return UserWorkout
     */
    protected function workoutByDayWithStatus($date, $status = null)
    {
        $userWorkouts = UserWorkout::where('user_id', $this->user->id)
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
    protected function calcTotalSpentTimeWorkouts($date)
    {
        return $this->workoutByDayWithStatus($date)
            ->sum('total_time');
    }

    /**
     * @param $date
     * @param null $status
     * @return array|null
     */
    protected function transformWorkouts($date, $status = null)
    {
        $workouts = ($this->workoutByDayWithStatus($date, $status))->get();
        $collection = new \League\Fractal\Resource\Collection($workouts, new UserWorkoutTransformer, 'user_workout');
        $this->fractal->setSerializer(new \App\Serializers\CustomJsonApiSerializer());
        return $this->fractal->createData($collection)->toArray();
    }

    /**
     * @param $date
     * @param null $status
     * @return array|null
     */
    protected function transformActivities($date, $status = null)
    {
        $userActivities = UserActivity::where('user_id', $this->user->id)
            ->whereDay('created_at', $date);

        if ($status) {
            $userActivities->where('status', $status);
        }
        $userActivities = $userActivities->get();
        $collection = new \League\Fractal\Resource\Collection($userActivities, new UserActivityTransformer, 'user_activity');
        $this->fractal->setSerializer(new \App\Serializers\CustomJsonApiSerializer());
        return $this->fractal->createData($collection)->toArray();
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
