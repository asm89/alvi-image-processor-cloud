<?php
namespace Alvi\Bundle\ImageProcessor\ScalerBundle\Policy;

/**
 * Policy parameters
 *
 * @author Vincent <vincentvanbeek@mac.com>
 */
class PolicyParameters
{
    private $policyParameters;
    
    /**
     * Set the parameters
     */
    public function __construct() {
        
        //paramters for constant size policy
        $policyParameters['constantsizepolicy'] = array(      "maxnumberofworkers"    => 5,   //max number of workers
                                                        "spinupcap"             => 3);  //number of workers that can spin up at the same time
        //paramters for process/finish time policy
        $policyParameters['timepolicy'] = array(        "spinupratio"           => 5,   // finishtime/processtime
                                                        "spindownratio"         => 2,   // finishtime/processtime
                                                        "spinupcap"             => 3,  //number of workers that can spin up at the same time
                                                        "spindowncap"           => 3);  //number of workers that can spin down at the same time
        //paramters for queue size policy
        $policyParameters['queuesizepolicy'] = array(   "spinupqueuesize"       => 100, // number of items in queue
                                                        "spindownqueuesize"     => 10,  // number of items in queue
                                                        "spinupcap"             => 3,  //number of workers that can spin up at the same time
                                                        "spindowncap"           => 3);  //number of workers that can spin down at the same time
    }

    /**
     * @param array with paramters
     * return parameters of a policy
     */
    public function getParameters($policy) {
        return $this->policyParameters[$policy];
    }
}