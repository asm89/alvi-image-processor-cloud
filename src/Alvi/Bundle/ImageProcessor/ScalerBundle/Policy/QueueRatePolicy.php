<?php

namespace Alvi\Bundle\ImageProcessor\ScalerBundle\Policy;

use Alvi\Bundle\ImageProcessor\ScalerBundle\Measurement\QueueMeasurement;
use Alvi\Bundle\ImageProcessor\ProvisionerBundle\VirtualMachineManager;

/**
 * Retrieve measurements for the Scaler policy
 *
 * @author Vincent <vincentvanbeek@mac.com>
 */
class QueueRatePolicy
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
        $this->parameters = $pp->getParameters('queueratepolicy');
        $this->queueMeasurement = $qm;
        $this->virtualMachineManager = $vmm;
    }

    /**
     * tell the provisioner manager to scale up or down. 
     */
    public function policyDecision() {
        
        $incomingRate = $this->queueMeasurement->getMovingAverageIncomingJobRate();
        $consumingRate = $this->queueMeasurement->getMovingAverageConsumingJobRate();
        
        //if no workers are up, spin one up
        if(($this->virtualMachineManager->getSpinningUpCount("worker")+ $this->virtualMachineManager->getRunningCount("worker") + $this->virtualMachineManager->getPreparingCount("worker")) < 1) {
            $this->virtualMachineManager->start("worker");
        }

        //if data is available do policy queue rate
        if(($incomingRate == '0' || $incomingRate >= 1) && ($consumingRate == '0' || $consumingRate >= 1)) {
            //if the incoming rate is larger than the consuming rate spin up
            if ($incomingRate > $consumingRate && abs($incomingRate - $consumingRate) > 1) {
                //scale up
                //number of workers that can spin up at the same time
                if(($this->virtualMachineManager->getSpinningUpCount("worker") + $this->virtualMachineManager->getPreparingCount("worker")) < $this->parameters['spinupcap']) {
                    $this->virtualMachineManager->start("worker");
                }
            }
            //if the incoming rate is smaller than the consuming rate and there are more than 1 workers, spin down a worker
            else if ($incomingRate < $consumingRate && abs($incomingRate - $consumingRate) > 1 && ($this->virtualMachineManager->getSpinningUpCount("worker")+ $this->virtualMachineManager->getRunningCount("worker") + $this->virtualMachineManager->getPreparingCount("worker")) > 1) {
                //scale down
                //number of workers that can spin down at the same time
                if($this->virtualMachineManager->getSpinningDownCount("worker") <= $this->parameters['spindowncap'] && $this->virtualMachineManager->getRunningCount("worker") != 0) {
                    sleep(60);
                    $this->virtualMachineManager->stop("worker");
                }
            }
        }
        else {
            //do nothing
        }
    }
}
