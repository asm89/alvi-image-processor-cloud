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
        
        
        //if no workers are up, spin one up
        if(($this->virtualMachineManager->getSpinningUpCount("worker")+ $this->virtualMachineManager->getRunningCount("worker") + $this->virtualMachineManager->getPreparingCount("worker")) < 1) {
            $this->virtualMachineManager->start("worker");
        }

        //if no data is available do nothing
        if($queueSize == '0' || $queueSize >= 1) {
            //if the queue size is larger than 'spinupqueuesize' jobs scale up
            if ($queueSize > $this->parameters['spinupqueuesize']) {
                //scale up
                //number of workers that can spin up at the same time
                if(($this->virtualMachineManager->getSpinningUpCount("worker") + $this->virtualMachineManager->getPreparingCount("worker")) < $this->parameters['spinupcap']) {
                    $this->virtualMachineManager->start("worker");
                }
            }
            //if the queue size is less than 'spindownqueuesize' and there are more than 1 workers, spin down a worker
            elseif ($queueSize < $this->parameters['spindownqueuesize'] && ($this->virtualMachineManager->getSpinningUpCount("worker")+ $this->virtualMachineManager->getRunningCount("worker") + $this->virtualMachineManager->getPreparingCount("worker")) > 1) {
                //scale down
                //number of workers that can spin down at the same time
                if($this->virtualMachineManager->getSpinningDownCount("worker") <= $this->parameters['spindowncap'] && $this->virtualMachineManager->getRunningCount("worker") != 0) {
                    $this->virtualMachineManager->stop("worker");
                    sleep(60);
                }
            }
        }
        else {
            //do nothing
        }
    }
}