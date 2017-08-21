<?php

namespace DeliveryCalculation\CDEK;


use DeliveryCalculation\Interfaces\CityInterface;
use DeliveryCalculation\Interfaces\OrderInterface;

class CDEKLibraryAPIConnector implements APIConnectorInterface
{
    private $sdekInstance;

    public function __construct($login, $password)
    {
        $this->sdekInstance = new CalculatePriceDeliveryCdek();
        $this->sdekInstance->setAuth($login, $password);
    }

    public function getData(CityInterface $from, CityInterface $to, OrderInterface $order)
    {
        $instance = clone $this->sdekInstance;
        $instance = $this->setDateExecute($instance);
        $instance = $this->setCities($instance, $from, $to);
        $instance = $this->setTariffPriorities($instance, [136, 234]);
        $instance = $this->setPlaces($instance, $order);

        if ($instance->calculate()) {
            return $instance->getResult();
        } else {
            return false;
        }
    }

    private function setDateExecute(CalculatePriceDeliveryCdek $instance)
    {
        $instance = clone $instance;
        $now = new \DateTime();
        $dateExecute = $now->add(new \DateInterval('P1D'));
        $instance->setDateExecute($dateExecute->format('Y-m-d'));
        return $instance;
    }

    private function setCities(CalculatePriceDeliveryCdek $instance, CityInterface $from, CityInterface $to)
    {
        $instance = clone $instance;
        $instance->setSenderCityId($from->getPostalCode(), true);
        $instance->setReceiverCityId($to->getPostalCode(), true);
        return $instance;
    }

    private function setTariffPriorities(CalculatePriceDeliveryCdek $instance, array $priorities)
    {
        $instance = clone $instance;
        foreach ($priorities as $priority => $tariff) {
            $instance->addTariffPriority($tariff, $priority + 1);
        }
        return $instance;
    }

    private function setPlaces(CalculatePriceDeliveryCdek $instance, OrderInterface $order)
    {
        $instance = clone $instance;
        $placeWeight = ceil(($order->getOverallItemsWeight() + 250) / $order->getPlacesQuantity()) / 1000;
        list($length, $width, $height) = $this->calculateSizes($placeWeight);
        for ($i = 0; $i < $order->getPlacesQuantity(); $i++) {
            $instance->addGoodsItemBySize($placeWeight, $length, $width, $height);
        }
        return $instance;
    }

    private function calculateSizes($placeWeight)
    {
        $placeWeight = ceil($placeWeight);
        if ($placeWeight <= 1) {
            return [25, 18, 4];
        } elseif ($placeWeight > 1 && $placeWeight <= 2) {
            return [35, 25, 5];
        } elseif ($placeWeight > 2 && $placeWeight <= 4) {
            return [38, 28, 12];
        } elseif ($placeWeight > 4 && $placeWeight <= 7) {
            return [35, 25, 18];
        } else {
            return [40, 30, 20];
        }
    }
}