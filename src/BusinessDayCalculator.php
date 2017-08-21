<?php

namespace DeliveryCalculation;


use DeliveryCalculation\Interfaces\DeliveryDateCalculatorInterface;

class BusinessDayCalculator implements DeliveryDateCalculatorInterface
{

    /**
     * @param \DateTimeImmutable $date
     * @param $businessDays
     * @return \DateTimeImmutable
     */
    public function getDeliveryDate(\DateTimeImmutable $date, $businessDays)
    {
        $thisDay = new \DateTimeImmutable($date->format('Y-m-d'));
        $thisFriday = $thisDay->modify('next monday')->modify('last friday');

        $remainingDays = $businessDays;
        if ($thisFriday > $thisDay) {
            $remainingDays = $remainingDays - ($thisFriday->diff($thisDay)->days);
        } elseif ($thisFriday->diff($thisDay)->days === 0) {
            $remainingDays = $remainingDays - 1;
        }

        $numberOfWeeks = ceil($remainingDays / 5);
        $remainingDays = $remainingDays - $numberOfWeeks * 5;

        return $thisFriday->modify("+{$numberOfWeeks} weeks +{$remainingDays} days");
    }
}