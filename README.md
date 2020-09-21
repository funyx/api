Simple rest API using :
* [atk4/data](https://github.com/atk4/data)
* [Phalcon Micro](https://docs.phalcon.io/4.0/en/application-micro)

Requirements : 
* php ^7.4
* [php-json *](https://pecl.php.net/package/json)
* [php-phalcon 4.0](https://pecl.php.net/package/phalcon)


```php
$app = new \funyx\api\App();
$posts = new \funyx\api\Collection('posts', \your\atk4\model\Post::class);
$app->mount($posts);
$app->handle($_SERVER["REQUEST_URI"]);
```
in your model
```
namespace \your\atk4\model;

class Post extends \funyx\api\Model
{
    // if a function is not defined in model a NOT IMPLEMENTED error will be thrown
    public function paginator() // will be GET /posts
    {}

    public function createOne() {} // will be POST /posts
    {}

    public function getOne($id) {} // will be GET /posts/1
    {}

    public function updateOne($id) // will be PUT /posts/1
    {
        $payload = (new Request())->getJsonRawBody(true);
        $this->load($id)->save($payload);
        return (new Response())->json($this->get());
    }
    
    public function removeOne($id) // will be DELETE /posts/1
    {}
}
```
