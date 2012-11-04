<?php

namespace Alvi\Bundle\ImageProcessor\ScalerBundle\Policy;

use Alvi\Bundle\ImageProcessor\ScalerBundle\Measurement\QueueMeasurement;
use Alvi\Bundle\ImageProcessor\ProvisionerBundle\VirtualMachineManager;

/**
 * Retrieve measurements for the Scaler policy
 *
 * @author Vincent <vincentvanbeek@mac.com>
 */
class QueueSizePolicy
{
    
    private $queueMeasurement;
    private $virtualMachineManager;
    private $parameters;
    
    /**
     * @param ProcessFinishTimeMeasurement $pftm
     * @param VirtualMachineManager $vmm
     */
    public function __construct(PolicyParameters $pp, QueueMeasurement $qm, VirtualMachineManager $vmm)
    {
        $this->parameters = $pp->getParameters('queuesizepolicy');
        $this->queueMeasurement = $qm;
        $this->virtualMachineManager = $vmm;
    }

    /**
     * tell the provisioner manager to scale up or down. 
     */
    public function policyDecision() {
        
        $queueSize = $this->queueMeasurement->getMovingAverageQueueSize();


        //if no data is available do nothing
        if($queueSize == false) {
            //do nothing
        }
        else {
            //if the queue size is larger than 'spinupqueuesize' jobs scale up
            if ($queueSize > $this->parameters['spinupqueuesize']) {
                //scale up
                //number of workers that can spin up at the same time
                if($this->virtualMachineManager->getSpinningUpCount("worker") <= $this->parameters['spinupcap']) {
                    $this->virtualMachineManager->start("worker");
                }
            }
            //if the queue size is less than 'spindownqueuesize' spin down a worker
            elseif ($averageFinishTime/$averageProcessTime < $this->parameters['spindownqueuesize']) {
                //scale down
                //number of workers that can spin down at the same time
                if($this->virtualMachineManager->getSpinningDownCount("worker") <= $this->parameters['spindowncap']) {
                    $this->virtualMachineManager->stop("worker");
                }
            }
        }
    }
}