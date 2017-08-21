<?php

namespace DeliveryCalculation\RuPost;


use DeliveryCalculation\Interfaces\CityInterface;
use DeliveryCalculation\Interfaces\OrderInterface;

class APIConnector implements APIConnectorInterface
{
    /**
     * @var
     */
    private $serverName;
    /**
     * @var
     */
    private $serverContactEmail;
    /**
     * @var
     */
    private $apiUrl;

    /**
     * CDEKLibraryAPIConnector constructor.
     * @param string $serverName
     * @param string $serverContactEmail
     * @param string $apiUrl
     */
    public function __construct($serverName, $serverContactEmail, $apiUrl)
    {
        $this->serverName = $serverName;
        $this->serverContactEmail = $serverContactEmail;
        $this->apiUrl = $apiUrl;
    }

    /**
     * @param CityInterface $from
     * @param CityInterface $to
     * @param OrderInterface $order
     * @return array|bool
     */
    public function getData(CityInterface $from, CityInterface $to, OrderInterface $order)
    {
        $query = [
            'st' => $this->serverName,
            'ml' => $this->serverContactEmail,
            'f'  => $from->getCaption(),
            't'  => $to->getPostalCode(),
            'w'  => $order->getOverallItemsWeight(),
//            'v'  => $order->getOverallItemsPrice(),
            'o'  => 'JSON',
        ];

        return $this->fetchData($query);
    }

    private function fetchData($query)
    {
        $context = stream_context_create([
            'http' => [
                'header'     => 'Accept-Encoding: gzip',
                'timeout'    => 5,
                'user_agent' => phpversion(),
            ],
        ]);
        if (!($response = @file_get_contents($this->apiUrl . '?' . http_build_query($query), false, $context))) {
            return false;
        }

        if (substr($response, 0, 3) != "\x1f\x8b\x08") {
            return false;
        }

        if (($data = json_decode(gzinflate(substr($response, 10, -8)), true)) === null) {
            return false;
        }
        return $data;
    }
}