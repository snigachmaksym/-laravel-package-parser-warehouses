<?php

namespace Parser\Postal\Services\PostalServices;

use Parser\Postal\Services\Db\Firebase;
use Parser\Postal\Services\Db\MoySklad;
use Parser\Postal\Services\Interfaces\ParserInterface;

class Delivery extends AbstractService implements ParserInterface
{
    public $fireBase;
    public $mySklad;
    public $tableName;
    /**
     * Delivery constructor.
     */
    public function __construct()
    {
        $this->fireBase = new Firebase();
        $this->mySklad = new MoySklad(config('parser-postal.moysklad-delivery-table-id'));
        $this->tableName = config('parser-postal.delivery-table-name');
    }

    /**
     * @return false|resource
     */
    public function initClient()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, config('parser-postal.delivery-url') . config('parser-postal.delivery-param'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        return $ch;
    }

    /**
     * @param $ch
     * @return mixed
     */
    public function getRequest($ch)
    {
        $output = curl_exec($ch);
        curl_close($ch);
        return json_decode($output, true);
    }

    /**
     * @param $outputData
     * @return array
     */
    public function reform($outputData)
    {
        $data = [
            'data' => [],
            'updated_at' => date('Y-m-d H:i:s')

        ];
        if ($outputData['status'] === true) {
            foreach ($outputData['data'] as $item) {
                $data['data'][$item['id']] = [
                    'address' => $item['RcName'],
                    'city' => $item['CityName'] . ' - ' . $item['address'],
                    'branch' => $item['RcName'],
                ];
            }

        }else{
            $this->logger('API data are empty for '. $this->tableName);
        }
        return $data;
    }


}
