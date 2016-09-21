<?php

namespace claudejanz\yii2nestedSortable;

use Yii;
use yii\base\Action;
use yii\web\Response;

class NestedSortableAction extends Action
{

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
        $posts = Yii::$app->request->post('item');
        $i=0;
        foreach ($posts as $key => $value) {
            $modelclass = $this->modelclass;
            $pks = (array) $modelclass::primaryKey();
            $model = $modelclass::find()->where([
                        join(',', $pks) => $key,
                    ])->one();
            if ($this->scenario) {
                $model->setScenario($this->scenario);
            }

            $model->{$this->orderBy} = $i;
            $model->{$this->parentId} = ($value!=0)?$value:null;
            $model->save(false);
            $i++;
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        // Echo status message for the update
        return Yii::t('app', "The hierarchy was updated on {datetime} !",['datetime'=>Yii::$app->formatter->asDatetime('Now')]);
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
