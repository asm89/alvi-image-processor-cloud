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
        
        $incommingRate = $this->queueMeasurement->getMovingAverageIncomingJobRate();
        $consumingRate = $this->queueMeasurement->getMovingAverageConsumingJobRate();
        
        
        //if no workers are up, spin one up
        if(($this->virtualMachineManager->getSpinningUpCount("worker")+ $this->virtualMachineManager->getRunningCount("worker") + $this->virtualMachineManager->getPreparingCount("worker")) < 1) {
            $this->virtualMachineManager->start("worker");
        }

        //if data is available do policy queue rate
        if(($incommingRate == '0' || $incommingRate >= 1) && ($consumingRate == '0' || $consumingRate >= 1)) {
            //if the incomming rate is larger than the consuming rate spin up
            if ($incommingRate > $consumingRate) {
                //scale up
                //number of workers that can spin up at the same time
                if(($this->virtualMachineManager->getSpinningUpCount("worker") + $this->virtualMachineManager->getPreparingCount("worker")) < $this->parameters['spinupcap']) {
                    $this->virtualMachineManager->start("worker");
                }
            }
            //if the incomming rate is smaller than the consuming rate and there are more than 1 workers, spin down a worker
            elseif ($incommingRate < $consumingRate && ($this->virtualMachineManager->getSpinningUpCount("worker")+ $this->virtualMachineManager->getRunningCount("worker") + $this->virtualMachineManager->getPreparingCount("worker")) > 1) {
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