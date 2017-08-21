<?php

namespace DeliveryCalculation\CDEK;


use DeliveryCalculation\Interfaces\CityInterface;
use DeliveryCalculation\Interfaces\OrderInterface;

interface APIConnectorInterface
{
    public function getData(CityInterface $from, CityInterface $to, OrderInterface $order);
}