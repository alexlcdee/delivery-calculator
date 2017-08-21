<?php

namespace DeliveryCalculation\Tests\RuPost;

use DeliveryCalculation\Interfaces\CityInterface;
use DeliveryCalculation\Interfaces\DeliveryMethodInterface;
use DeliveryCalculation\Interfaces\OrderInterface;
use DeliveryCalculation\RuPost\APIConnectorInterface;
use DeliveryCalculation\RuPost\Strategy;
use DeliveryCalculation\StrategyCalculation;
use PHPUnit\Framework\TestCase;

class StrategyTest extends TestCase
{

    /**
     * @param int $orderWeight
     * @param float $priceCoefficient
     * @param float $postcalcPrice
     * @param float $returnPrice
     * @param float $returnAviaPrice
     * @param int $deliveryInterval
     * @dataProvider getCalculatorData
     */
    public function test_calculate(
        $orderWeight,
        $priceCoefficient,
        $postcalcPrice,
        $returnPrice,
        $returnAviaPrice,
        $deliveryInterval
    ) {
        $orderPrice = 1000;

        /** @var APIConnectorInterface $connector */
        $connector = $this->createConnector($postcalcPrice, $deliveryInterval);

        /** @var CityInterface $from */
        $from = $this->createCity('Moscow', '101000');
        /** @var CityInterface $to */
        $to = $this->createCity('Ryazan', '390000');

        /** @var OrderInterface $order */
        $order = $this->createOrder($orderWeight, $orderPrice);

        $deliveryMethod = $this->getMockBuilder(DeliveryMethodInterface::class)->getMock();
        $deliveryMethod->method('getPriceCoefficient')->willReturn($priceCoefficient);

        $strategy = new Strategy($connector, $deliveryMethod, 'ЦеннаяПосылка');

        $this->assertEquals(
            new StrategyCalculation((float)$returnPrice, new \DateInterval('P' . ($deliveryInterval + 2) . 'D'),
                $deliveryMethod),
            $strategy->calculate($order, $from, $to)
        );
    }

