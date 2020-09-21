<?php
declare(strict_types=1);

namespace funyx\api;

use Phalcon\Mvc\Micro\Collection as PCollection;

class Collection extends PCollection
{
    /**
     * @var string
     */
    private string $path;
    /**
     * @var string
     */
    private string $model;

    public function __construct(string $path, string $model)
    {
        $this->path = $path;
        $this->model = $model;
        $this->setPrefix("/{$path}");
        $this->setLazy(true);
        $this->get('', 'paginator', 'list');
        $this->post('', 'createOne', 'create');
        $this->get('/{id_field}', 'getOne', 'read');
        $this->put('/{id_field}', 'updateOne', 'update');
        $this->delete('/{id_field}', 'removeOne', 'delete');
    }

    public function getHandler() : string
    {
        return $this->model;
    }
}
