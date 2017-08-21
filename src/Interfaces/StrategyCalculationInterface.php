<?php

namespace DeliveryCalculation\Interfaces;


interface StrategyCalculationInterface
{
    /**
     * @return float
     */
    public function getPrice();

    /**
     * @return \DateInterval
     */
    public function getTime();

    /**
     * @return DeliveryMethodInterface
     */
    public function getDeliveryMethod();
}