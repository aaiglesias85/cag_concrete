<?php

namespace App\Service\Base;

class BaseCalendarMonthService
{
    /**
     * Mismo año-mes calendario (Y-m), para reglas de override por período.
     */
    public function isSameCalendarMonth(\DateTimeInterface $invoiceStart, \DateTimeInterface $overridePeriodDate): bool
    {
        return $invoiceStart->format('Y-m') === $overridePeriodDate->format('Y-m');
    }
}