    private function createConnector($returnPrice, $deliveryInterval, array $limits = null, $noCalculation = false)
    {
        $connector = $this->getMockBuilder(APIConnectorInterface::class)->getMock();
        $connector->method('getData')->willReturnCallback(function (
            CityInterface $from,
            CityInterface $to,
            OrderInterface $order
        ) use ($returnPrice, $deliveryInterval, $limits, $noCalculation) {
            $data = [
                'Отправления'                 => [
                    'ЦеннаяПосылка' => [
                        'Название'      => 'Ценная посылка',
                        'Количество'    => '1',
                        'Тариф'         => $returnPrice,
                        'Доставка'      => $returnPrice,
                        'ПредельныйВес' => '20000',
                        'Проверено'     => '1',
                        'СрокДоставки'  => $deliveryInterval,
                        'ВычетНДС'      => '1',
                        'Ценное'        => '1',
                        'Товарное'      => '1',
                        'КлассДоставки' => '0',
                    ],
                ],
                'ЦеннаяПосылка_Difficult'     => [],
                'ЦеннаяАвиаПосылка_Difficult' => [],
                'EMS_Difficult'               => [],
                'Магистраль'                  => [
                    'ДоставкаСтандарт' => '2',
                    'ДоставкаАвиа'     => '4',
                    'ДоставкаКласс1'   => '2',
                    'Описание'         => mb_strtoupper($from->getCaption()) . ' => ' . mb_strtoupper($to->getCaption()),
                    'Расстояние'       => '241',
                ],
                'Откуда'                      => [
                    'Индекс'            => $from->getPostalCode(),
                    'Название'          => mb_strtoupper($from->getCaption()),
                    'Адрес'             => 'Москва, Мясницкая ул, 26',
                    'Телефон'           => 'Прочее	(800) 2005888',
                    'МестоположениеEMS' => mb_strtoupper($from->getCaption()),
                    'ЦентрРегиона'      => mb_strtoupper($from->getCaption()),
                ],
                'Куда'                        => [
                    'Индекс'            => $to->getPostalCode(),
                    'Название'          => mb_strtoupper($to->getCaption()),
                    'Адрес'             => 'Рязанская обл, Рязань, Почтовая ул, 49',
                    'Телефон'           => 'Начальник ОПС	(4912) 255305 Начальник ОПС	(4912) 284207',
                    'МестоположениеEMS' => mb_strtoupper($to->getCaption()),
                    'ЦентрРегиона'      => mb_strtoupper($to->getCaption()),
                ],
                'Вес'                         => $order->getOverallItemsWeight(),
                'ОценкаВложения'              => '0',
                'Дата'                        => '11.08.2017',
                'ДатаСверкиТарифов'           => '05.09.2016',
                'Status'                      => 'OK',
                'API'                         => '1.1',
                '_request'                    => [
                    'f' => $from->getCaption(),
                    't' => $to->getPostalCode(),
                    'w' => $order->getOverallItemsWeight(),
                    'o' => 'JSON',
                ],
                '_server'                     => [
                    'SERVER_ADDR'          => '88.198.243.244',
                    'REMOTE_ADDR'          => '80.87.202.106',
                    'HTTP_HOST'            => 'api.postcalc.ru',
                    'HTTP_USER_AGENT'      => '5.6.24-0+deb8u1',
                    'HTTP_ACCEPT_ENCODING' => 'gzip',
                ],
            ];
            if ($limits !== null) {
                $data['Ограничения'] = [
                    'Куда' => $limits,
                ];
            }
            if ($noCalculation === true) {
                $data['Отправления']['ЦеннаяПосылка']['НетРасчета'] = 'Да';
            }
            return $data;
        });
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

    /**
     * @param int $orderWeight
     * @param float $priceCoefficient
     * @param float $postcalcPrice
     * @param float $returnPrice
     * @param float $returnAviaPrice
     * @param int $deliveryInterval
     * @dataProvider getCalculatorData
     */
    public function test_calculate_with_fullBlock(
        $orderWeight,
        $priceCoefficient,
        $postcalcPrice,
        $returnPrice,
        $returnAviaPrice,
        $deliveryInterval
    ) {
        $orderPrice = 1000;

        /** @var APIConnectorInterface $connector */
        $connector = $this->createConnector($postcalcPrice, $deliveryInterval, [
            'Действует' => 'ЗАПРЕТ',
        ]);

        /** @var CityInterface $from */
        $from = $this->createCity('Moscow', '101000');
        /** @var CityInterface $to */
        $to = $this->createCity('Ryazan', '390000');

        /** @var OrderInterface $order */
        $order = $this->createOrder($orderWeight, $orderPrice);

        $deliveryMethod = $this->getMockBuilder(DeliveryMethodInterface::class)->getMock();
        $deliveryMethod->method('getPriceCoefficient')->willReturn($priceCoefficient);

        $strategy = new Strategy($connector, $deliveryMethod, 'ЦеннаяПосылка');

        $this->assertNull($strategy->calculate($order, $from, $to));
    }

    /**
     * @param int $orderWeight
     * @param float $priceCoefficient
     * @param float $postcalcPrice
     * @param float $returnPrice
     * @param float $returnAviaPrice
     * @param int $deliveryInterval
     * @dataProvider getCalculatorData
     */
    public function test_calculate_with_aviaOnly(
        $orderWeight,
        $priceCoefficient,
        $postcalcPrice,
        $returnPrice,
        $returnAviaPrice,
        $deliveryInterval
    ) {
        $orderPrice = 1000;

        /** @var APIConnectorInterface $connector */
        $connector = $this->createConnector($postcalcPrice, $deliveryInterval, [
            'Действует' => 'АВИА',
        ]);

        /** @var CityInterface $from */
        $from = $this->createCity('Moscow', '101000');
        /** @var CityInterface $to */
        $to = $this->createCity('Ryazan', '390000');

        /** @var OrderInterface $order */
        $order = $this->createOrder($orderWeight, $orderPrice);

        $deliveryMethod = $this->getMockBuilder(DeliveryMethodInterface::class)->getMock();
        $deliveryMethod->method('getPriceCoefficient')->willReturn($priceCoefficient);

        $strategy = new Strategy($connector, $deliveryMethod, 'ЦеннаяПосылка');

        $this->assertEquals(
            new StrategyCalculation((float)$returnAviaPrice, new \DateInterval('P' . ($deliveryInterval + 2) . 'D'),
                $deliveryMethod),
            $strategy->calculate($order, $from, $to)
        );
    }

    /**
     * @param int $orderWeight
     * @param float $priceCoefficient
     * @param float $postcalcPrice
     * @param float $returnPrice
     * @param float $returnAviaPrice
     * @param int $deliveryInterval
     * @dataProvider getCalculatorData
     */
    public function test_calculate_with_not_existent_method(
        $orderWeight,
        $priceCoefficient,
        $postcalcPrice,
        $returnPrice,
        $returnAviaPrice,
        $deliveryInterval
    ) {
        $orderPrice = 1000;

        /** @var APIConnectorInterface $connector */
        $connector = $this->createConnector($postcalcPrice, $deliveryInterval);

        /** @var CityInterface $from */
        $from = $this->createCity('Moscow', '101000');
        /** @var CityInterface $to */
        $to = $this->createCity('Ryazan', '390000');

        /** @var OrderInterface $order */
        $order = $this->createOrder($orderWeight, $orderPrice);

        $deliveryMethod = $this->getMockBuilder(DeliveryMethodInterface::class)->getMock();
        $deliveryMethod->method('getPriceCoefficient')->willReturn($priceCoefficient);

        $strategy = new Strategy($connector, $deliveryMethod, '_test_method_which_not_exists_in_API_response_');

        $this->assertNull($strategy->calculate($order, $from, $to));
    }

    public function test_calculate_with_no_response_from_api()
    {
        $connector = $this->getMockBuilder(APIConnectorInterface::class)->getMock();
        $connector->method('getData')->willReturn(false);

        /** @var CityInterface $from */
        $from = $this->createCity('Moscow', '101000');
        /** @var CityInterface $to */
        $to = $this->createCity('Ryazan', '390000');

        /** @var OrderInterface $order */
        $order = $this->createOrder(0, 0);

        $deliveryMethod = $this->getMockBuilder(DeliveryMethodInterface::class)->getMock();
        $deliveryMethod->method('getPriceCoefficient')->willReturn(0);

        $strategy = new Strategy($connector, $deliveryMethod, '_test_method_which_not_exists_in_API_response_');

        $this->assertNull($strategy->calculate($order, $from, $to));
    }

    /**
     * @return array [
     *  int $orderWeight,
     *  float $priceCoefficient,
     *  float $postcalcPrice,
     *  float $returnPrice,
     *  float $returnAviaPrice
     *  int $deliveryInterval
     * ]
     */
    public function getCalculatorData()
    {
        return [
            [0, 1, 100, 51, 77, 1], // weight = 0
            [1000, 1, 100, 50, 75, 2], // $pricePerKg >= 0 && $pricePerKg <= 190 => *= 0.5
            [1000, 1, 200, 120, 210, 3], // $pricePerKg >= 191 && $pricePerKg <= 280 => *= 0.6
            [1000, 1, 400, 280, 510, 4], // $pricePerKg >= 281 && $pricePerKg <= 500 => *= 0.7
            [1000, 1, 600, 510, 765, 5], // $pricePerKg >= 501 => *= 0.85
            [499, 1, 100, 36, 63, 6], // $order->getOverallItemsWeight() < 500
            [1000, 0.5, 100, 25, 38, 2], // $pricePerKg >= 0 && $pricePerKg <= 190 => *= 0.5 && PriceCoefficient = 0.5
        ];
    }
}
