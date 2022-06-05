<?php

namespace tsmd\post\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\HtmlPurifier;
use tsmd\base\user\models\User;

/**
 * This is the model class for table "{{%post}}".
 *
 * @property int $poid
 * @property int $parentid
 * @property int $uid
 * @property string $type
 * @property string $status
 * @property string|null $slug
 * @property string $title
 * @property string|null $excerpt
 * @property string|null $content
 * @property string $password
 * @property string $redirect
 * @property string $fileBase
 * @property string $filePath
 * @property string $mimeType
 * @property int $fileSize
 * @property int $imageWidth
 * @property int $imageHeight
 * @property int $commentStatus
 * @property int $commentCount
 * @property string $objTable
 * @property string $objid
 * @property int $publishedTime
 * @property int $createdTime
 * @property int $updatedTime
 *
 * @property User $parentPost
 * @property User $user
 */
class Post extends \tsmd\base\models\ArModel
{
    /**
     * 此分隔符用于从 content 中自动提取 excerpt
     */
    const MARK_MORE = '<!--more-->';

    const TYPE_POST     = 'post';
    const TYPE_PAGE     = 'page';
    const TYPE_LINK     = 'link';
    const TYPE_FILE     = 'file';
    const TYPE_NOTICE   = 'notice';
    const TYPE_EMER     = 'emer';
    const TYPE_QA       = 'qa';
    const TYPE_REVISION = 'revision';

    const STATUS_PUBLISH    = 'publish';
    const STATUS_FUTURE     = 'future';
    const STATUS_DRAFT      = 'draft';
    const STATUS_PENDING    = 'pending';
    const STATUS_PRIVATE    = 'private';
    const STATUS_TRASH      = 'trash';
    const STATUS_AUTODRAFT  = 'autoDraft';
    const STATUS_INHERIT    = 'inherit';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->on(static::EVENT_BEFORE_VALIDATE, [$this, 'prefilter']);
        $this->on(static::EVENT_BEFORE_INSERT, [$this, 'saveInput']);
        $this->on(static::EVENT_BEFORE_UPDATE, [$this, 'saveInput']);

