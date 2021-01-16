<?php
    declare(strict_types = 1);

    namespace funyx\api;

    use atk4\data\Model;
    use Phalcon\Http\Request;
    use Phalcon\Logger;

    /**
     * @method doAuth()
     */
    class Auth
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
        /**
         * @var \atk4\data\Model
         */
        public Model $user;

        /**
         * Authorization is http-headers based algorithm so pass all the headers to
         * the `doAuth` method IF requested
         *
         * @param \funyx\api\App        $app
         * @param \Phalcon\Logger       $logger
         * @param \Phalcon\Http\Request $request
         */
        public function __construct(App $app, Logger $logger, Request $request)
        {
            $this->app = $app;
            $this->logger = $logger;
            $this->request = $request;
            if ( !method_exists($this, 'doAuth')) {
                $logger->error('Authorization ('.get_class($this).') must implement protected function doAuth');
            }
            $this->user = $this->doAuth();
        }
    }
