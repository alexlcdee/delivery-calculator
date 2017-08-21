<?php

namespace DeliveryCalculation\Interfaces;


interface OrderInterface
{
    public function getOverallItemsWeight();

    public function getOverallItemsPrice();

    public function getPlacesQuantity();

}