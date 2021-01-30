<?php
declare(strict_types = 1);

namespace demo\api;

use funyx\api\Model;

class Comment extends Model
{
	public $table = 'comment';
	public $caption = 'Comment';

	protected bool $has_created_at = true;
	protected bool $has_updated_at = true;

	protected function init(): void
	{
		parent::init();
		$this->hasOne('user', [
			'model' => User::class,
			'our_field' => 'user_id',
			'their_field' => 'id'
		]);
		$this->hasOne('post', [
			'model' => Post::class,
			'our_field' => 'post_id',
			'their_field' => 'id'
		]);
		$this->addField('content');
	}
}
