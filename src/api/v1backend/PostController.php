<?php

namespace tsmd\post\api\v1backend;

use Yii;
use tsmd\base\models\TsmdResult;
use tsmd\post\models\Post;
use tsmd\post\models\PostSearch;

/**
 * 提供图文模块的管理接口，添加、查看、修改、删除等
 *
 * Table Field | Description
 * ----------- | -----------
 * poid         | POID
 * parent       | Parent 父级 POID
 * uid          | 用户ID
 * type         | 类型，eg. `post` `page` `link` `file` `notice` `comment` `revision`
 * slug         | 唯一标识，eg. abc
 * title        | 标题
 * excerpt      | 摘要
 * content      | 内容
 * status       | 状态
 * password     | Password
 * redirect     | 跳转
 * urlBase      | Url Base
 * urlPath      | Url Path
 * mimeType     | Mime Type
 * fileSize     | File Size
 * imageWidth   | Image Width
 * imageHeight  | Image Height
 * cmntClosed   | Comment Closed
 * cmntCounter  | Comment Counter
 * objTable     | 关联对象表
 * objid        | 关联对象ID
 * publishedAt  | 发布时间
 *
 * Type Values | Description
 * ----------- | -----------
 * post     | 贴文
 * page     | 页面
 * link     | 连接
 * file     | 文件、图片
 * notice   | 通知
 * comment  | 评论
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
     * 图文列表，可提交类型、状态进行筛选
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>AUTH</kbd> `/post/v1backend/post/index`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * type     | [[string]] | No | 类型
     * status   | [[string]] | No | 状态
     *
     * @return array|PostSearch
     */
    public function actionIndex()
    {
        $search = new PostSearch(['exclFields' => ['content']]);
        $rows = $search->search($this->getQueryParams(), true);
        return TsmdResult::formatSuc('list', $rows, ['count' => $search->counter]);
    }

    /**
     * 添加图文
     *
     * <kbd>API</kbd> <kbd>POST</kbd> <kbd>AUTH</kbd> <kbd>/post/v1backend/post/create</kbd>
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * type     | [[string]]  | Yes | 类型
     * title    | [[string]]  | Yes | 标题
     * content  | [[string]]  | Yes | 内容
     * pushTarget | [[string]]  | No  | `all` 所有用户，`parcelGt5kg` 5KG 以上快递包裹的用户
     *
     * @return Post
     */
    public function actionCreate()
    {
        $model = Post::createBy(Yii::$app->request->post(), [
            'scenario' => Yii::$app->request->post('type'),
        ]);
        // 给用户推送通知
        $pushTarget = Yii::$app->request->post('pushTarget');
        if (!$model->hasErrors() && $pushTarget) {
            $model->attachBehavior('PostPush', 'tsmd\post\models\PostPushBehavior');
            $model->jypushSend($pushTarget);
        }
        return $model;
    }

    /**
     * 轮询推送通知给指定用户
     *
     * <kbd>API</kbd> <kbd>POST</kbd> <kbd>AUTH</kbd> <kbd>/post/v1backend/post/jypush-send</kbd>
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * poid     | [[integer]] | No | POID
     * uids     | [[string]]  | No | 多个用户 UID 用半角逗号隔开，如：123,456
     *
     * @return array
     */
    public function actionJypushSend()
    {
        $poid = $this->getBodyParams('poid');
        $uids = $this->getBodyParams('uids');
        if (empty($poid) || empty($uids)) {
            return TsmdResult::formatErr('Error data.');
        }
        $model = $this->findModel($poid);
        list($res, $return) = Yii::$app->get('jypush')->send($uids, $model->title, 'post', $model->poid);
        return $res
            ? TsmdResult::formatSuc('model', $return)
            : TsmdResult::formatSuc('message', $return);
    }

    /**
     * 查看图文
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>AUTH</kbd> <kbd>/post/v1backend/post/view</kbd>
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * poid     | [[integer]] | No | POID
     *
     * @param $poid
     * @return Post
     */
    public function actionView($poid)
    {
        $row = $this->findModel($poid)->findFormat()->toArray();
        if ($row['type'] == Post::TYPE_WELFARE) {
            $binder = new PostBinder();
            $binder->prepareUserInfos([$row['uid']]);
            $binder->bindUser($row);
        }
        return $row;
    }

    /**
     * 修改图文
     *
     * <kbd>API</kbd> <kbd>POST</kbd> <kbd>AUTH</kbd> <kbd>/post/v1backend/post/update</kbd>
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * poid     | [[integer]] | Yes | POID
     * title    | [[string]]  | Yes | 标题
     * content  | [[string]]  | Yes | 内容
     *
     * @return Post
     */
    public function actionUpdate()
    {
        $post = $this->findModel(Yii::$app->request->post('poid'));
        $post->scenario = $post->type;
        $post->load(Yii::$app->request->post(), '');
        $post->update();
        return $post;
    }

    /**
     * 删除图文
     *
     * <kbd>API</kbd> <kbd>POST</kbd> <kbd>AUTH</kbd> <kbd>/post/v1backend/post/delete</kbd>
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * poid     | [[integer]] | No | POID
     *
     * @return array|Post
     */
    public function actionDelete()
    {
        $model =$this->findModel(Yii::$app->request->post('poid'));
        return $model->delete() ? $this->success() : $model;
    }

    /**
     * 发布公益贴文
     *
     * <kbd>API</kbd> <kbd>POST</kbd> <kbd>AUTH</kbd> <kbd>/post/v1backend/post/publish-welfare</kbd>
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * poid     | [[integer]] | Yes | POID
     * urlPath  | [[file]]    | Yes | urlPathFile,
     * content  | [[string]]  | Yes | 内容
     * extras   | [[array]]   | Yes | 额外数据
     *
     * ```json
     * {
     *     'poid': '283747',
     *     'urlPath': 'urlPathFile',
     *     'content': '...',
     *     'extras': {
     *         'fbid': '...'
     *         'fbAvatar': 'fbAvatarFile', //固定值
     *         'fbNickname': '...',
     *     }
     *     'urlPathFile':fs.createReadStream("..."),
     *     'fbAvatarFile':fs.createReadStream("..."),
     * }
     * ```
     *
     * @return array
     */
    public function actionPublishWelfare()
    {
        $model = $this->findModel(Yii::$app->request->post('poid', 0), PostWelfare::class);
        $model->publish(Yii::$app->request->post());
        return $model->hasErrors()
            ? TsmdResult::formatErr($model->firstErrors)
            : TsmdResult::formatSuc('model', $model->toArray());
    }

    /**
     * Finds the Log model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $poid
     * @return Post the loaded model
     * @throws \yii\web\NotFoundHttpException if the model cannot be found
     */
    protected function findModel($poid, string $class = Post::class)
    {
        if (($model = $class::findOne(['poid' => $poid])) !== null) {
            return $model;
        } else {
            throw new \yii\web\NotFoundHttpException('The requested page does not exist.');
        }
    }
}
