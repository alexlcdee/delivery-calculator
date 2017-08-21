<?php

namespace DeliveryCalculation;


use DeliveryCalculation\Interfaces\StrategyCalculationInterface;

class NullStrategyCalculation implements StrategyCalculationInterface
{

    /**
     * @return float
     */
    public function getPrice()
    {
        return null;
    }

    /**
     * @return \DateInterval
     */
    public function getTime()
    {
        return null;
    }

    public function getDeliveryMethod()
    {
        return null;
    }
}