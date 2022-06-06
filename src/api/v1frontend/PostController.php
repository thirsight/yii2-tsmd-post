<?php

namespace tsmd\post\api\v1frontend;

use Yii;
use tsmd\base\models\TsmdResult;
use tsmd\post\models\Post;
use tsmd\post\models\PostSearch;
use yii\db\Expression;

/**
 * 提供贴文的列表、查看等接口
 */
class PostController extends \tsmd\base\controllers\RestFrontendController
{
    /**
     * @var array
     */
    protected $authExcept = ['search', 'view'];

    /**
     * 已发布的图文列表，可提交类型进行筛选
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>AUTH</kbd> `/post/v1frontend/post/search`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * type         | [[string]]  | Yes | 类型，eg. `notice` `page`
     * parentid     | [[integer]] | No  | 上级贴文
     * objTable     | [[string]]  | No  | 關聯對象，eg. 值为 `empty` 表示查询 objTable 为空的记录
     * objid        | [[integer]] | No  | 關聯對象值
     * pDateStart   | [[string]]  | No  | 發布時間起始日期，eg. 2022-01-01
     * pDateEnd     | [[string]]  | No  | 發布時間結束日期，eg. 2022-06-06
     * page         | [[integer]] | No  | 分页参数，第几页，默认 1
     * pageSize     | [[integer]] | No  | 分页参数，每页多少条，默认 20
     *
     * @param string $type
     * @param string|int $parentid
     * @param string $objTable
     * @param string|int $objid
     * @param string $pDateStart
     * @param string $pDateEnd
     * @return array
     */
    public function actionSearch($type, $parentid = '', $objTable = '', $objid = '', $pDateStart = '', $pDateEnd = '')
    {
        $params = [
            'parentid'   => $parentid,
            'type'       => $type,
            'objTable'   => $objTable,
            'objid'      => $objid,
            'pDateStart' => $pDateStart,
            'pDateEnd'   => $pDateEnd,
            'status'     => Post::STATUS_PUBLISH,
        ];
        $search = new PostSearch();
        $search->load($params, '');
        if (!$search->validate()) {
            return TsmdResult::failed($search->firstErrors);
        }
        list($rows, $count) = $search->query()
            ->addSelectFields([], ['content', 'password'])
            ->addSelectHasPassword()
            ->allWithCount();
        return TsmdResult::response($rows, ['count' => $count]);
    }

    /**
     * 贴文詳情
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>NO AUTH</kbd> `/post/v1frontend/post/view`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * poidSlug | [[integer]] | No | 贴文 poid，或 slug (eg: about)
     *
     * @param string $poidSlug
     * @param string $password
     * @return array
     */
    public function actionView($poidSlug, string $password = '')
    {
        $model = $this->findModel($poidSlug);
        if (!$model->validatePassword($password)) {
            return TsmdResult::failed(['PostPasswordIncorrect' => 'Post password is incorrect.']);
        }
        return TsmdResult::responseModel($model->toArray());
    }

    /**
     * @param string $poid
     * @return Post the loaded model
     * @throws \yii\web\NotFoundHttpException if the model cannot be found
     */
    protected function findModel($poidSlug)
    {
        $cond = [
            'and',
            ['or', ['poid' => $poidSlug], ['slug' => $poidSlug]],
            ['status' => Post::STATUS_PUBLISH],
        ];
        $model = Post::find()
            ->where($cond)
            ->limit(1)
            ->one();
        if ($model !== null) {
            return $model;
        } else {
            throw new \yii\web\NotFoundHttpException('The requested `post` does not exist.');
        }
    }
}
