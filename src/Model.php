<?php

    namespace funyx\api;

    use atk4\data\Persistence;
    use Phalcon\Logger;

    class Model extends \atk4\data\Model
    {
        /**
         * @var \funyx\api\App
         */
        protected App $app;
        protected Logger $logger;

        public function __construct($persistence = null, $defaults = [])
        {
            if (is_null($persistence)) {
                $persistence = Persistence::connect(getenv('DEFAULT_PERSISTENCE'));
            }
            $this->app = $GLOBALS['app'];
            $this->logger = $this->app->getService('logger');
            parent::__construct($persistence, $defaults);
        }

        public function paginator()
        {
            $e = ['api' => 'Not implemented'];
            $GLOBALS['app']->getService('logger')->error(json_encode($e));
            return (new Response())->error($e);
        }

        public function createOne()
        {
            $e = ['api' => 'Not implemented'];
            $GLOBALS['app']->getService('logger')->error(json_encode($e));
            return (new Response())->error($e);
        }

        public function getOne($id)
        {
            $e = ['api' => 'Not implemented'];
            $GLOBALS['app']->getService('logger')->error(json_encode($e));
            return (new Response())->error($e);
        }

        public function updateOne($id,$data)
        {
            $e = ['api' => 'Not implemented'];
            $GLOBALS['app']->getService('logger')->error(json_encode($e));
            return (new Response())->error($e);
        }

        public function removeOne($id)
        {
            $e = ['api' => 'Not implemented'];
            $GLOBALS['app']->getService('logger')->error(json_encode($e));
            return (new Response())->error($e);
        }
    }
