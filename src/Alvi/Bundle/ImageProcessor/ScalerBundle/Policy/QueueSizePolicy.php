<?php

namespace Alvi\Bundle\ImageProcessor\ScalerBundle\Policy;

use Alvi\Bundle\ImageProcessor\ScalerBundle\Measurement\QueueSizeMeasurement;
use Alvi\Bundle\ImageProcessor\ProvisionerBundle\VirtualMachineManager;

/**
 * Retrieve measurements for the Scaler policy
 *
 * @author Vincent <vincentvanbeek@mac.com>
 */
class QueueSizePolicy
{
    
    private $queueSizeMeasurement;
    private $virtualMachineManager;
    
    /**
     * @param ProcessFinishTimeMeasurement $pftm
     * @param VirtualMachineManager $vmm
     */
    public function __construct(QueueSizeMeasurement $qsm, VirtualMachineManager $vmm)
    {
        $this->queueSizeMeasurement = $qsm;
        $this->virtualMachineManager = $vmm;
    }

    /**
     * tell the provisioner manager to scale up or down. 
     */
    public function policyDecision() {
        
        $queueSize = $this->queueSizeMeasurement->getMovingAverageQueueSize();


        //if no data is available do nothing
        if($queueSize == false) {
            //do nothing
        }
        else {
            //if the queue size is larger than 100 jobs scale up
            if ($queueSize > 100) {
                //scale up
                //number of workers that can spin up at the same time
                if($this->virtualMachineManager->getSpinningUp("worker") <= 2) {
                    $this->virtualMachineManager->start("worker");
                }
            }
            //if the finish time is less than 2 times the process time scale down
            elseif ($averageFinishTime/$averageProcessTime < 20) {
                //scale down
                //number of workers that can spin down at the same time
                if($this->virtualMachineManager->getSpinningDown("worker") <= 2) {
                    $this->virtualMachineManager->stop("worker");
                }
            }
        }
    }
}