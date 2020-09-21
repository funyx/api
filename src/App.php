<?php
declare(strict_types=1);

namespace funyx\api;
use Phalcon\Di\DiInterface;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\Stream;
use Phalcon\Mvc\Micro;
use Throwable;

class App extends Micro
{
    public function __construct(DiInterface $container = null)
    {
        parent::__construct($container);
        $this->setService('logger',function(){
            return new Logger('messages', [
                'main' => new Stream('php://stderr'),
            ]);
        });
        $this->getService('logger')->info('REQ : '.$_SERVER['REQUEST_URI']);
        $this->notFound(function (){
            $this->getService('logger')->error('Not Found');
            (new Response())->notFound();
            return false;
        });
        $this->error(function (Throwable $error){
            switch(get_class($error)){
                case \atk4\data\Exception::class:
                    $p = $error->getParams();
                    switch ($error->getMessage()):
                        case 'Field is not defined in model':
                            $error_data = ['api' => 'Field "'. $p['field'] .'" is not available in model "'.$p['model']->caption.'"'];
                            $this->getService('logger')->error('RES : '.json_encode($error_data));
                            break;
                        default:
                            $error_data = $error->getMyTrace();
                            break;
                    endswitch;
                    break;
                case \atk4\dsql\ExecuteException::class:
                    $error_data = ['dsql' => $error->getParams()['error']];
                    $this->getService('logger')->error('RES : '.json_encode($error_data));
                    break;
                default :
                    $error_data = [
                        get_class($error) => $error->getMessage()
                    ];
                    $this->getService('logger')->error('RES : '.json_encode($error_data));
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
    public function use(string $spot, Callable $middleware)
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
