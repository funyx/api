<?php
declare(strict_types = 1);

namespace demo\api;

use funyx\api\Data\AccountUser;

class User extends AccountUser
{
	protected function init(): void
	{
		parent::init();
		$this->hasMany('posts', [
			'model'       => Post::class,
			'our_field'   => 'id',
			'their_field' => 'user_id'
		]);
		$this->data_map['posts'] = [
			'id',
			'content',
			'created_at'
		];
		$this->hasMany('comments', [
			'model'       => Comment::class,
			'our_field'   => 'id',
			'their_field' => 'user_id'
		]);
		$this->data_map['comments'] = [
			'content',
			'post_id',
			'created_at'
		];
	}
}
