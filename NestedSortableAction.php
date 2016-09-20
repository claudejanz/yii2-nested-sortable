<?php

namespace claudejanz\yii2nestedSortable;

use Yii;
use yii\base\Action;

class NestedSortableAction extends Action {

    public $modelclass;
    public $scenario = '';

    /**
     * column name of sorting value
     * @var string 
     */
    public $orderBy = 'weight';

    /**
     * value of root elements in sorting value 
     * depends on db settings 0 or null
     */
    public $rootValue = null;

    /**
     * column name of parent id 
     * @var string 
     */
    public $parentId = 'parent_id';

    public function run() {
        // Get the JSON string
        $jsonstring = $_GET['jsonstring'];

        // Decode it into an array
        $jsonDecoded = json_decode($jsonstring, true, 64);

        // Run the function above
        $readbleArray = $this->parseJsonArray($jsonDecoded);

        // Loop through the "readable" array and save changes to DB
        foreach ($readbleArray as $key => $value) {

            // $value should always be an array, but we do a check
            if (is_array($value)) {

                $modelclass = $this->modelclass;
                $pks = (array)$modelclass::primaryKey();
                $model = $modelclass::find()->where([
                            join(',',$pks) => $value['id'],
                        ])->one();
                if ($this->scenario) {
                    $model->setScenario($this->scenario);
                }

                $model->{$this->orderBy} = $key;
                $model->{$this->parentId} = $value['parentID'];
                $model->save(false);
            }
        }

        // Echo status message for the update
        echo Yii::t('app', "The list was updated ") . date("y-m-d H:i:s") . "!";
    }

    public function parseJsonArray($jsonArray, $parentID = null) {
        $return = array();
        foreach ($jsonArray as $subArray) {
            $returnSubSubArray = array();
            if (isset($subArray['children'])) {
                $returnSubSubArray = $this->parseJsonArray($subArray['children'], $subArray['id']);
            }
            $return[] = array('id' => $subArray['id'], 'parentID' => $parentID);
            $return = array_merge($return, $returnSubSubArray);
        }

        return $return;
    }

}
