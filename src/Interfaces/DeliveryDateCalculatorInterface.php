<?php

namespace DeliveryCalculation\Interfaces;


interface DeliveryDateCalculatorInterface
{
    /**
     * @param \DateTimeImmutable $date
     * @param $businessDays
     * @return \DateTimeImmutable
     */
    public function getDeliveryDate(\DateTimeImmutable $date, $businessDays);
}