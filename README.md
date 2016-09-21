Nested Sortable
===============

[![Latest Stable Version](https://poser.pugx.org/claudejanz/yii2-nested-sortable/v/stable.svg)](https://packagist.org/packages/claudejanz/yii2-nested-sortable) [![Total Downloads](https://poser.pugx.org/claudejanz/yii2-nested-sortable/downloads.svg)](https://packagist.org/packages/claudejanz/yii2-nested-sortable) [![Latest Unstable Version](https://poser.pugx.org/claudejanz/yii2-nested-sortable/v/unstable.svg)](https://packagist.org/packages/claudejanz/yii2-nested-sortable) [![License](https://poser.pugx.org/claudejanz/yii2-nested-sortable/license.svg)](https://packagist.org/packages/claudejanz/yii2-nested-sortable)


an implementation of [nestedSortable2.0](http://ilikenwf.github.io/example.html)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist claudejanz/yii2-nested-sortable "*"
```

or add

```
"claudejanz/yii2-nested-sortable": "*"
```

to the require section of your `composer.json` file.

Prepare Model
-------------

In table migrateion:
```php
$this->createTable('page', [
    'id'               => $this->primaryKey(),
    'title'            => $this->string(255)->notNull(),
    'parent_id'        => $this->integer()->null(),
    'weight'           => $this->integer(11)->notNull()->defaultValue(1),
]);

$this->createIndex('idx-page-parent_id', 'page', 'parent_id');
$this->addForeignKey('fk-page-parent_id-page-id', 'page', 'parent_id', 'page', 'id', 'SET NULL', 'CASCADE');
```

In ActiveRecord:
[for more details on Customizing Query Classes](http://www.yiiframework.com/doc-2.0/guide-db-active-record.html#customizing-query-classes)
```php
/**
* @inheridoc
*/
public static function find()
{
    return (new PageQuery(get_called_class()))->orderBy('weight');
}

/**
* @return ActiveQuery
*/
public function getParent()
{
   return $this->hasOne(Page::className(), ['id' => 'parent_id']);
}

/**
* @return ActiveQuery
*/
public function getPages()
{
   return $this->hasMany(Page::className(), ['parent_id' => 'id'])->inverseOf('parent');
}
```

Usage
-----

Once the extension is installed, simply use it in your code by  :

In view:
```php
use claudejanz\yii2nestedSortable\NestedSortable;
echo NestedSortable::widget([
    'items'         => Page::find()->andWhere(['parent_id'=>null])->all(),
    'url'           => ['pages/save-sortable'],
    'contentAttribute' => 'title';
    'itemsAttribute' => 'pages';
]);
```

In controller:
```php
public function actions()
{
    return [
        'save-sortable' => [
            'class' => 'claudejanz\yii2nestedSortable\NestedSortableAction',
            //'scenario'=>'editable',  //optional
            'modelclass' => Page::className(),
        ],
    ];
}
```
