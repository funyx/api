<?php
declare(strict_types = 1);

namespace demo\api;

use funyx\api\Model;

class Post extends Model
{
	public $table = 'post';
	public $caption = 'Post';
	public $title_field = 'content';
	public array $data_map = [
		'id',
		'content',
		'user'     => [
			'username',
			'email',
			'created_at'
		],
		'comments' => [
			'content',
			'user' => [
				'username',
				'email',
				'created_at'
			],
			'created_at'
		],
		'created_at'
	];
	protected bool $has_created_at = true;
	protected bool $has_updated_at = true;

	public function filterList(): void
	{
		parent::filterList();
	}

	/**
	 * @param array $data
	 *
	 * @return void
	 * @throws \Atk4\Data\Exception
	 * @throws \funyx\api\Exception
	 */
	public function createOne( array $data ): void
	{
		if ($user = $this->auth(true)) {
			parent::createOne($this->save([
				'user_id' => $user->get('id'),
				'content' => $this->body('content')
			])->format());
		}
	}

	/**
	 * @param array $data
	 *
	 * @return void
	 * @throws \Atk4\Data\Exception
	 */
	public function getOne( array $data ): void
	{
		// status 200 ok
		parent::getOne($this->load($this->param('id'))->format());
	}

	/**
	 * @param array $data
	 *
	 * @return void
	 * @throws \Atk4\Data\Exception
	 * @throws \funyx\api\Exception
	 */
	public function updateOne( array $data ): void
	{
		if ($user = $this->auth(true)) {
			parent::updateOne($this->load($this->param('id'))->save([
				'user_id' => $user->get('id'),
				'content' => $this->body('content')
			])->format());
		}
	}

	/**
	 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/410
	 *
	 * @throws \Atk4\Data\Exception
	 * @throws \funyx\api\Exception
	 */
	public function deleteOne(): void
	{
		if ($user = $this->auth(true)) {
			$this->load($this->param('id'))->delete();
			parent::deleteOne();
		}
	}

	protected function init(): void
	{
		parent::init();
		$this->addField('content');
		$this->hasOne('user', [
			'model'       => User::class,
			'our_field'   => 'user_id',
			'their_field' => 'id'
		]);
		$this->hasMany('comments', [
			'model'       => Comment::class,
			'our_field'   => 'id',
			'their_field' => 'post_id'
		]);
	}
}
