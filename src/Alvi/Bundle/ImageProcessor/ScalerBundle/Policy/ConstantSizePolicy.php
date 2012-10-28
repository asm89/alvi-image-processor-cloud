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
    
    /**
     * @param VirtualMachineManager $vmm
     */
    public function __construct(VirtualMachineManager $vmm)
    {
        $this->virtualMachineManager = $vmm;
    }

    /**
     * tell the provisioner manager to scale up or down. 
     */
    public function policyDecision() {
        //scale up if less than 5 workers
        if($this->virtualMachineManager->getRunning("worker") < 5) {
            $this->virtualMachineManager->start("worker");
        }
    }
}