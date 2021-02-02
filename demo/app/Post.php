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
		parent::doFilterList();
	}

	public function getOne(): void
	{
		parent::doGetOne();
	}

	/**
	 * @return void
	 * @throws \atk4\data\Exception
	 * @throws \funyx\api\Exception
	 */
	public function createOne(): void
	{
		if ($user = $this->auth(true)) {
			$this->set('user_id', $user->get('id'));
			$this->set('content', $this->body('content'));
			parent::doCreateOne();
		}
	}

	/**
	 * @return void
	 * @throws \atk4\data\Exception
	 * @throws \funyx\api\Exception
	 * @throws \atk4\core\Exception
	 */
	public function updateOne(): void
	{
		if ($user = $this->auth(true)) {
			$this->addCondition('user_id', $user->get('id'));
			$this->addCondition($this->public_key_field, $this->param('id'));
			$this->loadAny();
			$this->set('content', $this->body('content'));
			parent::doUpdateOne();
		}
	}

	/**
	 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/410
	 *
	 * @throws \atk4\data\Exception
	 * @throws \funyx\api\Exception
	 */
	public function deleteOne(): void
	{
		if ($user = $this->auth(true)) {
			$this->addCondition('user_id', $user->get('id'));
			$this->addCondition($this->public_key_field, $this->param('id'));
			$this->loadAny();
			parent::doDeleteOne();
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
