<?php

namespace App\Modules\Activities\Request;

use App\Core\Base\Request;
use App\Core\Constants;
use App\Exceptions\BadRequestException;
use App\Exceptions\ServerException;
use App\Modules\Activities\Services\FetchActivityService;
/**
 * Class ActivityRequest
 *
 * @property integer budget  Activity budget
 * @property integer days    Total days
 * @property string  city    Activity city
 * @property string  country Activity country
 *
 *
 * @package App\Modules\Activities\Request
 * @author  Jaai Chandekar
 */
class ActivityRequest extends Request
{
    /** @var array */
    protected $requestData;

    /** @var string */
    protected $activitiesDir = __DIR__ . '/../../../../database/seeds/data/activities/';

    /**
     * ActivityRequest constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->requestData = $data;
    }

    /**
     * Request attributes
     *
     * @return array
     */
    function attributes(): array
    {
        return [
            'budget',
            'days',
            'city',
            'country',
        ];
    }

    /**
     * Check per day budget
     *
     * @return $this
     *
     * @throws \App\Exceptions\BadRequestException
     */
    public function validate()
    {
        $budget = intval($this->requestData['budget'] ?? '');
        if (empty($budget) || !is_int($budget) || $budget < 100 || $budget > 5000) {
            throw new BadRequestException('Input budget should be between 100 & 5000 inclusive');

        }

        $totalDays = intval($this->requestData['days'] ?? '');
        if (empty($totalDays) || !is_int($totalDays) || $totalDays < 1 || $totalDays > 5) {
            throw new BadRequestException('Input days should be between 1 & 5 inclusive');

        }

        // check if min budget per day >= 50
        if (($budget / $totalDays) < Constants::MIN_BUDGET_PER_DAY) {
            var_dump('here');
            throw new BadRequestException('Min. budget per day should be >= ' . Constants::MIN_BUDGET_PER_DAY);
        }

        return $this;
    }

    /**
     * Process request
     *
     * @return array
     *
     * @throws \App\Exceptions\ServerException
     * @throws \App\Exceptions\BaseException
     */
    public function process(): array
    {
        $activities = $this->readActivities();
        $response   = (new FetchActivityService($this->getAttributes()))->getActivities($activities);

        return $response;
    }

    /**
     * Read all activity files from the directory
     *
     * @return array
     *
     * @throws \App\Exceptions\ServerException
     */
    public function readActivities(): array
    {
        $activities = [];
        if (is_dir($this->activitiesDir)) {
            if ($dh = opendir($this->activitiesDir)) {
                while (($file = readdir($dh)) !== false) {
                    // ignore links to current and parent dirs
                    if (($file != '.') && ($file != '..')) {
                        $fileJson = file_get_contents($this->activitiesDir . $file);

                        $dataArray = json_decode($fileJson, true);

                        if (!empty(json_last_error()) || empty($dataArray)) {
                            throw new ServerException('Invalid Activities file : ' . $this->activitiesDir . $file);
                        }

                        $fileParts = explode('_', $file);
                        $city      = $fileParts[0] ?? Constants::DEFAULT_CITY;

                        // could be fetched from file name if the filename structure
                        // is like <country>_<city>_<number>.json
                        $country = Constants::DEFAULT_COUNTRY;

                        foreach ($dataArray as $activity) {
                            $activity['city']    = $city;
                            $activity['country'] = $country;
                            $activities[]        = $activity;
                        }
                    }
                }
            }
            closedir($dh);
        }

        return $activities;
    }
}