<?php
    declare(strict_types = 1);

    namespace funyx\api;

    use funyx\api\Logger\LineFormat;
    use Phalcon\Di\DiInterface;
    use Phalcon\Logger;
    use Phalcon\Logger\Adapter\Stream;
    use Phalcon\Mvc\Micro;
    use Throwable;

    class App
        extends
        Micro
    {
        public function __construct( DiInterface $container = null )
        {
            parent::__construct($container);
            $request_uid = uniqid('req_',true);
            $this->setService('logger', function () use ($request_uid)
            {
                $formatter = new LineFormat($request_uid);
                $adapter = new Stream('php://stderr');
                $adapter->setFormatter($formatter);

                return new Logger('messages', [
                    'main' => $adapter,
                ]);
            });
            $this->getService('logger')
                ->info($_SERVER['REQUEST_METHOD'].' @ '.$_SERVER['REQUEST_URI']);
            $this->notFound(function ()
            {
                $this->getService('logger')
                    ->error('Not Found');
                (new Response())->notFound();
                return false;
            });
            $this->error(function ( Throwable $error )
            {
                switch (get_class($error)) {
                    case \funyx\api\Exception::class:
                        $this->getService('logger')
                            ->error('RES : '.json_encode($error->getParams()));
                        $error_data = $error->getParams();
                        break;
                    case \atk4\data\Exception::class:
                        $p = $error->getParams();
                        switch ($error->getMessage()):
                            case 'Field is not defined in model':
                                $error_data = ['api' => 'Field "'.$p['field'].'" is not available in model "'.$p['model']->caption.'"'];
                                $this->getService('logger')
                                    ->error('RES : '.json_encode($error_data));
                                (new Response())->error($error_data);
                                die();
                                break;
                        endswitch;
                        switch ($error->getCode()):
                            case 404:
                                $this->getService('logger')
                                    ->error('Not Found');
                                (new Response())->notFound();
                                die();
                                break;
                        endswitch;
                        $error_data = $error->getMyTrace();
                        break;
                    case \atk4\dsql\ExecuteException::class:
                        $error_data = ['dsql' => $error->getParams()['error']];
                        $this->getService('logger')
                            ->error('RES : '.json_encode($error_data));
                        break;
                    default :
                        $error_data = [
                            get_class($error) => $error->getMessage()
                        ];
                        $this->getService('logger')
                            ->error('RES : '.json_encode($error_data));
                }
                (new Response())->error($error_data);
                die();
            });
        }

        /**
         * $spot values
         * before - before execute the route
         * afterBinding - after model binding
         * after - after execute the route
         * finish - when the request is finished
         *
         * @param string   $spot
         * @param callable $middleware
         *
         * @return mixed
         */
        public function use( string $spot, callable $middleware )
        {
            switch ($spot):
                case 'before' :
                case 'afterBinding' :
                case 'after' :
                case 'finish' :
                    return $this->{$spot}(call_user_func($middleware, $this));
            endswitch;
        }
    }
