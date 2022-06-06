<?php

namespace tsmd\post\models;

/**
 * PostSearch represents the model behind the search form about `Post`.
 */
class PostSearch extends \yii\base\Model
{
    public $poid;
    public $parentid;
    public $uid;
    public $type;
    public $status;
    public $slug;

    /**
     * @var int
     */
    public $commentOpen;

    /**
     * @var string `empty` 查询所有 objTable 为空的记录
     */
    public $objTable;
    /**
     * @var string
     */
    public $objid;

    /**
     * @var string 发布日期
     */
    public $pDateStart;
    /**
     * @var string 发布日期
     */
    public $pDateEnd;
    /**
     * @var string 创建日期
     */
    public $cDateStart;
    /**
     * @var string 创建日期
     */
    public $cDateEnd;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['poid', 'number'],
            ['parentid', 'number'],
            ['uid', 'number'],

            ['type', 'in', 'range' => array_keys(Post::presetTypes())],
            ['status', 'in', 'range' => array_keys(Post::presetStatuses())],

            ['slug', 'string', 'max' => 128],
            ['commentOpen', 'number'],

            ['objTable', 'string', 'max' => 64],
            ['objid', 'string', 'max' => 64],

            [['pDateStart', 'pDateEnd', 'cDateStart', 'cDateEnd'], 'datetime', 'format' => 'php:Y-m-d'],
        ];
    }

    /**
     * @return PostQuery
     */
    public function query()
    {
        return (new PostQuery)
            ->andFilterWhere(['poid' => $this->poid])
            ->andFilterWhere(['slug' => $this->slug])
            ->andWherePublishedDate($this->pDateStart, $this->pDateEnd)
            ->andWhereCreatedDate($this->cDateStart, $this->cDateEnd)
            ->andFilterWhere(['objid' => $this->objid])
            ->andWhereObjTable($this->objTable)
            ->andFilterWhere(['parentid' => $this->parentid])
            ->andFilterWhere(['uid' => $this->uid])
            ->andFilterWhere(['type' => $this->type])
            ->andFilterWhere(['status' => $this->status])
            ->andFilterWhere(['commentOpen' => $this->commentOpen])
            ->orderBy('poid DESC')
            ->addPaging();
    }
}