        Yii::$app->formatter->attachBehavior('YiiFormatterWpBehavior', 'tsmd\base\yii\YiiFormatterWpBehavior');
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%post}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'poid' => 'poid',
            'parentid' => 'Parent poid',
            'uid' => 'uid',
            'type' => 'Type',
            'status' => 'Status',
            'slug' => 'Slug',
            'title' => 'Title',
            'excerpt' => 'Excerpt',
            'content' => 'Content',
            'password' => 'Password',
            'redirect' => 'Redirect',
            'fileBase' => 'File Url Base',
            'filePath' => 'File Url Path',
            'mimeType' => 'Mime Type',
            'fileSize' => 'File Size',
            'imageWidth' => 'Image Width',
            'imageHeight' => 'Image Height',
            'commentStatus' => 'Comment Status',
            'commentCount' => 'Comment Count',
            'objTable' => 'Object Table',
            'objid' => 'Object ID',
            'publishedTime' => 'Published Time',
            'createdTime' => 'Created Time',
            'updatedTime' => 'Updated Time',
      ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParentPost()
    {
        return $this->hasOne(Post::class, ['poid' => 'parentid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['uid' => 'uid']);
    }

    /**
     * 贴文类型
     * @param null $key
     * @param null $default
     * @return array|mixed
     */
    public static function presetTypes($key = null, $default = null)
    {
        $data = [
            self::TYPE_POST     => ['name' => 'Post'],
            self::TYPE_PAGE     => ['name' => 'Page'],
            self::TYPE_LINK     => ['name' => 'Link'],
            self::TYPE_FILE     => ['name' => 'File'],
            self::TYPE_NOTICE   => ['name' => 'Notice'],
            self::TYPE_EMER     => ['name' => 'Emergency'],
            self::TYPE_QA       => ['name' => 'Q&A'],
            self::TYPE_REVISION => ['name' => 'Revision'],
        ];
        return $key === null ? $data : ArrayHelper::getValue($data, $key, $default);
    }

    /**
     * 贴文状态
     * @param null $key
     * @param null $default
     * @return array|mixed
     */
    public static function presetStatuses($key = null, $default = null)
    {
        $data = [
            self::STATUS_PUBLISH    => ['name' => 'Publish'],
            self::STATUS_FUTURE     => ['name' => 'Future'],
            self::STATUS_DRAFT      => ['name' => 'Draft'],
            self::STATUS_PENDING    => ['name' => 'Pending'],
            self::STATUS_PRIVATE    => ['name' => 'Private'],
            self::STATUS_TRASH      => ['name' => 'Trash'],
            self::STATUS_AUTODRAFT  => ['name' => 'Auto Draft'],
            self::STATUS_INHERIT    => ['name' => 'Inherit'],
        ];
        return $key === null ? $data : ArrayHelper::getValue($data, $key, $default);
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        foreach (array_keys(static::presetTypes()) as $type) {
            $scenarios[$type] = $scenarios['default'];
        }
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        switch ($this->scenario) {
            case self::TYPE_POST:
            case self::TYPE_PAGE:
            case self::TYPE_NOTICE:
            case self::TYPE_EMER:
            case self::TYPE_QA:
            case self::TYPE_REVISION:
                return $this->rulesPost();
            case self::TYPE_LINK:
                return $this->rulesLink();
            case self::TYPE_FILE:
                return $this->rulesFile();
        }
        return parent::rules();
    }

    /**
     * @return array
     */
    protected function baseRules()
    {
        return [
            ['parentid', 'integer'],
            ['uid', 'integer'],

            ['type', 'required'],
            ['type', 'in', 'range' => array_keys(static::presetTypes())],
            ['status', 'default', 'value' => self::STATUS_DRAFT],
            ['status', 'in', 'range' => array_keys(static::presetStatuses())],

            ['slug', 'string', 'max' => 128],
            ['slug', 'unique'],

            ['title', 'string'],

            ['content', 'string'],
            ['content', 'filter', 'filter' => [Yii::$app->formatter, 'convertInvalidEntities']],
            ['content', 'filter', 'filter' => [Yii::$app->formatter, 'balanceTags']],
            ['content', 'filter', 'filter' => [Yii::$app->formatter, 'stripEmptyTags']],

            ['excerpt', 'string'],
            ['excerpt', function ($attribute, $params) {
                if (empty($this->excerpt)) {
                    $morePos = stripos($this->content, self::MARK_MORE);
                    $this->excerpt = substr($this->content, 0, $morePos);
                }
            }, 'skipOnEmpty' => false],

            ['password', 'string', 'max' => 64],

            ['objTable', 'string', 'max' => 64],
            ['objid', 'string', 'max' => 64],

            // Type Link
            ['redirect', 'string', 'max' => 255],

            // Type File
            ['fileBase', 'string', 'max' => 255],
            ['filePath', 'string', 'max' => 255],
            ['mimeType', 'string', 'max' => 64],
            ['fileSize', 'integer'],
            ['imageWidth', 'integer'],
            ['imageHeight', 'integer'],

            // Reply
            ['commentStatus', 'default', 'value' => 10],
            ['commentStatus', 'in', 'range' => [10, 30]],

            ['publishedTime', 'integer'],
        ];
    }

    /**
     * @return array
     */
    protected function rulesPost()
    {
        return array_merge([
            ['title', function ($attribute, $params) {
                if (empty($this->title) && empty($this->content)) {
                    $this->addError($attribute, 'Title or content can not be empty.');
                }
            }, 'skipOnEmpty' => false],
        ], $this->baseRules());
    }

    /**
     * @return array
     */
    protected function rulesLink()
    {
        return array_merge([
            ['redirect', 'required'],
        ], $this->baseRules());
    }

    /**
     * @return array
     */
    protected function rulesFile()
    {
        return array_merge([
            ['fileBase', 'required'],
            ['filePath', 'required'],
        ], $this->baseRules());
    }

    /**
     * 验证前的前置过滤器
     */
    protected function prefilter()
    {
        foreach ($this as $field => $value) {
            if (in_array($field, ['content'])) {
                $this->{$field} = HtmlPurifier::process(Yii::$app->formatter->mergeBlank($value), [
                    'HTML.Allowed' => 'h1,h2,h3,h4,h5,h6,span,strong,code,em,b,i,dl,dt,dd,ul,ol,li,blockquote,sup,sub,big,small,p,u,s,br,hr,img,a,table,caption,thead,tbody,tr,td,th,col,colgroup',
                    'HTML.AllowedAttributes' => '*.title,*.src,*.href,*.height,*.width,*.class,*.style',
                ]);

            } elseif (is_string($value)) {
                $this->{$field} = Yii::$app->formatter->mergeBlank(strip_tags($value));
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function saveInput()
    {
        parent::saveInput();

        if ($this->status == self::STATUS_PUBLISH && !$this->publishedTime) {
            $this->publishedTime = time();
        }
    }
}
