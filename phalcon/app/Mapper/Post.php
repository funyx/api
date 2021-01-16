<?php
    declare(strict_types = 1);

    namespace demo\api\Mapper;

    use funyx\api\Mapper;

    class Post
        extends
        Mapper
    {
        public function doMap()
        {
            return [
                'author' => $this->get('alias_name'),
                'content' => $this->get('content')
            ];
        }
    }
