<?php

namespace Parser\Postal\Services\Db;

class Firebase
{
    public function getData($tableName)
    {
        $ch = curl_init($this->setUrl($tableName));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

        $response = curl_exec($ch);


        return $response;

    }

    public function saveData($tableName, $data = '{ "new": "Test"}')
    {

        $ch = curl_init($this->setUrl($tableName));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);
        curl_close($ch);
    }

    public function updateData($tableName, $data = '{ "new": "Test"}')
    {

        $ch = curl_init($this->setUrl($tableName));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    public function putMySkladId($data)
    {

        foreach ($data as $item){

            $jsonItem = json_encode($item, JSON_UNESCAPED_UNICODE);
            $this->updateData($this->tableName . '/data/' . $item['fireBaseId'], $jsonItem);
        }
    }

    public function setUrl($tableName)
    {
        return 'https://' . config('parser-postal.db-name') . '.firebaseio.com/'.$tableName.'.json?auth=' . config('parser-postal.firebase-auth');
    }
}
