<?php

namespace DeliveryCalculation\Tests\CDEK;

use DeliveryCalculation\CDEK\APIConnectorInterface;
use DeliveryCalculation\CDEK\Strategy;
use DeliveryCalculation\Interfaces\CityInterface;
use DeliveryCalculation\Interfaces\DeliveryMethodInterface;
use DeliveryCalculation\Interfaces\OrderInterface;
use DeliveryCalculation\StrategyCalculation;
use DeliveryCalculation\ThresholdStrategy\Strategy as ThresholdStrategy;
use DeliveryCalculation\ThresholdStrategy\Threshold;
use PHPUnit\Framework\TestCase;

class StrategyTest extends TestCase
{
    /**
     * @param float $orderPrice
     * @param float $deliveryInterval
     * @param float $validPrice
     * @param array $thresholds
     * @dataProvider getCalculationData
     */
    public function test_calculate($orderPrice, $deliveryInterval, $validPrice, array $thresholds)
    {
        $cityCaptionFrom = 'Москва';
        $cityCaptionTo = 'Рязань';
        $cityPostalCodeFrom = '101000';
        $cityPostalCodeTo = '390000';

        $orderWeight = 1000;

        $priceCoefficient = 1;

        $cdekPrice = $validPrice + 1000;

        /** @var APIConnectorInterface $connector */
        $connector = $this->createConnector([
            'price'             => $cdekPrice,
            'deliveryPeriodMax' => $deliveryInterval,
        ]);
        /** @var CityInterface $from */
        $from = $this->createCity($cityCaptionFrom, $cityPostalCodeFrom);
        /** @var CityInterface $to */
        $to = $this->createCity($cityCaptionTo, $cityPostalCodeTo);

        /** @var OrderInterface $order */
        $order = $this->createOrder($orderWeight, $orderPrice);

        $strategy = new Strategy($connector, $this->createThresholdStrategy($priceCoefficient, $thresholds),
            $this->createDeliveryMethod($priceCoefficient, $thresholds));

        $validInterval = $deliveryInterval + 1;

        $this->assertEquals(
            new StrategyCalculation((float)$validPrice, new \DateInterval("P{$validInterval}D"),
                $this->createDeliveryMethod($priceCoefficient, $thresholds)),
            $strategy->calculate($order, $from, $to)
        );
    }

    private function createConnector($result)
    {
        $connector = $this->getMockBuilder(APIConnectorInterface::class)->getMock();
        $connector->method('getData')->willReturn($result);
        return $connector;
    }

    private function createCity($name, $postalCode)
    {
        $city = $this->getMockBuilder(CityInterface::class)->getMock();
        $city->method('getCaption')->willReturn($name);
        $city->method('getPostalCode')->willReturn($postalCode);
        return $city;
    }

    private function createOrder($weigh, $price)
    {
        $order = $this->getMockBuilder(OrderInterface::class)->getMock();
        $order->method('getOverallItemsWeight')->willReturn($weigh);
        $order->method('getOverallItemsPrice')->willReturn($price);
        return $order;
    }

    private function createThresholdStrategy($priceCoefficient, array $thresholds)
    {
        /** @var DeliveryMethodInterface $deliveryMethod */
        $deliveryMethod = $this->createDeliveryMethod($priceCoefficient, $thresholds);
        return new ThresholdStrategy($deliveryMethod, function (Threshold $threshold, OrderInterface $order) {
            return $threshold->lessThan($order->getOverallItemsPrice());
        });
    }

    private function createDeliveryMethod($priceCoefficient, array $thresholds)
    {
        $deliveryMethod = $this->getMockBuilder(DeliveryMethodInterface::class)->getMock();
        $deliveryMethod->method('getPriceCoefficient')->willReturn($priceCoefficient);
        $deliveryMethod->method('getThresholds')->willReturnCallback(function () use ($thresholds) {
            return array_map(function ($thresholdSpec) {
                return new Threshold($thresholdSpec[0], $thresholdSpec[1]);
            }, $thresholds);
        });
        return $deliveryMethod;
    }

    public function test_calculate_on_api_return_false()
    {
        $cityCaptionFrom = 'Москва';
        $cityCaptionTo = 'Рязань';
        $cityPostalCodeFrom = '101000';
        $cityPostalCodeTo = '390000';

        $orderWeight = 1000;
        $orderPrice = 100;

        $priceCoefficient = 1;

        /** @var APIConnectorInterface $connector */
        $connector = $this->createConnector(false);
        /** @var CityInterface $from */
        $from = $this->createCity($cityCaptionFrom, $cityPostalCodeFrom);
        /** @var CityInterface $to */
        $to = $this->createCity($cityCaptionTo, $cityPostalCodeTo);

        /** @var OrderInterface $order */
        $order = $this->createOrder($orderWeight, $orderPrice);

        $thresholds = [
            [100, 90],
            [250, 85],
            [500, 75],
            [1000, 60],
        ];

        $strategy = new Strategy($connector, $this->createThresholdStrategy($priceCoefficient, $thresholds),
            $this->createDeliveryMethod($priceCoefficient, $thresholds));

        $this->assertNull($strategy->calculate($order, $from, $to));
    }

    /**
     * @return array [
     *  float $orderPrice,
     *  float $deliveryInterval,
     *  float $validPrice,
     *  array $thresholds [float $threshold, value $value]
     * ]
     */
    public function getCalculationData()
    {
        return [
            [
                250,
                10,
                90,
                [
                    [100, 90],
                    [250, 85],
                    [500, 75],
                    [1000, 60],
                ],
            ],
            [
                330,
                2,
                85,
                [
                    [100, 90],
                    [250, 85],
                    [500, 75],
                    [1000, 60],
                ],
            ],
        ];
    }
}
