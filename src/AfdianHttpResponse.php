<?php
namespace Ham0er\Afdian;

class AfdianHttpResponse
{

    public function __construct($status, $headers, $data)
    {
        $this->status = $status;
        $this->headers = $headers;
        $this->data = $data;
    }
}