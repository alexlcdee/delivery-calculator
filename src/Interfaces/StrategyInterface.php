<?php

namespace DeliveryCalculation\Interfaces;


interface StrategyInterface
{
    /**
     * @param OrderInterface $order
     * @param CityInterface $from
     * @param CityInterface $to
     * @return StrategyCalculationInterface
     */
    public function calculate(OrderInterface $order, CityInterface $from, CityInterface $to);
}