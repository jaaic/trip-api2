<?php

namespace App\Modules\Activities\Services;

use App\Core\Constants;
use App\Exceptions\BadRequestException;
use App\Modules\Activities\Models\Activity;

/**
 * Class FetchActivityService
 *
 * @package App\Modules\Activities\Services
 * @author  Jaai Chandekar
 */
class FetchActivityService
{
    /**
     * @var object
     */
    protected $request;

    protected $budgetSpent     = 0;
    protected $relocationTime  = 0;
    protected $totalActivities = 0;
    protected $totalBudget     = 0;
    protected $totalDays       = 0;

    /**
     * FetchActivityService  constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->request = $attributes;

    }

    /**
     * Get Activities
     *
     * @param array $allActivities
     *
     * @return array
     *
     * @throws \App\Exceptions\BadRequestException
     */
    public function getActivities(array $allActivities): array
    {
        $city              = $this->request['city'] ?? Constants::DEFAULT_CITY;
        $country           = $this->request['country'] ?? Constants::DEFAULT_COUNTRY;
        $this->totalDays   = $days = $this->request['days'];
        $this->totalBudget = $this->request['budget'];

        $collection    = new Activity();
        $allActivities = $collection->filterActivitiesByCountryAndCity($city, $country, $allActivities);
        $allActivities = $collection->getActivitiesByPriceAsc($allActivities);

        $cheapestActivity = current($allActivities);
        $cheapestPrice    = $cheapestActivity['price'] ?? 0;

        // check budget
        if ($cheapestPrice >= $this->totalBudget) {
            throw new BadRequestException('Budget lesser than cheapest activity!');
        }

        // min activities needed per day is 3
        // so exit if that is not possible within budget
        $cheapestBudget = $days * $cheapestPrice * Constants::MIN_ACTIVITIES_PER_DAY;

        if ($cheapestBudget > $this->totalBudget) {
            throw new BadRequestException('Budget lesser than 3 cheapest activities per day!');
        }

        $filteredByDay = $this->getActivitiesByDay($allActivities, $days, $this->totalBudget);

        $response = [
            'schedule' => [
                'summary' => [
                    'budget_spent'       => $this->budgetSpent,
                    'time_in_relocation' => ($this->totalActivities - ($this->totalDays)) * Constants::COMMUTE_MINS,
                    'total_activities'   => $this->totalActivities,
                ],
                'days'    => $filteredByDay,
            ],
        ];

        return $response;
    }

    /**
     * Get activities per day
     *
     * @param array $activities
     * @param int   $days
     * @param int   $avgBudgetPerDay
     *
     * @return array
     * @throws \App\Exceptions\BadRequestException
     */
    public function getActivitiesByDay(array $activities, int $days, int $avgBudgetPerDay)
    {
        $dayInfo = [];
        $day     = 1;

        while ($day <= $days) {
            $dayInfo[] = $this->getItineraryPerDay($activities, $day, $avgBudgetPerDay);

            $day++;
        }

        return $dayInfo;
    }

    /**
     * Get itinerary per day
     *
     * @param     $activities
     * @param int $day
     * @param int $budgetPerDay
     *
     * @return array
     *
     * @throws \App\Exceptions\BadRequestException
     */
    public function getItineraryPerDay(&$activities, $day, int $budgetPerDay)
    {
        $totalMins          = Constants::HOURS_PER_DAY * 60; // 720
        $minCommute         = 60; // 30 mins between each activity and min activities per day = 3
        $totalAvailMins     = $totalMins - $minCommute; //660
        $maxMinsPerActivity = $totalAvailMins - (2 * Constants::MIN_ACTIVITY_MINS);

        // try to filter out activities that need more time than the max. time available in 1 day
        $activities = array_filter($activities, function ($activity) use ($maxMinsPerActivity) {

            // default isVisited to 0
            // default duration to max time if absent so that activity will be excluded
            return (($activity['isVisited'] ?? 0) == 0) && (($activity['duration'] ?? 720) < $maxMinsPerActivity);
        });

        $start      = Constants::ACTIVITY_START_TIME;
        $itinerary  = [];
        $minsLeft   = $totalMins;
        $budgetLeft = $budgetPerDay;
        $timeSpent  = 0;

        foreach ($activities as &$activity) {
            $duration = $activity['duration'] ?? 0;
            $price    = $activity['price'] ?? 0;


            if (empty($duration) || (($activity['isVisited'] ?? 0) == 1) || ($price > $budgetLeft)) {
                continue;
            }

            if ($duration > $minsLeft) {
                continue;
            }

            $element = [
                'start'    => $start,
                'activity' => [
                    'id'       => $activity['id'] ?? '',
                    'duration' => $duration,
                    'price'    => $price,
                ],
            ];

            $itinerary[] = $element;

            $activity['isVisited'] = 1; // do not pick same activity again on any days
            $this->budgetSpent     += $price;
            $nextStart             = $duration + Constants::COMMUTE_MINS;
            $timeSpent             += $nextStart;

            // check if multiple of 30
            if ($nextStart % 30 != 0) {
                $multiple = ceil($nextStart / 30);

                $nextStart = 30 * $multiple;
            }
            $start = date("H:i", strtotime("+$nextStart minutes", strtotime($start)));

            $minsLeft = $totalMins - $timeSpent;

            $budgetLeft = $budgetPerDay - $this->budgetSpent;
        }

        if (count($itinerary) < Constants::MIN_ACTIVITIES_PER_DAY) {
            throw new BadRequestException(Constants::MIN_ACTIVITIES_PER_DAY . ' itineraries per day cannot be fetched!');

        }
        $this->totalActivities += count($itinerary);

        // format to day info
        $dayInfo = [
            'day'       => $day,
            'itinerary' => $itinerary,
        ];

        return $dayInfo;
    }
}