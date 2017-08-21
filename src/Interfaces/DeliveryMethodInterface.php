<?php

namespace DeliveryCalculation\Interfaces;


interface DeliveryMethodInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getCaption();

    /**
     * @return bool
     */
    public function isActive();

    /**
     * @return bool
     */
    public function isDeleted();

    /**
     * @return float
     */
    public function getPriceCoefficient();

    /**
     * @return Threshold[]
     */
    public function getThresholds();

    /**
     * @return CityInterface[]
     */
    public function getCities();

    /**
     * @return CountryInterface
     */
    public function getCountries();
}