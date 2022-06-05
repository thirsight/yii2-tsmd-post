<?php

/**
 * ```
 * $ cd ../yii2-app-advanced/api # (the dir with codeception.yml)
 * $ ./codecept run api -g postBePost -d
 * $ ./codecept run api -c codeception-sandbox.yml -g postBePost -d
 * ```
 */
class BePostCest
{
    public $poid;

    public function _fixtures()
    {
        return [
            'users' => 'tsmd\base\tests\fixtures\UsersFixture',
        ];
    }

    /**
     * @group postBePost
     * @group postBePostCreate
     */
    public function tryCreate(ApiTester $I)
    {
        $data = [
            'type' => 'qa',
            'title' => '你有什么问题？',
            'content' => '不想给你答案，自个儿想去。',
            'status' => 'publish',
        ];
        $url = $I->grabFixture('users')->wrapUrl('/post/v1backend/post/create', 'be');
        $I->sendPOST($url, $data);
        $I->seeResponseContains('title');

        $this->poid = json_decode($I->grabResponse(), true)['poid'];
    }

    /**
     * @group postBePost
     * @group postBePostView
     */
    public function tryView(ApiTester $I)
    {
        $url = $I->grabFixture('users')->wrapUrl('/post/v1backend/post/view', 'be');
        $I->sendGET($url, ['poid' => $this->poid]);
        $I->seeResponseContains($this->poid);
    }

    /**
     * @group postBePost
     * @group postBePostUpdate
     */
    public function tryUpdate(ApiTester $I)
    {
        $data = [
            'poid' => $this->poid,
            'status' => 'draft',
        ];
        $url = $I->grabFixture('users')->wrapUrl('/post/v1backend/post/update', 'be');
        $I->sendPOST($url, $data);
        $I->seeResponseContains('draft');
    }

    /**
     * @group postBePost
     * @group postBePostDelete
     */
    public function tryDelete(ApiTester $I)
    {
        $data = [
            'poid' => $this->poid,
        ];
        $url = $I->grabFixture('users')->wrapUrl('/post/v1backend/post/delete', 'be');
        $I->sendPOST($url, $data);
        $I->seeResponseContains('SUCCESS');
    }

    /**
     * @group postBePublishWelfare
     */
    public function tryPublishWelfare(ApiTester $I)
    {
        $data = [
            'poid' => '12152',
            'urlBase' => 'https://files.ada.com.tw/',
            'urlPath' => 'https://files.ada.com.tw/data/attachment/forum/202205/12/160002511441a2246261ba33cb44d05380f670.jpg',
            'content' => '不想给你答案，自个儿想去。',
            'extras' => [
                'fbid' => 1111111111111111111,
                'fbAvatar' => '头像',
                'fbNickname' => '昵称',
            ],
        ];
        $url = $I->grabFixture('users')->wrapUrl('/post/v1backend/post/publish-welfare', 'be');
        $I->sendPOST($url, $data);
        $I->seeResponseContains('title');
    }
}