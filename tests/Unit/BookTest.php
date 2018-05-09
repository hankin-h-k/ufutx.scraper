<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Wechat;
class BookTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->assertTrue(true);
    }


    /**
     * 注册用户
     */
    public function testCreateUser()
    {	
    	$wechat_config = config('wechat.mock_user');
    	// dd($wechat_config);
    	$wechat = Wechat::where('openid', $wechat_config['openid'])->first();
    	if (empty($wechat)) {
    		$wechat = Wechat::create($wechat_config);
    	}
    	if (empty($wechat->user_id)) {
    		$mobile = '15872844800';
    		$user = User::create([
    			'name'=>$wechat_config['nickname'],
    			'mobile'=>$mobile,
    			'email'=>$mobile.'@ufutx.com',
    			'password'=>'',
    		]);
    		$wechat->user_id = $user->id;
    		$wechat->save();
    	}else{
    		$user = User::find($wechat->user_id);
    	}
    	$this->assertTrue(true);
    	return $user;
    	
    }

    /**
     * @depends testCreateUser
     */
    public function testBook($user)
    {
    	$response = $this->actingAs($user, 'api')
                         ->get('/api/books/3/v2');
        $response_v2 = $this->actingAs($user, 'api')
                         ->get('/api/books/3/v2?library_id=1');
        $response->assertStatus(200);
        $response_v2->assertStatus(200);
    }

    /**
     * 删除图书馆
     * @depends testCreateUser
     */
    public function testDeleteLibrary($user)
    {
        $response = $this->actingAs($user, 'api')
                         ->delete('/api/libraries/31');
        $response->assertStatus(200);
    }

    /**
     * 图书馆列表
     * @depends testCreateUser
     */
    public function testLibraries($user)
    {
        $response = $this->actingAs($user, 'api')
                         ->get('/api/libraries?keyword=Hankin');
        $response->assertStatus(200);
    }

    /**
     * 修改我的信息
     * @depends testCreateUser
     */
    public function testUpdateUser($user)
    {
        $response = $this->actingAs($user, 'api')
                         ->put('/api/user', ['name'=>'']);
        $response->assertStatus(200);
    }

    /**
     * 删除预借信息
     * @depends testCreateUser
     */
    public function testDeleteBorrow($user)
    {
        $response = $this->actingAs($user, 'api')
                         ->delete('/api/libraries/30/borrows/46');
        $response->assertStatus(200);

    }

    /**
     * 手动添加图书
     * @depends testCreateUser
     */
    public function testAddBook($user)
    {
        $data = [
            'class_id'=>0,
            'title'=>'长颈鹿不会跳舞',
            'origin_title'=>'',
            'author'=>'(英)吉尔斯·安德烈 著 (英)盖伊·帕克-里斯 绘',
            'translator'=>'麦豆 兰童',
            'image'=>'https://img3.doubanio.com/lpic/s8866475.jpg',
            'summary'=>'杰拉德是只长颈鹿，长脖子优美而纤细。可惜膝盖向外弯曲，腿瘦得骨头连着皮。 杰拉德不太会跳舞。每年他都担心灰在丛林舞会出丑。但是在一个美丽的月圆之夜，杰拉德发现，原来换一首音乐来跳舞，就可以变得独一无二……',
            'publisher'=>'北京科学技术出版社',
            'price'=>'29.80',
            'isbn'=>'9787530457011',
            'pubdate'=>'2012-4',
            'pages'=>'40',
        ];
        $response = $this->actingAs($user, 'api')
                         ->post('/api/books/v2', $data);
        $response->assertStatus(200);
    }
}
