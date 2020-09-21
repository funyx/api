<?php
declare(strict_types=1);

namespace funyx\api\Model;

use funyx\api\Model;
use funyx\api\Response;
use Phalcon\Http\Request;

class Post extends Model
{
    /**
     * @var string
     */
    public $table = 'post';
    public $caption = 'Post';
    public function init(): void
    {
        $this->addField('content');
        $this->addField('author');
        parent::init();
        return;
    }

    public function getOne($id){
        $this->tryLoad($id);
        if(!$this->loaded()){
            $this->logger->info('NOT FOUND');
            return (new Response())->notFound();
        }
        $data = $this->get();
        $this->logger->info(json_encode($data));
        return (new Response())->json($data);
    }

    public function createOne()
    {
        $payload = (new Request())->getJsonRawBody(true);
        $this->save($payload);
        return $this->get();
    }
}
