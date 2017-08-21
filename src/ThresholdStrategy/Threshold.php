<?php

namespace DeliveryCalculation\ThresholdStrategy;


class Threshold implements \JsonSerializable
{
    /**
     * @var
     */
    private $threshold;
    /**
     * @var
     */
    private $value;

    public function __construct($threshold, $value)
    {
        $this->threshold = $threshold;
        $this->value = $value;
    }

    public function isEqualsTo(Threshold $threshold)
    {
        return $this->getValue() === $threshold->getValue() && $this->getThreshold() === $threshold->getThreshold();
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return (float)$this->value;
    }

    /**
     * @return mixed
     */
    public function getThreshold()
    {
        return (float)$this->threshold;
    }

    public function greaterThan($threshold)
    {
        return $this->getThreshold() > (float)$threshold;
    }

    public function lessThan($threshold)
    {
        return $this->getThreshold() < (float)$threshold;
    }

    public function greaterThanEq($threshold)
    {
        return $this->getThreshold() >= (float)$threshold;
    }

    public function lessThanEq($threshold)
    {
        return $this->getThreshold() <= (float)$threshold;
    }

    public function equalTo($threshold)
    {
        return $this->getThreshold() === (float)$threshold;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {
        return ['threshold' => $this->getThreshold(), 'value' => $this->getValue()];
    }
}