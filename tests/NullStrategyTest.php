<?php

namespace DeliveryCalculation\Tests;

use DeliveryCalculation\Interfaces\CityInterface;
use DeliveryCalculation\Interfaces\OrderInterface;
use DeliveryCalculation\NullStrategy;
use DeliveryCalculation\NullStrategyCalculation;
use PHPUnit\Framework\TestCase;

class NullStrategyTest extends TestCase
{
    public function test_calculate()
    {
        $strategy = new NullStrategy();

        $order = $this->getMockBuilder(OrderInterface::class)->getMock();

        $from = $to = $this->getMockBuilder(CityInterface::class)->getMock();

        $this->assertEquals(new NullStrategyCalculation(), $strategy->calculate($order, $from, $to));
    }
}
