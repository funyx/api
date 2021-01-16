<?php
    declare(strict_types = 1);

    namespace funyx\api;

    use Phalcon\Mvc\Micro\Collection as PCollection;

    class Collection
        extends
        PCollection
    {
        /**
         * @var string
         */
        private string $model;

        public function __construct($path, string $model)
        {
            if(is_string($path)){
                $s = $path;
                $m = $path;
            }elseif(is_array($path)){
                $s = $path[0];
                $m = $path[1];
            }
            $this->model = $model;
            $this->setLazy(true);
            $this->get("/{$m}", 'paginatorHandler', 'list');
            $this->post("/{$s}", 'createOneHandler', 'create');
            $this->get("/{$s}/{id_field}", 'getOneHandler', 'read');
            $this->put("/{$s}/{id_field}", 'updateOneHandler', 'update');
            $this->delete("/{$s}/{id_field}", 'removeOneHandler', 'delete');
        }

        public function getHandler(): string
        {
            return $this->model;
        }
    }
