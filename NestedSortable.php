<?php

/**
 * @inheritdoc
 */

namespace claudejanz\yii2nestedSortable;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\jui\Sortable;
use yii\web\JsExpression;
use const PHP_EOL;

/**
 * @inheritdoc
 */
class NestedSortable extends Sortable
{

    public $contentAttribute = 'title';
    public $itemsAttribute = 'pages';
    public $url = [];
    public $handleOptions = [];
    public $clientOptions = [
        'forcePlaceholderSize' => true,
        'handle'               => 'div',
        'listType'             => 'ul',
        'helper'               => 'clone',
        'items'                => 'li',
        'opacity'              => .6,
        'placeholder'          => 'placeholder',
        'revert'               => 250,
        'tabSize'              => 25,
        'tolerance'            => 'pointer',
        'toleranceElement'     => '> div',
        'maxLevels'            => 4,
        'isTree'               => true,
        'expandOnHover'        => 700,
        'startCollapsed'       => false,
    ];

    public function run()
    {

        if (isset($this->options['tag'])) {
            $this->clientOptions['listType'] = ArrayHelper::remove($this->options, 'tag', 'ul');
        }
        if (isset($this->handleOptions['tag'])) {
            $this->clientOptions['handle'] = ArrayHelper::remove($this->handleOptions, 'tag', 'div');
        }
        if (isset($this->itemOptions['tag'])) {
            $this->clientOptions['items'] = ArrayHelper::remove($this->itemOptions, 'tag', 'li');
        }
        if (!isset($this->clientEvents['update'])) {
            $this->clientEvents['update'] = new JsExpression("function(){
                    $.ajax({
                        method: 'POST',
                        url: '" . Url::to($this->url) . "',
                        data: $('#" . $this->options['id'] . "').nestedSortable('serialize'),
                        dataType: 'json'
                    })
                    .done(function( msg ) {
                      alert( 'Data Saved: ' + msg );
                    });
					
				}");
        }
        $this->registerWidget('nestedSortable');
        echo $this->renderItemsR($this->items) . PHP_EOL;
    }

    /**
     * Renders sortable items as specified on [[items]].
     * @return string the rendering result.
     * @throws InvalidConfigException.
     */
    public function renderItemsR($models)
    {
        $items = [];
        $items[] = Html::beginTag($this->clientOptions['listType'], $this->options) . PHP_EOL;
        ArrayHelper::remove($this->options, 'id');
        foreach ($models as $model) {
            $content = Html::tag($this->clientOptions['handle'], $model->{$this->contentAttribute}, $this->handleOptions);
            if ($model->{$this->itemsAttribute}) {
                $content .= $this->renderItemsR($model->{$this->itemsAttribute});
            }
            $options = ArrayHelper::merge($this->itemOptions, ['id' => 'item-' . $model->id]);
            $items[] = Html::tag($this->clientOptions['items'], $content, $options);
        }

        $items[] = Html::endTag($this->clientOptions['listType']) . PHP_EOL;
        return implode("\n", $items);
    }

    /**
     * Registers a specific jQuery UI widget asset bundle, initializes it with client options and registers related events
     * @param string $name the name of the jQuery UI widget
     * @param string $id the ID of the widget. If null, it will use the `id` value of [[options]].
     */
    protected function registerWidget($name, $id = null)
    {
        if ($id === null) {
            $id = $this->options['id'];
        }
        NestedSortableAsset::register($this->getView());
        $this->registerClientEvents($name, $id);
        $this->registerClientOptions($name, $id);
    }

}
