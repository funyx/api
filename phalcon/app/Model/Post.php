<?php
declare(strict_types=1);

namespace demo\api\Model;

use demo\api\Mapper\Post as Payload;
use funyx\api\Model;

class Post extends Model
{
    /**
     * @var string
     */
    public $table = 'post';
    public $caption = 'Post';
    public function init(): void
    {
        parent::init();
        $this->addField('content');
        $this->addField('author');
    }

    // model should be loaded
    public function getOne(){
        return $this->get();
    }

    public function createOne(Payload $payload)
    {
        return $this->save($payload->data)->get();
    }

    // model should be loaded
    public function updateOne(Payload $payload)
    {
        return $this->save($payload->data)->get();
    }
}
