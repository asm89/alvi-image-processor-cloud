<?php

namespace Alvi\Bundle\ImageProcessor\ScalerBundle\Policy;

use Alvi\Bundle\ImageProcessor\ProvisionerBundle\VirtualMachineManager;

/**
 * Retrieve measurements for the Scaler policy
 *
 * @author Vincent <vincentvanbeek@mac.com>
 */
class ConstantSizePolicy
{
    private $virtualMachineManager;
    private $parameters;
    
    /**
     * @param VirtualMachineManager $vmm
     */
    public function __construct(PolicyParameters $pp, VirtualMachineManager $vmm)
    {
        $this->virtualMachineManager = $vmm;
        $this->parameters = $pp->getParameters('constantsizepolicy');
    }

    /**
     * tell the provisioner manager to scale up or down. 
     */
    public function policyDecision() {
        //scale up if less than 5 workers
        if($this->virtualMachineManager->getRunning("worker") < $this->parameters['maxnumberofworkers']) {
            //workers that can spin up at the same time
            if($this->virtualMachineManager->getSpinningUp("worker") < $this->parameters['spinupcap']) {
                $this->virtualMachineManager->start("worker");
            }
        }
    }
}