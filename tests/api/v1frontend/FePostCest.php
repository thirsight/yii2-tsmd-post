<?php

/**
 * ```
 * $ cd ../yii2-app-advanced/api # (the dir with codeception.yml)
 * $ ./codecept run api -g postFePost -d
 * $ ./codecept run api -c codeception-sandbox.yml -g postFePost -d
 * ```
 */
class FePostCest
{
    public function _fixtures()
    {
        return [
            'users' => 'tsmd\base\tests\fixtures\UsersFixture',
        ];
    }

    /**
     * @group postFePostIndex
     */
    public function tryIndex(ApiTester $I)
    {
        $data = [
            'type' => 'qa',
        ];
        $url = $I->grabFixture('users')->wrapUrl('/post/v1frontend/post/index', 'fe');
        $I->sendGET($url, $data);
        $I->seeResponseContains('title');
    }

    /**
     * @group postFePostView
     */
    public function tryView(ApiTester $I)
    {
        $data = [
            'poidSlug' => '12152',
        ];
        $url = $I->grabFixture('users')->wrapUrl('/post/v1frontend/post/view', 'fe');
        $I->sendGET($url, $data);
        $I->seeResponseContains('title');
    }

    /**
     * @group postFeCreateWelfare
     */
    public function tryCreateWelfare(ApiTester $I)
    {
        $data = [
            'excerpt' => 'https://m.facebook.com/story.php?story_fbid=5425215360831539&id=100000293855187',
        ];
        $url = $I->grabFixture('users')->wrapUrl('/post/v1frontend/post/create-welfare', 'fe');
        $I->sendPOST($url, $data);
        $I->seeResponseContains('SUCCESS');
    }
}