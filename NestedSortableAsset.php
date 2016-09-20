<?php

/**
 * @inheritdoc
 */

namespace claudejanz\yii2nestedSortable;

use yii\web\AssetBundle;

/**
 * @inheritdoc
 */
class NestedSortableAsset extends AssetBundle
{

    public $sourcePath = '@bower/nestedSortable2.0';
    public $js = [
        'jquery.mjs.nestedSortable.js',
    ];

}
