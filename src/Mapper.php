<?php
    declare(strict_types = 1);

    namespace funyx\api;

    use Phalcon\Http\Request;
    use Phalcon\Logger;

    /**
     * @method doMap()
     */
    class Mapper
    {
        /**
         * @var \funyx\api\App
         */
        protected App $app;
        /**
         * @var \Phalcon\Logger
         */
        protected Logger $logger;
        /**
         * @var \Phalcon\Http\Request
         */
        protected Request $request;
        public array $data;
        /**
         * @var mixed
         */
        protected $ctx;

        public function __construct(App $app, Logger $logger, Request $request)
        {
            $this->app = $app;
            $this->logger = $logger;
            $this->request = $request;
            if ( !method_exists($this, 'doMap')) {
                $logger->error('Mapper ('.get_class($this).') must implement protected function doMap');
            }
            $this->resetCtx($this->request->getJsonRawBody(true));
            $this->data = $this->doMap();
        }

        public function get($name)
        {
            return \JmesPath\Env::search($name,$this->ctx);
        }

        /**
         * @param mixed $ctx
         */
        public function setCtx($ctx): void
        {
            $this->ctx = $ctx;
        }

        /**
         * @param mixed $ctx
         */
        public function resetCtx($ctx): void
        {
            $this->ctx = $this->request->getJsonRawBody(true);
        }
    }
