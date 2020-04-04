<?php


namespace Parser\Postal\Services\PostalServices;

use Illuminate\Support\Facades\Log;
use Parser\Postal\Services\Db\Firebase;
use Parser\Postal\Services\Db\MoySklad;
use Parser\Postal\Services\Interfaces\ParserInterface;

abstract class AbstractService implements ParserInterface
{
    /** @var Firebase $fireBase */
    public $fireBase;
    /** @var string $tableName */
    public $tableName;
    /** @var MoySklad $mySklad */
    public $mySklad;

    /**
     * @return mixed
     */
    abstract public function initClient();

    /**
     * @param $data
     * @return mixed
     */
    abstract public function getRequest($data);

    /**
     * @return array
     */
    public function getData()
    {
        try {
            $ch = $this->initClient();
            $outputData = $this->getRequest($ch);
            return $this->reform($outputData);
        } catch (\Exception $e) {
            $this->logger('Error with connect to API ' . $this->tableName);
            $this->logger('/** ' . $e->getMessage() . '**/');
        }
    }

    /**
     * @param $outputData
     * @return array
     */
    abstract public function reform($outputData);

    public function checkChanges($newApiData)
    {
        $changedData = [];
        $changedBranches = [];
        $newBranches = [];
        $tableName = $this->tableName;

        $fireBaseStatus = $this->fireBase->getData($tableName . '/updated_at/') === 'null' ? 0 : 1;
        if ($fireBaseStatus) {
            $fireBaseData = $this->fireBase->getData($tableName . '/data/');
            $arrayFireBase = json_decode($fireBaseData, true);

            if (is_array($arrayFireBase) && !empty($arrayFireBase)) {
                $newBranches = array_diff_key($newApiData['data'], $arrayFireBase);
                foreach ($arrayFireBase as $key => $branch) {
                    $mySkladId = $branch['mySkladId'];
                    unset($branch['mySkladId']);
                    if (array_key_exists($key, $newApiData['data'])) {
                        $diff = (array_diff_assoc($newApiData['data'][$key], $branch));
                    }
                    if (!empty($diff)) {
                        $diff = array_merge($diff, ['mySkladId' => $mySkladId]);
                        $changedBranches[$key] = $diff;

                    }
                }
            }
            $changedData['changedBranches'] = $changedBranches;
            $changedData['newBranches'] = $newBranches;
        } else {
            $changedData['newData'] = $newApiData;
        }
        return $changedData;
    }

    /**
     * @return mixed|void
     */
    public function saveData()
    {
        $newApiData = $this->getData();
        $data = $this->checkChanges($newApiData);
        if (key_exists('newData', $data)) {
            $countItems = count($data['newData']['data']);
            $json = $this->jsonEncode($data['newData']);
            $this->saveToFirebase($this->tableName, $json, $countItems);
            $this->saveToMySklad($data['newData']['data']);
            $this->saveToFile($json, $this->tableName);

        } else {
            if (!empty($data['changedBranches']) && is_array($data['changedBranches'])) {
                $this->saveToFile(
                    $this->jsonEncode($data['changedBranches']),
                    'updates_branches_' . $this->tableName
                );
                $countItems = count($data['changedBranches']);
                foreach ($data['changedBranches'] as $id => $item) {
                    $jsonItem = $this->jsonEncode($item);
                    $updated = $this->fireBase->updateData($this->tableName . '/data/' . $id, $jsonItem);
                    $this->updateIntoMySklad($updated);
                }
                $this->logger('Updated ' . $countItems . ' items in Firebase ' . $this->tableName);
            }
            if (!empty($data['newBranches']) && is_array($data['newBranches'])) {
                $countItems = count($data['newBranches']);
                $json = $this->jsonEncode($data['newBranches']);
                $this->fireBase->updateData($this->tableName . '/data/', $json);

                $this->logger('Created ' . $countItems . ' items in Firebase ' . $this->tableName);
                $this->saveToMySklad($data['newBranches']);


                $this->saveToFile(
                    $this->jsonEncode($data['newBranches']),
                    'new_branches_' . $this->tableName
                );
            }
            if (empty($data['changedBranches']) && empty($data['newBranches'])) {
                $this->logger('There aren`t new items for updating or creating for ' . $this->tableName);
            }
        }
    }

    /**
     * @param $tableName
     * @param $data
     * @param $countItems
     */
    public function saveToFirebase($tableName, $data, $countItems)
    {
        $this->fireBase->saveData($tableName, $data);
        $this->logger('Created ' . $countItems . ' items in Firebase ' . $tableName);
    }

    /**
     * @param $data
     * @param $tableName
     */
    public function saveToFile($data, $tableName)
    {
//        $fileName = Config::DATA_FILES . str_replace(" ", "_", date('Y-m-d H:i:s')) . "_" . $tableName . ".json";
//        file_put_contents($fileName, $data, true);
    }

    public function preparingData($data)
    {
        $forMySklad = [];
        foreach ($data as $key => $item) {
            $forMySklad[] =
                [
                    'name' => $item['city'],
                    'description' => $item['address'],
                    'code' => $item['branch'],
                    'externalCode' => (string)$key,
                ];
        }
        return $this->jsonEncode($forMySklad);
    }

    public function preparingForUpdate($data)
    {
        $forMySklad =
            [
                'name' => key_exists('city', $data) ? $data['city'] : '',
                'description' => key_exists('address', $data) ? $data['address'] : '',
                'code' => key_exists('branch', $data) ? $data['branch'] : ''
            ];
        $forMySklad = array_diff($forMySklad, ['']);
        return $this->jsonEncode($forMySklad);
    }

    public function saveToMySklad($data)
    {
        $forMySklad = $this->preparingData($data);
        $arrayIds = $this->mySklad->createNewItems($forMySklad);
        $this->logger('Created ' . count($arrayIds) . ' items in MySklad ' . $this->tableName);
        foreach ($arrayIds as $fId => $mySkladitem) {
            $jsonItemF = $this->jsonEncode($mySkladitem);
            $this->fireBase->updateData($this->tableName . '/data/' . $fId, $jsonItemF);
        }
    }

    public function updateIntoMySklad($data)
    {
        $array = json_decode($data, true);
        $forUpdate = $this->preparingForUpdate($array);
        $this->mySklad->updateItem($array['mySkladId'], $forUpdate);
    }

    public function jsonEncode($data)
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function logger($text)
    {
        Log::info($text);
//        $file = fopen(Config::LOG_TXT, "a");
//        fwrite($file, '[' . date('Y-m-d H:i:s') . '] ' . $text . PHP_EOL);
    }
}
