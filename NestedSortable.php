<?php

/**
 * @inheritdoc
 */

namespace claudejanz\yii2nestedSortable;

use yii\jui\Sortable;

/**
 * @inheritdoc
 */
class NestedSortable extends Sortable
{

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
