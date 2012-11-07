<?php

namespace Alvi\Bundle\ImageProcessor\ScalerBundle\Measurement;


/**
 * RabbitMQ API
 *
 * @author Vincent <vincentvanbeek@mac.com>
 */
class RabbitMQAPI
{
    private $rabbitMQ;
    
    /**
     * @param graphiteArray $graphite
     */
    
    //TODO: zookeeper api 
    //public function __construct(ZookeeperAPI $zkAPI)
    public function __construct()
    {
        $this->rabbitMQ = array("ip"=>"172.16.1.23", "username" => "guest", "password" => "guest", "port" => "55672");   
    }
    
    /**
     * return RabbitMQ url
     */
     private function getRabbitMQUrl() {
         return "http://".$this->rabbitMQ['ip'].":".$this->rabbitMQ['port']."/api/"; 
     }
     
     /**
      * return RabbitMQ context
      */
      private function getRabbitMQContext() {
          $context = stream_context_create(array('http' => array('header'  => "Authorization: Basic " . base64_encode($this->rabbitMQ['username'].":".$this->rabbitMQ['password']))));
          return $context;
      }
    
    /**
     * execute a command on the RabbitMQ REST API
     * @param $command
     * return array or false
     */
     public function executeApiCall($command) {
         $rabbitMQjsonResponse = @file_get_contents($this->getRabbitMQUrl().$command, false, $this->getRabbitMQContext());
         if($rabbitMQjsonResponse == false) {
             return false;
         }
         else {
             $responsArray = json_decode($rabbitMQjsonResponse, true);
             return $responsArray;
         }
     }
}    
?>