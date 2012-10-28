<?php

namespace Alvi\Bundle\ImageProcessor\ScalerBundle\Policy;

use Alvi\Bundle\ImageProcessor\ScalerBundle\Measurement\ProcessFinishTimeMeasurement;
use Alvi\Bundle\ImageProcessor\ProvisionerBundle\VirtualMachineManager;

/**
 * Retrieve measurements for the Scaler policy
 *
 * @author Vincent <vincentvanbeek@mac.com>
 */
class ProcessFinishTimePolicy
{
    
    private $processFinishTimeMeasurement;
    private $virtualMachineManager;
    
    /**
     * @param ProcessFinishTimeMeasurement $pftm
     * @param VirtualMachineManager $vmm
     */
    public function __construct(ProcessFinishTimeMeasurement $pftm, VirtualMachineManager $vmm)
    {
        $this->processFinishTimeMeasurement = $pftm;
        $this->virtualMachineManager = $vmm;
    }

    /**
     * tell the provisioner manager to scale up or down. 
     */
    public function policyDecision() {
        
        $averageFinishTime = $this->processFinishTimeMeasurement->getMovingAverageFinishTime();
        $averageProcessTime = $this->processFinishTimeMeasurement->getMovingAverageProcessTime();

        //if no data is available do nothing
        if($averageFinishTime == false || $averageFinishTime == false) {
            //do nothing
        }
        else {
            //if the finish time is more than 5 times the process time scale up
            if ($averageFinishTime/$averageProcessTime > 5) {
                //scale up
                if($this->virtualMachineManager->getSpinningUp("worker") <= 2) {
                    $this->virtualMachineManager->start("worker");
                }
            }
            //if the finish time is less than 2 times the process time scale down
            elseif ($averageFinishTime/$averageProcessTime < 2) {
                //scale down
                if($this->virtualMachineManager->getSpinningDown("worker") <= 2) {
                    $this->virtualMachineManager->stop("worker");
                }
            }
        }
    }
}