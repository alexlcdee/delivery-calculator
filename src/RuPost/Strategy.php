<?php

namespace DeliveryCalculation\RuPost;


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
     * @var string
     */
    private $preferredMethod;
    /**
     * @var DeliveryMethodInterface
     */
    private $deliveryMethod;

    /**
     * Strategy constructor.
     * @param APIConnectorInterface $connector
     * @param DeliveryMethodInterface $deliveryMethod
     * @param string $preferredMethod
     */
    public function __construct(
        APIConnectorInterface $connector,
        DeliveryMethodInterface $deliveryMethod,
        $preferredMethod
    ) {
        $this->connector = $connector;
        $this->preferredMethod = $preferredMethod;
        $this->deliveryMethod = $deliveryMethod;
    }

    /**
     * @param OrderInterface $order
     * @param CityInterface $from
     * @param CityInterface $to
     * @return StrategyCalculationInterface|null
     */
    public function calculate(OrderInterface $order, CityInterface $from, CityInterface $to)
    {
        $data = $this->connector->getData($from, $to, $order);

        if ($data === false) {
            return null;
        }

        if (!isset($data['Отправления'])) {
            return null;
        }

        list($price, $time) = $this->selectSuitablePrice($data);

        if ($price === null && $time === null) {
            return null;
        }

        $price = $this->dividePrice($price, $order);

        $time += 2;

        return new StrategyCalculation(
            ceil($price * $this->deliveryMethod->getPriceCoefficient()),
            new \DateInterval("P{$time}D"),
            $this->deliveryMethod
        );
    }

    private function selectSuitablePrice($data)
    {
        $deliveryMethodsData = $data['Отправления'];
        list($aviaOnly, $fullBlock) = $this->checkLimits($data);

        if ($fullBlock) {
            return [null, null];
        }

        if (isset($deliveryMethodsData[$this->preferredMethod]) && !isset($deliveryMethodsData[$this->preferredMethod]['НетРасчета'])) {
            $time = $deliveryMethodsData[$this->preferredMethod]['СрокДоставки'];
            $deliveryPrice = $deliveryMethodsData[$this->preferredMethod]['Доставка'];
            if ($aviaOnly) {
                /*
                 * С 20 января 2017 года исключены расчеты авиапосылок и авиабандеролей,
                 * так как Почта России более их не рассчитывает.
                 *
                 * Вместо них применяется наценка 50% на посылки и бандероли,
                 * если отделение связи отправителя и/или получателя закрыто для наземной доставки (постоянно или временно).
                 */
                return [ceil($deliveryPrice + $deliveryPrice * 0.5), $time];
            }
            return [ceil($deliveryPrice), $time];
        }

        return [null, null];
    }

    private function checkLimits($data)
    {
        $aviaOnly = false;
        $fullBlock = false;
        if (isset($data['Ограничения']) && $data['Ограничения']['Куда']) {
            $limit = $data['Ограничения']['Куда'];
            $limitApplied = $limit['Действует'] != false;

            switch (mb_strtoupper($limit['Действует'])) {
                case 'АВИА':
                    $aviaOnly = $limitApplied;
                    break;
                case 'ЗАПРЕТ':
                    $fullBlock = $limitApplied;
                    break;
            }
        }
        return [$aviaOnly, $fullBlock];
    }

    private function dividePrice($inputPrice, OrderInterface $order)
    {
        $weight = $order->getOverallItemsWeight() / 1000;
        if ($weight === 0.0 || $weight === 0) {
            $weight = 0.001;
        }
        /*
         * 14.06.2017 Расчет почтовой доставки
         */
//        $pricePerKg = $order->getOverallItemsPrice() / $weight;
        $pricePerKg = $inputPrice / $weight;

        if ($pricePerKg >= 0 && $pricePerKg <= 190) {
            $inputPrice *= 0.5;
        } elseif ($pricePerKg >= 191 && $pricePerKg <= 280) {
            $inputPrice *= 0.6;
        } elseif ($pricePerKg >= 281 && $pricePerKg <= 500) {
            $inputPrice *= 0.7;
        } elseif ($pricePerKg >= 501) {
            $inputPrice *= 0.85;
        }

        /*
         * 14.06.2017 Расчет почтовой доставки
         * 23.03.2017 Корректировка тарифов доставки
         */
        if ($order->getOverallItemsWeight() < 500) {
            $inputPrice *= 0.6;
        }
        return $inputPrice;
    }
}