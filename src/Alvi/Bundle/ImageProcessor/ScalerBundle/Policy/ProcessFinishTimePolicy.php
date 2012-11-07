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
    private $parameters;
    
    /**
     * @param ProcessFinishTimeMeasurement $pftm
     * @param VirtualMachineManager $vmm
     */
    public function __construct(PolicyParameters $pp, ProcessFinishTimeMeasurement $pftm, VirtualMachineManager $vmm)
    {
        $this->parameters = $pp->getParameters('timepolicy');
        $this->processFinishTimeMeasurement = $pftm;
        $this->virtualMachineManager = $vmm;
    }

    /**
     * tell the provisioner manager to scale up or down. 
     */
    public function policyDecision() {
        
        $averageFinishTime = $this->processFinishTimeMeasurement->getMovingAverageFinishTime();
        $averageProcessTime = $this->processFinishTimeMeasurement->getMovingAverageProcessTime();
        
        //if no workers are up, spin one up
        if(($this->virtualMachineManager->getSpinningUpCount("worker")+ $this->virtualMachineManager->getRunningCount("worker") + $this->virtualMachineManager->getPreparingCount("worker")) < 1) {
            $this->virtualMachineManager->start("worker");
        }
        
        //if no data is available do nothing
        if($averageFinishTime == false || $averageFinishTime == false) {
            //do nothing
        }
        else {
            //if the finish time is more than 5 times the process time scale up
            if ($averageFinishTime/$averageProcessTime > $this->parameters['spinupratio']) {
                //scale up
                if(($this->virtualMachineManager->getSpinningUpCount("worker") + $this->virtualMachineManager->getPreparingCount("worker")) < $this->parameters['spinupcap']) {
                    $this->virtualMachineManager->start("worker");
                }
            }
            //if the finish time is less than 2 times the process time and there are more than 1 workers scale down
            elseif ($averageFinishTime/$averageProcessTime < $this->parameters['spindownratio']  && ($this->virtualMachineManager->getSpinningUpCount("worker")+ $this->virtualMachineManager->getRunningCount("worker") + $this->virtualMachineManager->getPreparingCount("worker")) > 1) {
                //scale down
                if($this->virtualMachineManager->getSpinningDownCount("worker") < $this->parameters['spindowncap']) {
                    $this->virtualMachineManager->stop("worker");
                    sleep(60);
                }
            }
        }
    }
}