<?php
namespace Ham0mer\Afdian;

class HttpResponse
{

    public function __construct($status, $headers, $data)
    {
        $this->status = $status;
        $this->headers = $headers;
        $this->data = $data;
    }
}
