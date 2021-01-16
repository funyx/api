<?php

    namespace funyx\api;

    use atk4\data\Persistence;
    use Phalcon\Http\Request;
    use Phalcon\Logger;
    use ReflectionMethod;

    class Model
        extends
        \atk4\data\Model
    {
        /**
         * @var \funyx\api\App
         */
        protected App $app;
        protected Logger $logger;

        public string $api_id_field = '';

        public function __construct($persistence = null, $defaults = [])
        {
            $this->app = $GLOBALS['app'];
            /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
            $this->logger = $this->app->getService('logger');
            parent::__construct($persistence, $defaults);
            $this->api_id_field = $this->id_field;
        }

        public function fnInjector(string $fn_name, $id)
        {
            if (is_null($this->persistence)) {
                $this->persistence = Persistence::connect(getenv('DEFAULT_PERSISTENCE'));
            }
            $params = (new ReflectionMethod($this, $fn_name))->getParameters();
            $args = [];
            if (count($params)) {
                foreach ($params as $p) {
                    if ($p->hasType()) {
                        if ($pc = $p->getClass()) {
                            // handle mapper injection
                            $c = $pc->getName();
                            $r = null; // request cache
                            if (is_a($c, \funyx\api\Mapper::class, true)) {
                                $args[$p->getPosition()] = new $c(
                                    $this->app,
                                    $this->logger,
                                    $r ?? $r = new Request()
                                );
                            }
                            // handle authorization injection
                            if (is_a($c, \funyx\api\Auth::class, true)) {
                                $args[$p->getPosition()] = new $c(
                                    $this->app,
                                    $this->logger,
                                    $r ?? $r = new Request()
                                );
                            }
                        }
                    }
                }
            }
            if(!$this->_initialized){
                $this->init();
            }
            if(in_array($fn_name,['updateOne', 'getOne', 'removeOne'])){
                $this->loadBy($this->api_id_field,$id);
            }
            $return = $this->$fn_name(...$args);
            if ( !is_a($return, Response::class)) {
                (new Response())->json($return);
            } else {
                $return->json($return);
            }
            $GLOBALS['app']->getService('logger')
                ->info(json_encode(['response' => $return]));
        }

        public function paginatorHandler()
        {
            if ( !method_exists($this, 'paginator')) {
                $GLOBALS['app']->getService('logger')
                    ->error(json_encode(['api' => 'Not implemented']));
                return (new Response())->notImplemented();
            }
            return $this->fnInjector('paginator');
        }

        public function createOneHandler()
        {
            if ( !method_exists($this, 'createOne')) {
                $GLOBALS['app']->getService('logger')
                    ->error(json_encode(['api' => 'Not implemented']));
                return (new Response())->notImplemented();
            }
            return $this->fnInjector('createOne');
        }

        public function getOneHandler($id)
        {
            if ( !method_exists($this, 'getOne')) {
                $GLOBALS['app']->getService('logger')
                    ->error(json_encode(['api' => 'Not implemented']));
                return (new Response())->notImplemented();
            }
            return $this->fnInjector('getOne', $id);
        }

        public function updateOneHandler($id)
        {
            if ( !method_exists($this, 'updateOne')) {
                $GLOBALS['app']->getService('logger')
                    ->error(json_encode(['api' => 'Not implemented']));
                return (new Response())->notImplemented();
            }
            return $this->fnInjector('updateOne', $id);
        }

        public function removeOneHandler($id)
        {
            if ( !method_exists($this, 'removeOne')) {
                $GLOBALS['app']->getService('logger')
                    ->error(json_encode(['api' => 'Not implemented']));
                return (new Response())->notImplemented();
            }
            return $this->fnInjector('removeOne', $id);
        }
    }
