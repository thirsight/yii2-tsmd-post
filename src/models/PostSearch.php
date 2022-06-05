<?php

namespace tsmd\post\models;

use Yii;
use yii\db\Expression;

/**
 * PostSearch represents the model behind the search form about `Post`.
 */
class PostSearch extends \yii\base\Model
{
    public $poid;
    public $parent;
    public $uid;
    public $type;
    public $slug;
    public $status;

    public $cmntClosed;
    public $cmntCounter;

    /**
     * @var string `empty` 查询所有 objTable 为空的记录
     */
    public $objTable;
    public $objid;

    public $paStart;
    public $paEnd;

    public $publishedDate;
    public $createdDate;
    public $updatedDate;

    public $counter = -1;

    public $inclFields = [];
    public $exclFields = [];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['poid', 'integer'],

            ['parent', 'integer'],

            ['uid', 'integer'],

            ['type', 'in', 'range' => array_keys(Post::presetTypes())],

            ['slug', 'string', 'max' => 128],

            ['status', 'in', 'range' => array_keys(Post::presetStatuses())],

            ['cmntClosed', 'in', 'range' => [0, 1]],
            ['cmntCounter', 'integer'],

            ['objTable', 'string', 'max' => 64],
            ['objid', 'string', 'max' => 64],

            [['paStart', 'paEnd'], 'datetime', 'format' => 'php:Y-m-d'],
            [['publishedDate', 'createdDate', 'updatedDate'], 'datetime', 'format' => 'php:Y-m-d'],

            ['inclFields', 'safe'],
            ['exclFields', 'safe'],
        ];
    }

    /**
     * @param array $params
     * @param bool $isCount 是否统计总数
     * @return array
     */
    public function search($params, $isCount = false)
    {
        $this->load($params, '');
        if (!$this->validate()) {
            return null;
        }

        $fields = array_diff($this->inclFields ?: (new Post)->attributes(), $this->exclFields);
        $query = Post::find()
            ->select($fields)
            ->andFilterWhere([
                'poid'   => $this->poid,
                'parent' => $this->parent,
                'uid'    => $this->uid,
                'type'   => $this->type,
                'slug'   => $this->slug,
                'status' => $this->status,
                'cmntClosed'  => $this->cmntClosed,
                'cmntCounter' => $this->cmntCounter,
            ]);
        if ($this->paStart && $this->paEnd) {
            $query->andWhere(['between', 'publishedAt', "{$this->paStart} 00:00:00", "{$this->paEnd} 23:59:59"]);
        }
        if ($this->publishedDate) {
            $query->andWhere(['between', 'publishedAt', "{$this->publishedDate} 00:00:00", "{$this->publishedDate} 23:59:59"]);
        }
        if ($this->createdDate) {
            $query->andWhere(['between', 'createdAt', "{$this->createdDate} 00:00:00", "{$this->createdDate} 23:59:59"]);
        }
        if ($this->updatedDate) {
            $query->andWhere(['between', 'updatedAt', "{$this->updatedDate} 00:00:00", "{$this->updatedDate} 23:59:59"]);
        }
        $query->andWhere($this->whereObjTable());
        $query->andWhere($this->whereObjid());

        if ($isCount) {
            $this->counter = $query->count();
        }

        return $query->offset(Yii::$app->request->getPageOffset())
            ->limit(Yii::$app->request->getPageSize())
            ->orderBy('publishedAt DESC')
            ->asArray()
            ->all();
    }

    /**
     * @return array
     */
    protected function whereObjTable()
    {
        if ($this->objTable == 'empty') {
            return ['objTable' => ''];
        } elseif ($this->objTable) {
            return ['objTable' => $this->objTable];
        }
        return [];
    }

    /**
     * @return array|Expression
     */
    protected function whereObjid()
    {
        if (empty($this->objid)) return [];

        $where = ['or'];
        foreach (explode(',', $this->objid) as $objid) {
            $where[] = new Expression("FIND_IN_SET('{$objid}', objid)");
        }
        return $where;
    }
}
