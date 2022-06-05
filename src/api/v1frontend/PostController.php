<?php

namespace tsmd\post\api\v1frontend;

use Yii;
use tsmd\base\models\TsmdResult;
use tsmd\post\models\Post;
use tsmd\post\models\PostWelfare;
use tsmd\post\models\PostSearch;

/**
 * 提供贴文的列表、查看等接口
 */
class PostController extends \tsmd\base\controllers\RestFrontendController
{
    /**
     * @var array
     */
    protected $authExcept = ['index', 'view', 'view-page'];

    /**
     * 已发布的图文列表，可提交类型进行筛选
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>AUTH</kbd> `/post/v1frontend/post/index`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * type     | [[string]]  | Yes | 类型，eg. `notice` `page`
     * objTable | [[string]]  | No  | 關聯對象，eg. `calendar`，值为 `empty` 表示查询 objTable 为空的记录
     * objid    | [[string]]  | No  | 關聯對象值，eg. `se`, `air`, `air,se`
     * paStart  | [[string]]  | No  | 發布時間起始日期，eg. 2021-01-05
     * paEnd    | [[string]]  | No  | 發布時間結束日期，eg. 2021-02-05
     * page     | [[string]]  | No  | 分页参数，第几页，默认 1
     * pageSize | [[string]]  | No  | 分页参数，每页多少条，默认 20
     *
     * @param string $type
     * @param null|string $objTable
     * @param null|string $objid
     * @param null|string $paStart
     * @param null|string $paEnd
     * @return array
     */
    public function actionIndex($type, $objTable = null, $objid = null, $paStart = null, $paEnd = null)
    {
        $params = [
            'type' => $type,
            'objTable' => $objTable,
            'objid' => $objid,
            'paStart' => $paStart,
            'paEnd' => $paEnd,
            'inclFields' => $type == Post::TYPE_QA ? ['poid', 'title', 'excerpt', 'content'] : null,
            'status' => Post::STATUS_PUBLISH,
        ];
        $search = new PostSearch();
        $rows = $search->search($params, true);
        return TsmdResult::formatSuc('list', $rows ?: [], ['count' => $search->counter]);
    }

    /**
     * 查看詳情（如：關於我們、公司介紹、公告等）
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>NO AUTH</kbd> `/post/v1frontend/post/view`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * poidSlug | [[integer]] | No | 贴文 ID，或 slug (eg: about)
     *
     * @param string $poidSlug
     * @return array
     */
    public function actionView($poidSlug)
    {
        if (is_numeric($poidSlug)) {
            $post = Post::find()->where(['poid' => $poidSlug, 'status' => Post::STATUS_PUBLISH])->limit(1)->one();
            $post->findFormat();
        } elseif (is_string($poidSlug)) {
            $post = Post::find()->where(['slug' => $poidSlug, 'status' => Post::STATUS_PUBLISH])->limit(1)->one();
            $post->findFormat();
        }
        return TsmdResult::formatSuc('model', $post ?? []);
    }

    /**
     * 查看詳情（如：關於我們、公司介紹、公告等），向後兼容，參見 `view` 接口
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>NO AUTH</kbd> `/post/v1frontend/post/view-page`
     *
     * @param string $poidSlug
     * @return array
     */
    public function actionViewPage($poidSlug)
    {
        return $this->actionView($poidSlug);
    }

    /**
     * 创建公益贴文
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>NO AUTH</kbd> `/post/v1frontend/post/create-welfare`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * excerpt  | [[string]] | No | 贴文地址
     *
     * @return array
     */
    public function actionCreateWelfare()
    {
        ($model = new PostWelfare())->create(Yii::$app->request->post('excerpt', ''), $this->user->uid);
        return $model->hasErrors()
            ? TsmdResult::formatErr($model->firstErrors)
            : TsmdResult::formatSuc('model', $model->toArray());
    }
}
