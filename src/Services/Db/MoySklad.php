<?php


namespace Parser\Postal\Services\Db;

class MoySklad
{

    private $login;
    private $password;
    public $tableId;

    public function __construct($tableId)
    {
        $this->login = config('parser-postal.moysklad-email');
        $this->password = config('parser-postal.parser-postal.moysklad-email');
        $this->tableId = $tableId;
    }

    public function getData()
    {

        $ch = curl_init($this->setUrl($this->tableId));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$this->login:$this->password");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

        $response = curl_exec($ch);
        curl_close($ch);

        $array = json_decode($response, true);

        return !empty($array['rows']) && is_array($array['rows']) ? $array['rows'] : [];


    }
    public function reform()
    {
        $reformed = [];
        $data = $this->getData();
        if(!empty($data)){
            foreach ($data as $row){
                $reformed[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'description' => key_exists('description', $row) ? $row['description'] : '',
                    'code' => key_exists('code', $row) ? $row['code'] : '',
                    'externalCode' => key_exists('externalCode', $row) ? $row['externalCode'] : '',
                ];
            }
        }
        return $reformed;
    }

    public function updateItem($idItem, $data = '{}')
    {

        $ch = curl_init($this->setUrl($this->tableId.'/'. $idItem));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$this->login:$this->password");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json')
        );

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response = curl_exec($ch);

        curl_close($ch);



    }

    public function createNewItems($data = '{}')
    {
        $ch = curl_init($this->setUrl($this->tableId));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$this->login:$this->password");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json')
        );

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response = curl_exec($ch);

        curl_close($ch);
        $responseToArray = json_decode($response, true);
        $arrayIds = $this->getArrayWithMoySkladIds($responseToArray);
        return $arrayIds;

    }

    public function getArrayWithMoySkladIds($response){

        $data = [];
        foreach ($response as $items) {
            $data[$items['externalCode']] = [
                'mySkladId' => $items['id']
            ];
        }
        return $data;
    }

    public function setUrl($tableId)
    {
        return 'https://online.moysklad.ru/api/remap/1.2/entity/customentity/'.$tableId;
    }
}
