<?php

namespace App\Tests\Components\DeliveryCalculation;

use DeliveryCalculation\BusinessDayCalculator;
use PHPUnit\Framework\TestCase;

class BusinessDayCalculatorTest extends TestCase
{
    /**
     * @param $today
     * @param $interval
     * @param $valid
     * @dataProvider getDays
     */
    public function test_getDeliveryDate($today, $interval, $valid)
    {
        $todayDate = new \DateTimeImmutable($today);
        $validDate = new \DateTimeImmutable($valid);

        $calculator = new BusinessDayCalculator();

        $this->assertEquals($validDate, $calculator->getDeliveryDate($todayDate, $interval));
    }

    public function getDays()
    {
        return [
            ['2017-08-16', 2, '2017-08-18'],
            ['2017-08-16', 3, '2017-08-21'],
            ['2017-08-16', 5, '2017-08-23'],
            ['2017-08-16', 13, '2017-09-04'],
            ['2017-08-18', 2, '2017-08-21'],
            ['2017-08-19', 2, '2017-08-22'],
            ['2017-08-20', 2, '2017-08-22'],
            ['2017-08-18', 6, '2017-08-25'],
            ['2017-08-16', 43, '2017-10-16'],
            ['2017-08-16', 27, '2017-09-22'],
        ];
    }
}
