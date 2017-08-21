<?php

namespace DeliveryCalculation\Tests\CDEK;

use DeliveryCalculation\CDEK\CDEKLibraryAPIConnector;
use DeliveryCalculation\Interfaces\CityInterface;
use DeliveryCalculation\Interfaces\OrderInterface;
use PHPUnit\Framework\TestCase;

class CDEKLibraryAPIConnectorTest extends TestCase
{
    /**
     * @param $weight
     * @param $quantity
     * @param $period
     * @dataProvider dataProvider
     */
    public function test_getData($weight, $quantity, $period)
    {
        /** @var CityInterface $cityFrom */
        $cityFrom = $this->createCity('390048');
        /** @var CityInterface $cityTo */
        $cityTo = $this->createCity('101000');

        /** @var OrderInterface $order */
        $order = $this->createOrder($quantity, $weight);

        $connector = new CDEKLibraryAPIConnector(
            getenv('CDEKLogin'),
            getenv('CDEKPassword')
        );

        $data = $connector->getData($cityFrom, $cityTo, $order);

        $this->assertNotFalse($data);
        $this->assertArrayHasKey('deliveryPeriodMax', $data);
        $this->assertEquals($period, $data['deliveryPeriodMax']);
    }

    private function createCity($postalCode)
    {
        $city = $this->getMockBuilder(CityInterface::class)->getMock();
        $city->method('getPostalCode')->willReturn($postalCode);
        return $city;
    }

    private function createOrder($quantity, $weight)
    {
        $order = $this->getMockBuilder(OrderInterface::class)->getMock();
        $order->method('getPlacesQuantity')->willReturn($quantity);
        $order->method('getOverallItemsWeight')->willReturn($weight);
        return $order;
    }

    public function test_getData_returns_false()
    {
        /** @var CityInterface $cityFrom */
        $cityFrom = $this->createCity('390048');
        /** @var CityInterface $cityTo */
        $cityTo = $this->createCity('111111');

        /** @var OrderInterface $order */
        $order = $this->createOrder(1, 1000);

        $connector = new CDEKLibraryAPIConnector(
            getenv('CDEKLogin'),
            getenv('CDEKPassword')
        );

        $data = $connector->getData($cityFrom, $cityTo, $order);

        $this->assertFalse($data);
    }

    public function dataProvider()
    {
        return [
            [1000, 1, 2],
            [2000, 3, 2],
            [3000, 2, 2],
            [5000, 4, 2],
            [5000, 1, 2],
            [8000, 3, 2],
            [8000, 1, 2],
        ];
    }
}
