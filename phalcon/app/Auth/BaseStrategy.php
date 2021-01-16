<?php
declare(strict_types=1);

namespace demo\Auth;

trait BaseStrategy
{
    public string $base_auth_field = 'username';
    public string $base_auth_key_field = 'password';

    public function baseAuth(string $str): array
    {
        list($$this->base_auth_field, $$this->base_auth_key_field) = explode(':',base64_decode($str));
        $this->addCondition($this->base_auth_field,$$this->base_auth_field);
        $this->addCondition($this->base_auth_key_field,$$this->base_auth_key_field);
        return $this->tryLoadAny()->get();
    }
}
