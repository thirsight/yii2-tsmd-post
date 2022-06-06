<?php

namespace tsmd\post\models;

use yii\db\Expression;
use tsmd\base\models\TsmdQueryTrait;

/**
 * This is the Query class for [[Post]].
 */
class PostQuery extends \yii\db\Query
{
    use TsmdQueryTrait;

    /**
     * {@inheritdoc}
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->from(Post::tableName());
        $this->modelClass = Post::class;
    }

    /**
     * @return $this
     */
    public function addSelectHasPassword()
    {
        return $this->addSelect(new Expression('IF(LENGTH(password)>0, "Y", "N") AS hasPassword'));
    }

    /**
     * @param string|null $objTable
     * @return $this
     */
    public function andWhereObjTable($objTable)
    {
        if ($objTable == 'empty') {
            return $this->andWhere(['objTable' => '']);
        } elseif ($objTable) {
            return $this->andWhere(['objTable' => $objTable]);
        }
        return $this;
    }

    /**
     * @param string|null $dateStart
     * @param string|null $dateEnd
     * @return $this
     */
    public function andWherePublishedDate($dateStart, $dateEnd)
    {
        if ($dateStart && $dateEnd) {
            return $this->andWhere(['between', 'publishedTime', strtotime($dateStart), strtotime($dateEnd . ' +1 Day')]);
        }
        return $this;
    }

    /**
     * @param string|null $dateStart
     * @param string|null $dateEnd
     * @return $this
     */
    public function andWhereCreatedDate($dateStart, $dateEnd)
    {
        if ($dateStart && $dateEnd) {
            return $this->andWhere(['between', 'createdTime', strtotime($dateStart), strtotime($dateEnd . ' +1 Day')]);
        }
        return $this;
    }
}
