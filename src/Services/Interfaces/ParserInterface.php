<?php

namespace Parser\Postal\Services\Interfaces;

interface ParserInterface
{
    /**
     * @return mixed
     */
    public function initClient();

    /**
     * @param $data
     * @return mixed
     */
    public function getRequest($data);

    /**
     * @return mixed
     */
    public function getData();

    /**
     * @param $outputData
     * @return mixed
     */
    public function reform($outputData);

    /**
     * @param $newApiData
     * @return mixed
     */
    public function checkChanges($newApiData);
    /**
     * @return mixed
     */
    public function saveData();
}
