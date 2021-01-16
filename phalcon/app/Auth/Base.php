<?php
declare(strict_types=1);

namespace demo\Auth;

use atk4\data\Persistence;
use funyx\api\Auth;

class Base extends Auth
{
    public function doAuth()
    {
        $auth_header = $this->request->getHeader('Authorization');
        $basic = str_replace('Basic ','', $auth_header);
        $m = new User(Persistence::connect(getenv('DEFAULT_PERSISTENCE')));
        $this->user = $m->baseAuth($this->app->getService('auth')->basicAuth($basic));
    }
}
