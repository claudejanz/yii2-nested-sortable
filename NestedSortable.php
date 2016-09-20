<?php

/**
 * @inheritdoc
 */

namespace claudejanz\yii2nestedSortable;

use app\models\Page;
use app\models\search\PageSearch;
use Yii;
use yii\base\Widget;
use yii\helpers\Json;

/**
 * @inheritdoc
 */
class NestedSortable extends Widget
{

    /**
     * @see NestedSortable
     * @var PageSearch
     */
    public $searchModel;

    /**
     * @see NestedSortable
     * @var string
     */
    public $group = 0;

    /**
     * @see NestedSortable
     * @var string
     */
    public $maxDepth = 5;

    /**
     * @see NestedSortable
     * @var string
     */
    public $threshold = 20;

    /**
     * @see NestedSortable
     * @var string
     */
    public $url = './save-sortable';

    /**
     * @see NestedSortable
     * @var string
     */
    public $pluginOptions = [];

    /**
     * @see NestedSortable
     * @var string
     */
    public $model = null;

    /**
     * @see NestedSortable
     * @var string
     */
    public $expand = false;

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

    /**
     * @see NestedSortable
     * @var string
     */
    public $expandMenu = '
		<menu id="nestable-menu">
			<button type="button" data-action="expand-all">Expand All</button>
			<button type="button" data-action="collapse-all">Collapse All</button>
		</menu>';

    /**
     * Init extension default
     * @see NestedSortable
     */
    public function init()
    {
        parent::init();
        $this->registerAssets();
    }

    public function run()
    {

        $view = $this->getView();

        $view->registerJs("
			var urlSortable = '$this->url';
		", $view::POS_END);

        $view->registerJs("

		/* The output is ment to update the nestableMenu-output textarea
		* So this could probably be rewritten a bit to only run the menu_updatesort function onchange
		*/

		var updateOutput = function(e)
		{
			var list   = e.length ? e : $(e.target),
				output = list.data('output');
			if (window.JSON) {
                        $.pjax.reload($('#menu-" . $this->searchModel->root_menu . "'),{timeout:false});
				output.val(window.JSON.stringify(list.nestable('serialize')));//, null, 2));
				menu_updatesort(window.JSON.stringify(list.nestable('serialize')));
			} else {
				output.val('JSON browser support required for this demo.');
			}
		};

		", $view::POS_READY);

        $this->registerScript();
        return $this->renderNested();

    }

    public function registerScript()
    {
        $options = false;

        $view = $this->getView();

        foreach ($this->pluginOptions as $name => $value) {
            $options .= $name . ":" . Json::encode($value) . ",";
        }

        $view->registerJs("jQuery('#$this->id').nestable({" . $options . "}).on('change', updateOutput);", $view::POS_READY);
        $view->registerJs("updateOutput($('#$this->id').data('output', $('#$this->id-output')));", $view::POS_READY);

        if ($this->expand == true) {
            $view->registerJs("

				$('#nestable-menu').on('click', function(e)
				{
					var target = $(e.target),
						action = target.data('action');
					if (action === 'expand-all') {
						$('.dd').nestable('expandAll');
					}
					if (action === 'collapse-all') {
						$('.dd').nestable('collapseAll');
					}
				});

			", $view::POS_READY);
        }

    }

    public function showNested($parentID)
    {
        $modelclass = $this->model;

        $model = $modelclass::find()->where([
                    $this->parentId => $parentID
                ])->orderBy($this->orderBy);

        $nested = false;

        if ($model->count() > 0) {
            $nested .= "<ol class='dd-list'>";
            foreach ($model->all() as $row) {
                $nested .= "<li class='dd-item' data-id='{$row->id}'>";
                $nested .= "<div class='dd-handle'>{$row->title} : " . Page::getTypeOptions()[$row->type] . "</div>";
                $this->showNested($row->id);
                $nested .= "</li>";
            }
            $nested .= "</ol>";
        }

        return $nested;
    }

    public function renderNested()
    {
        $modelclass = $this->model;

        $model = $modelclass::find()->where([
                    $this->parentId => $this->rootValue
                ])->andWhere(['root_menu' => $this->searchModel->root_menu])->orderBy($this->orderBy);
        $nested = false;

        // Feedback div for update hierarchy to DB
        // IMPORTANT: This needs to be here! But you can remove the style
        $nested .= "<div id='sortDBfeedback' class='alert alert-success'></div>";

        if ($this->expand == true) {
            $nested .= $this->expandMenu;
        }

        $nested .= "<div class='cf nestable-lists'>";
        $nested .= "<div class='dd' id='$this->id'>";
        $nested .= "<ol class='dd-list'>";

        foreach ($model->all() as $row) {
            $nested .= "<li class='dd-item' data-id='{$row->id}'>";
            $nested .= "<div class='dd-handle'>{$row->title} : " . Page::getTypeOptions()[$row->type] . "</div>";
            $nested .=$this->showNested($row->id);
            $nested .= "</li>";
        }

        $nested .= "</ol>";
        $nested .= "</div>";
        $nested .= "</div>";

        return $nested;

        // Script output for debuug
        //echo "<textarea id='$this->id-output'></textarea>";
    }

    /**
     * Register assets from this extension and yours types
     * @see NestedSortable
     */
    public function registerAssets()
    {
        $this->view = Yii::$app->getView();
        NestedSortableAsset::register($this->view);
    }

}
