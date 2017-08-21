<?php

namespace DeliveryCalculation;


use DeliveryCalculation\Interfaces\CityInterface;
use DeliveryCalculation\Interfaces\OrderInterface;
use DeliveryCalculation\Interfaces\StrategyCalculationInterface;
use DeliveryCalculation\Interfaces\StrategyInterface;

class NullStrategy implements StrategyInterface
{

    /**
     * @param OrderInterface $order
     * @param CityInterface $from
     * @param CityInterface $to
     * @return StrategyCalculationInterface
     */
    public function calculate(
        OrderInterface $order,
        CityInterface $from,
        CityInterface $to
    ) {
        return new NullStrategyCalculation();
    }
}