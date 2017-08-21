<?php

namespace DeliveryCalculation\CDEK;


use DeliveryCalculation\Interfaces\CityInterface;
use DeliveryCalculation\Interfaces\DeliveryMethodInterface;
use DeliveryCalculation\Interfaces\OrderInterface;
use DeliveryCalculation\Interfaces\StrategyCalculationInterface;
use DeliveryCalculation\Interfaces\StrategyInterface;
use DeliveryCalculation\StrategyCalculation;

class Strategy implements StrategyInterface
{
    /**
     * @var APIConnectorInterface
     */
    private $connector;
    /**
     * @var CityInterface
     */
    private $from;
    /**
     * @var CityInterface
     */
    private $to;
    /**
     * @var StrategyInterface
     */
    private $thresholdStrategy;
    /**
     * @var DeliveryMethodInterface
     */
    private $deliveryMethod;

    /**
     * Strategy constructor.
     * @param APIConnectorInterface $connector
     * @param StrategyInterface $thresholdStrategy
     * @param DeliveryMethodInterface $deliveryMethod
     */
    public function __construct(
        APIConnectorInterface $connector,
        StrategyInterface $thresholdStrategy,
        DeliveryMethodInterface $deliveryMethod
    ) {
        $this->connector = $connector;
        $this->thresholdStrategy = $thresholdStrategy;
        $this->deliveryMethod = $deliveryMethod;
    }

    /**
     * @param OrderInterface $order
     * @param CityInterface $from
     * @param CityInterface $to
     * @return StrategyCalculationInterface
     */
    public function calculate(OrderInterface $order, CityInterface $from, CityInterface $to)
    {
        $thresholdCalculation = $this->getThresholdPrice($order, $from, $to);

        $cdekCalculation = $this->getCDEKCalculation($order, $from, $to);

        if ($cdekCalculation === false) {
            return null;
        }

        return new StrategyCalculation($thresholdCalculation->getPrice(), $cdekCalculation->getTime(),
            $this->deliveryMethod);
    }

    /**
     * @param OrderInterface $order
     * @param CityInterface $from
     * @param CityInterface $to
     * @return StrategyCalculationInterface
     */
    private function getThresholdPrice(OrderInterface $order, CityInterface $from, CityInterface $to)
    {
        return $this->thresholdStrategy->calculate($order, $from, $to);
    }

    private function getCDEKCalculation(OrderInterface $order, CityInterface $from, CityInterface $to)
    {
        $data = $this->connector->getData($from, $to, $order);

        if ($data === false) {
            return false;
        }
        $period = (int)$data['deliveryPeriodMax'];
        $period += 1;
        return new StrategyCalculation($data['price'], new \DateInterval("P{$period}D"),
            $this->deliveryMethod);
    }
}