Simple rest API using :
* [atk4/data](https://github.com/atk4/data)
* [Phalcon Micro](https://docs.phalcon.io/4.0/en/application-micro)

Requirements : 
* php ^7.4
* [php-json *](https://pecl.php.net/package/json)
* [php-phalcon 4.0](https://pecl.php.net/package/phalcon)


```php
$app = new \funyx\api\App([
	'authorization' => [
		'jwt' => [
			'secret' => 'my-secret',
			'algorithm' => ['HS256'],
			'valid_for' => '+ 10 minutes',
//			'valid_after' => '+ 1 minute'
		]
	],
	'database'      => [
		'dsn' => 'sqlite:.personal_data/data.sqlite3'
	],
	'mws'           => [
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
			Post::class.'::paginator'
		],
		[
			'POST',
			'/post',
			Post::class.'::createOne'
		],
		[
			'GET',
			'/post/{:id}',
			Post::class.'::getOne'
		],
		[
			'PUT',
			'/post/{:id}',
			Post::class.'::updateOne'
		],
		[
			'DELETE',
			'/post/{:id}',
			Post::class.'::deleteOne'
		]
	]
]);
$app->handle($_SERVER["REQUEST_URI"]);
```

test it locally with:
```bash
composer start
```
or with live reloading local server - [nodemon](https://nodemon.io/)
```bash
composer start-live
```

# links
- [x] https://docs.konghq.com/2.1.x/getting-started/enabling-plugins/

# todo 
- [ ] implement authorisation middleware
- [ ] implement user token table and check in the middleware for blacklisted tokens
- [ ] implement user roles
- [ ] implement user permissions
