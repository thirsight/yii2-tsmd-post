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
    /**
     * @var int
     */
    public $poid;

    /**
     * @return string[]
     */
    public function _fixtures()
    {
        return [
            'users' => 'tsmd\base\tests\fixtures\UsersFixture',
        ];
    }

    /**
     * @group postFePost
     * @group postFePostSearch
     */
    public function trySearch(ApiTester $I)
    {
        $data = [
            'type' => 'qa',
        ];
        $url = $I->grabFixture('users')->wrapUrl('/post/v1frontend/post/search', 'fe');
        $I->sendGET($url, $data);
        $I->seeResponseContains('SUCCESS');

        $this->poid = json_decode($I->grabResponse(), true)['list'][0]['poid'];
    }

    /**
     * @group postFePost
     * @group postFePostView
     */
    public function tryView(ApiTester $I)
    {
        $data = [
            'poidSlug' => $this->poid,
            'password' => 'password',
        ];
        $url = $I->grabFixture('users')->wrapUrl('/post/v1frontend/post/view', 'fe');
        $I->sendGET($url, $data);
        $I->seeResponseContainsJson(['poid' => $this->poid]);
    }
}
