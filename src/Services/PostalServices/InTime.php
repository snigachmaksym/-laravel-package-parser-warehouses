<?php

namespace Parser\Postal\Services\PostalServices;

use Parser\Postal\Services\Interfaces\ParserInterface;
use Parser\Postal\Services\Db\Firebase;
use Parser\Postal\Services\Db\MoySklad;
use SoapClient;

class InTime extends AbstractService implements ParserInterface
{
    public $fireBase;
    public $tableName;
    public $mySklad;
    /**
     * InTime constructor.
     */
    public function __construct()
    {
        $this->tableName = config('parser-postal.intime-table-name');
        $this->fireBase = new Firebase();
        $this->mySklad = new MoySklad(config('parser-postal.moysklad-intime-table-id'));
    }

    /**
     * @return SoapClient
     */
    public function initClient()
    {
        try {
            return new SoapClient(config('parser-postal.intime-url'), [
                "trace" => 1,
                "exceptions" => 0]);
        } catch (\SoapFault $e) {
            $this->logger('Error with connect to API ' . $this->tableName);
        }
    }

    /**
     * @param $soapClient
     * @return mixed
     */
    public function getRequest($soapClient)
    {
        $service_param = [
            "api_key" => config('parser-postal.intime-api-key'),
        ];
        /** @var  SoapClient $soapClient */
        return $soapClient->__soapCall('get_branch_by_id', [$service_param]);

    }

    public function reform($outputData)
    {
        $data = [
            'data' => [],
            'updated_at' => date('Y-m-d H:i:s')

        ];
        if (!empty($outputData->Entry_get_branch_by_id)) {
            $test = (array) json_decode(json_encode($outputData->Entry_get_branch_by_id, JSON_UNESCAPED_UNICODE), true);
            foreach ($test as $item) {
                $data['data'][$item['id']] = [
                    'address' => $item['address_ua'],
                    'city' => $item['name_ua'],
                    'branch' => $item['number'],
                ];
            }
        }else{
            $this->logger('API data are empty for '. $this->tableName);
        }
        return $data;
    }

}
