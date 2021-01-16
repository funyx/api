<?php
    declare(strict_types = 1);

    namespace funyx\api;

    use Phalcon\Http\Response as PhalconResponse;

    class Response
        extends
        PhalconResponse
    {
        public function json($data)
        {
            $this->setStatusCode(200);
            $this->setJsonContent([
                'status' => 'OK',
                'data' => $data,
                'error' => null
            ]);
            $this->send();
        }

        public function notFound()
        {
            $this->setStatusCode(404);
            $this->setJsonContent([
                'status' => 'NOT_FOUND',
                'data' => null,
                'error' => null
            ]);
            $this->send();
        }

        public function notImplemented()
        {
            $this->error(['api' => 'Not implemented']);
        }

        /**
         * @param array|null $dictionary - [err::class = err->getMessage ]
         * @param int        $code
         * @param string     $msg
         */
        public function error(array $dictionary = null, int $code = 500, string $msg = 'ERROR')
        {
            $this->setStatusCode($code);
            $this->setJsonContent([
                'status' => $msg,
                'data' => null,
                'error' => $dictionary
            ]);
            $this->send();
        }
    }
