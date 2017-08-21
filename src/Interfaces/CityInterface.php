<?php

namespace DeliveryCalculation\Interfaces;


interface CityInterface
{
    /**
     * @return string
     */
    public function getPostalCode();

    /**
     * @return CountryInterface
     */
    public function getCountry();

    /**
     * @return string
     */
    public function getCaption();
}