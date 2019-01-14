<?php

namespace App\Modules\Activities\Response;

use App\Core\Base\Response;

/**
 * Class ErrorResponse
 *
 * @package App\Modules\Activities\Response
 * @author  Jaai Chandekar
 */
class ErrorResponse extends Response
{
    /**
     * Response attributes
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'status',
            'title',
            'detail',
            'type',
        ];
    }

}