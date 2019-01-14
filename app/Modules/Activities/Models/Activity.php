<?php

namespace App\Modules\Activities\Models;

/**
 * Class Activity
 *
 * @package App\Modules\Activities\Models
 * @author  Jaai Chandekar <jaai.chandekar@tajawal.com>
 */
class Activity
{
    /**
     * Sort activities by prices in ascending order
     *
     * @param array $allActivities
     *
     * @return array
     */
    public function getActivitiesByPriceAsc(array $allActivities): array
    {
        uasort($allActivities, function (array $a1, array $a2) {
            $price1 = $a1['price'] ?? 0;
            $price2 = $a2['price'] ?? 0;

            if ($price1 == $price2)
                return 0;

            return ($price1 < $price2) ? -1 : 1;

        });

        return $allActivities;
    }

    /**
     * Filter activities by city, country and non null price
     *
     * @param string $city
     * @param string $country
     * @param array  $allActivities
     *
     * @return array
     */
    public function filterActivitiesByCountryAndCity(string $city, string $country, array $allActivities): array
    {
        $allActivities = array_filter($allActivities, function ($activity) use ($city, $country) {

            return (($activity['city'] ?? '' == strtolower($city)) &&
                    ($activity['country'] ?? '' == strtolower($country)) &&
                    (isset($activity['price'])));
        });

        return $allActivities;
    }
}