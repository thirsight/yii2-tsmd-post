<?php

namespace tsmd\post\api\v1backend;

use Yii;
use tsmd\base\models\TsmdResult;
use tsmd\post\models\Post;
use tsmd\post\models\PostSearch;

/**
 * 提供贴文模块的管理接口，添加、查看、修改、删除等
 *
 * Table Field | Description
 * ----------- | -----------
 * poid             | POID
 * parentid         | Parent 父级 POID
 * uid              | 用户 UID
 * type             | 类型，eg: `post` `page` `link` `file` `notice` `emer` `qa` `revision`
 * status           | 状态
 * slug             | 唯一标识
 * title            | 标题
 * excerpt          | 摘要
 * content          | 内容
 * password         | Password
 * redirect         | 跳转
 * fileBase         | File Url Base
 * filePath         | File Url Path
 * mimeType         | Mime Type
 * fileSize         | File Size
 * imageWidth       | Image Width
 * imageHeight      | Image Height
 * commentStatus    | Comment Status
 * commentCount     | Comment Count
 * objTable         | 关联对象表
 * objid            | 关联对象 ID
 * publishedTime    | 发布时间
 *
 * Type Values | Description
 * ----------- | -----------
 * post     | 贴文
 * page     | 页面
 * link     | 连接
 * file     | 文件、图片
 * notice   | 通知
 * emer     | 紧急
 * qa       | 问答
 * revision | 版本
 *
 * Status Values | Description
 * ------------- | -----------
 * publish    | 发布
 * future     | 延时发布
 * draft      | 草稿
 * pending    | 待发布
 * private    | 私有
 * trash      | 丢弃
 * autodraft  | 自动草稿
 * inherit    | 继承
 */
class PostController extends \tsmd\base\controllers\RestController
{
    /**
     * 贴文列表
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>AUTH</kbd> `/post/v1backend/post/search`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * type     | [[string]] | No | 类型
     * status   | [[string]] | No | 状态
     *
     * @return array
     */
    public function actionSearch()
    {
        $search = new PostSearch();
        $search->load($this->getBodyParams(), '');
        if (!$search->validate()) {
            return TsmdResult::failed($search->firstErrors);
        }
        list($rows, $count) = $search->query()->addSelectFields([], ['content'])->allWithCount();
        return TsmdResult::response($rows, ['count' => $count]);
    }

    /**
     * 添加贴文
     *
     * <kbd>API</kbd> <kbd>POST</kbd> <kbd>AUTH</kbd> <kbd>/post/v1backend/post/create</kbd>
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * type     | [[string]]  | Yes | 类型
     * title    | [[string]]  | Yes | 标题
     * content  | [[string]]  | Yes | 内容
     *
     * @return array
     */
    public function actionCreate()
    {
        $model = Post::createBy($this->getBodyParams(), [
            'scenario' => $this->getBodyParams('type'),
        ]);
        return $model->hasErrors()
            ? TsmdResult::failed($model->firstErrors)
            : TsmdResult::response($model->toArray());
    }

    /**
     * 查看贴文
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>AUTH</kbd> <kbd>/post/v1backend/post/view</kbd>
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * poid     | [[integer]] | Yes | poid
     *
     * @return array
     */
    public function actionView(int $poid)
    {
        $model = $this->findModel($poid);
        return TsmdResult::responseModel($model->toArray());
    }

    /**
     * 修改贴文
     *
     * <kbd>API</kbd> <kbd>POST</kbd> <kbd>AUTH</kbd> <kbd>/post/v1backend/post/update</kbd>
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * poid     | [[integer]] | Yes | POID
     * title    | [[string]]  | Yes | 标题
     * content  | [[string]]  | Yes | 内容
     *
     * @return array
     */
    public function actionUpdate()
    {
        $model = $this->findModel($this->getBodyParams('poid'));
        $model->setScenario($model->type);
        $model->load($this->getBodyParams(), '');
        $model->update();
        return $model->hasErrors()
            ? TsmdResult::failed($model->firstErrors)
            : TsmdResult::response($model->toArray());
    }

    /**
     * 删除贴文
     *
     * <kbd>API</kbd> <kbd>POST</kbd> <kbd>AUTH</kbd> <kbd>/post/v1backend/post/delete</kbd>
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * poid     | [[integer]] | No | POID
     *
     * @return array
     */
    public function actionDelete()
    {
        $model =$this->findModel($this->getBodyParams('poid'));
        return $model->delete() ? TsmdResult::response() : TsmdResult::failed();
    }

    /**
     * @param string $poid
     * @return Post the loaded model
     * @throws \yii\web\NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $poid)
    {
        if (($model = Post::findOne($poid)) !== null) {
            return $model;
        } else {
            throw new \yii\web\NotFoundHttpException('The requested `post` does not exist.');
        }
    }
}
