<?php

namespace DeliveryCalculation\ThresholdStrategy;


use DeliveryCalculation\Interfaces\CityInterface;
use DeliveryCalculation\Interfaces\DeliveryMethodInterface;
use DeliveryCalculation\Interfaces\OrderInterface;
use DeliveryCalculation\Interfaces\StrategyCalculationInterface;
use DeliveryCalculation\Interfaces\StrategyInterface;
use DeliveryCalculation\StrategyCalculation;
use Webmozart\Assert\Assert;

class Strategy implements StrategyInterface
{
    const DEFAULT_DATE_INTERVAL = 'P3D';
    /**
     * @var Threshold[]
     */
    private $thresholds;
    /**
     * @var callable
     */
    private $compareStrategy;
    /**
     * @var DeliveryMethodInterface
     */
    private $deliveryMethod;

    /**
     * Strategy constructor.
     * @param DeliveryMethodInterface $deliveryMethod
     * @param callable $compareStrategy function(Threshold $threshold, OrderInterface $order):bool
     */
    public function __construct(DeliveryMethodInterface $deliveryMethod, callable $compareStrategy)
    {
        $thresholds = $deliveryMethod->getThresholds();
        Assert::greaterThan(count($thresholds), 0, 'There must be at least one threshold in list');
        Assert::allIsInstanceOf($thresholds, Threshold::class);
        usort($thresholds, function (Threshold $item1, Threshold $item2) {
            return (!$item1->equalTo($item2->getThreshold())) ?
                ($item1->lessThan($item2->getThreshold()) ? 1 : -1)
                : 0;
        });
        $this->thresholds = $thresholds;
        $this->compareStrategy = $compareStrategy;
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
        $strategy = $this->compareStrategy;
        foreach ($this->thresholds as $threshold) {
            if ($strategy($threshold, $order)) {
                return new StrategyCalculation(
                    $threshold->getValue() * $this->deliveryMethod->getPriceCoefficient(),
                    new \DateInterval(static::DEFAULT_DATE_INTERVAL),
                    $this->deliveryMethod
                );
            }
        }
        return new StrategyCalculation(
            $this->thresholds[0]->getValue() * $this->deliveryMethod->getPriceCoefficient(),
            new \DateInterval(static::DEFAULT_DATE_INTERVAL),
            $this->deliveryMethod
        );
    }
}