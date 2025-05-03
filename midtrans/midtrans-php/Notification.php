<?php
namespace Midtrans;
use Exception;

class Notification
{
    private $response;

    public function __construct()
    {
        $input = file_get_contents('php://input');
        $this->response = json_decode($input, true);
        
        if(!$this->response) {
            throw new Exception('Invalid notification data');
        }
    }

    public function __get($name)
    {
        return $this->response[$name] ?? null;
    }
}
?>