<?php
use Phalcon\Logger\Adapter\Stream;

require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/../vendor/autoload.php';

$app = new \funyx\api\App();
$posts = new \funyx\api\Collection('posts', \funyx\api\Model\Post::class);
$app->mount($posts);
$app->handle($_SERVER["REQUEST_URI"]);
