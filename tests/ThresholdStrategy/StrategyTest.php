<?php

namespace DeliveryCalculation\Tests\ThresholdStrategy;

use DeliveryCalculation\Interfaces\CityInterface;
use DeliveryCalculation\Interfaces\DeliveryMethodInterface;
use DeliveryCalculation\Interfaces\OrderInterface;
use DeliveryCalculation\StrategyCalculation;
use DeliveryCalculation\ThresholdStrategy\Strategy;
use DeliveryCalculation\ThresholdStrategy\Threshold;
use PHPUnit\Framework\TestCase;

class StrategyTest extends TestCase
{
    public function test_calculate()
    {
        $cityCaptionFrom = 'Москва';
        $cityCaptionTo = 'Рязань';
        $cityPostalCodeFrom = '101000';
        $cityPostalCodeTo = '390000';

        $method = $this->getMockBuilder(DeliveryMethodInterface::class)->getMock();
        $method->method('getThresholds')->willReturn([
            new Threshold(200, 400),
            new Threshold(100, 500),
            new Threshold(300, 300),
        ]);
        $method->method('getPriceCoefficient')->willReturn(0.5);

        $strategy = new Strategy($method, function (Threshold $threshold, OrderInterface $order) {
            return $threshold->lessThan($order->getOverallItemsPrice());
        });

        $builder = $this->getMockBuilder(OrderInterface::class);

        /** @var CityInterface $from */
        $from = $this->createCity($cityCaptionFrom, $cityPostalCodeFrom);
        /** @var CityInterface $to */
        $to = $this->createCity($cityCaptionTo, $cityPostalCodeTo);

        $order = $builder->getMock();
        $order->method('getOverallItemsPrice')->willReturn(120);
        $this->assertEquals(new StrategyCalculation(250, new \DateInterval(Strategy::DEFAULT_DATE_INTERVAL),
            $method),
            $strategy->calculate($order, $from, $to));

        $order = $builder->getMock();
        $order->method('getOverallItemsPrice')->willReturn(201);
        $this->assertEquals(new StrategyCalculation(200, new \DateInterval(Strategy::DEFAULT_DATE_INTERVAL),
            $method),
            $strategy->calculate($order, $from, $to));

        $order = $builder->getMock();
        $order->method('getOverallItemsPrice')->willReturn(90);
        $this->assertEquals(new StrategyCalculation(150, new \DateInterval(Strategy::DEFAULT_DATE_INTERVAL),
            $method),
            $strategy->calculate($order, $from, $to));
    }

    private function createCity($name, $postalCode)
    {
        $city = $this->getMockBuilder(CityInterface::class)->getMock();
        $city->method('getCaption')->willReturn($name);
        $city->method('getPostalCode')->willReturn($postalCode);
        return $city;
    }
}
