<?php

use demo\api\Post;
use funyx\api\App;
use funyx\api\Data\AccountUser\AccountUserAuth;
use funyx\api\Middleware\Authorization;
use funyx\api\Middleware\CORSMiddleware;
use funyx\api\Middleware\RequestMiddleware;
use funyx\api\Middleware\ResponseMiddleware;

require_once __DIR__.'/constants.php';
require_once __DIR__.'/../vendor/autoload.php';
$app = new App([
	'authorization' => [
		'jwt' => [
			'secret' => 'my-secret',
			'algorithm' => ['HS256'],
			'valid_for' => '+ 30 minutes',
//			'valid_after' => '+ 1 minute'
		]
	],
	'database'      => [
		'dsn' => 'sqlite:.personal_data/data.sqlite3'
	],
	'mws'           => [
		[
			CORSMiddleware::class,
			'event' => 'before'
		],
		[
			Authorization\JWT::class,
			'event' => 'before'
		],
		[
			RequestMiddleware::class,
			'event' => 'before'
		],
		[
			ResponseMiddleware::class,
			'event' => 'after'
		]
	],
	'routes'        => [
		[
			'POST',
			'/auth/register',
			AccountUserAuth::class.'::register'
		],
		[
			'POST',
			'/auth/login',
			AccountUserAuth::class.'::login'
		],
		[
			'GET',
			'/auth/me',
			AccountUserAuth::class.'::getMe'
		],
		[
			'GET',
			'/posts',
			Post::class.'::filterList'
		],
		[
			'POST',
			'/post',
			Post::class.'::createOne'
		],
		[
			'GET',
			'/post/{id}',
			Post::class.'::getOne'
		],
		[
			'PUT',
			'/post/{id}',
			Post::class.'::updateOne'
		],
		[
			'DELETE',
			'/post/{id}',
			Post::class.'::deleteOne'
		]
	]
]);
$app->handle($_SERVER["REQUEST_URI"]);
