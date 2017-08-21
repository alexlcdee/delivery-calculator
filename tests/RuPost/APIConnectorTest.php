<?php

namespace DeliveryCalculation\Tests\RuPost;

use DeliveryCalculation\Interfaces\CityInterface;
use DeliveryCalculation\Interfaces\OrderInterface;
use DeliveryCalculation\RuPost\APIConnector;
use PHPUnit\Framework\TestCase;

class APIConnectorTest extends TestCase
{
    public function test_getData()
    {
        $serverName = 'server.test.ru';
        $serverContactEmail = 'test@test.ru';
        $apiUrl = 'http://api.postcalc.ru';
        $from = $this->createCity('Москва', '101010');
        $to = $this->createCity('Рязань', '390000');
        $order = $this->createOrder(100, 100);
        $connector = new APIConnector($serverName, $serverContactEmail, $apiUrl);

        $response = $connector->getData($from, $to, $order);

        $this->assertTrue(is_array($response));
        $this->assertEquals('МОСКВА', $response['Откуда']['Название']);
        $this->assertEquals('390000', $response['Куда']['Индекс']);
    }

    /**
     * @param string $name
     * @param string $postalCode
     * @return CityInterface
     */
    public function createCity($name, $postalCode)
    {
        $city = $this->getMockBuilder(CityInterface::class)->getMock();
        $city->method('getCaption')->willReturn($name);
        $city->method('getPostalCode')->willReturn($postalCode);
        return $city;
    }

    /**
     * @param int $price
     * @param int $weight
     * @return OrderInterface
     */
    public function createOrder($price, $weight)
    {
        $order = $this->getMockBuilder(OrderInterface::class)->getMock();
        $order->method('getOverallItemsPrice')->willReturn($price);
        $order->method('getOverallItemsWeight')->willReturn($weight);
        return $order;
    }
}
