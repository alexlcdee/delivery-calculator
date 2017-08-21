<?php

namespace DeliveryCalculation;


use DeliveryCalculation\Interfaces\DeliveryMethodInterface;
use DeliveryCalculation\Interfaces\StrategyCalculationInterface;

class StrategyCalculation implements StrategyCalculationInterface
{
    /**
     * @var
     */
    private $price;
    /**
     * @var \DateInterval
     */
    private $time;
    /**
     * @var DeliveryMethodInterface
     */
    private $deliveryMethod;

    public function __construct($price, \DateInterval $time, DeliveryMethodInterface $deliveryMethod)
    {
        $this->price = $price;
        $this->time = $time;
        $this->deliveryMethod = $deliveryMethod;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return \DateInterval
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @return DeliveryMethodInterface
     */
    public function getDeliveryMethod()
    {
        return $this->deliveryMethod;
    }
}