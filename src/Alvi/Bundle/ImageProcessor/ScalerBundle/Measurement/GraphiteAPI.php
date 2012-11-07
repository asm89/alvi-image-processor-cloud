<?php

namespace Alvi\Bundle\ImageProcessor\ScalerBundle\Measurement;


/**
 * Retrieve measurements for the Scaler policy
 *
 * @author Vincent <vincentvanbeek@mac.com>
 */
class GraphiteAPI
{
    private $graphite;
    private $renderUrl;
    
    /**
     * @param graphiteArray $graphite
     */
    
    //TODO: zookeeper api 
    //public function __construct(ZookeeperAPI $zkAPI)
    public function __construct()
    {
        //get graphite from Zookeeper Graphite server
        //$this->graphite = unserialize($zkAPI->get("/Grpahite"));
        $this->graphite = array("IP"=>"172.16.1.23");
        $this->renderUrl = "http://".$this->graphite['IP']."/render";
    }
    
    /**
     * execute a command on the graphite REST API
     * @param graphiteCommand $command
     */
     public function getDataFromGraphiteCommand($command) {
        try {
            $jsonRespons = json_decode(file_get_contents($this->renderUrl.$command));
        }
        catch(Exception $e) {
            //command or server not found
            $jsonRespons = false;
        }
        return $jsonRespons;
     }
}    
?>