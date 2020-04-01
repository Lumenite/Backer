<?php

namespace Lumenite\Backer\Values;

use Carbon\Carbon;

/**
 * @author Mohammed Mudassir <hello@mudasir.me>
 */
class QueryFilter
{
    protected $startDate = null;

    protected $endDate = null;

    /**
     * @return Carbon
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param Carbon $endDate
     * @return $this
     */
    public function setEndDate(Carbon $endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @return Carbon
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param Carbon $startDate
     * @return $this
     */
    public function setStartDate(Carbon $startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }
}
