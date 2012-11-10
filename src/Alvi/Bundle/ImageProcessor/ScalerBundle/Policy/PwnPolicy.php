<?php

namespace Alvi\Bundle\ImageProcessor\ScalerBundle\Policy;

use Alvi\Bundle\ImageProcessor\ScalerBundle\Measurement\QueueMeasurement;
use Alvi\Bundle\ImageProcessor\ProvisionerBundle\VirtualMachineManager;

/**
 * Retrieve measurements for the Scaler policy
 *
 * @author Vincent <vincentvanbeek@mac.com>
 */
class PwnPolicy
{
    private $queueMeasurement;
    private $virtualMachineManager;

    private $previousQueueSize;

    /**
     * @param ProcessFinishTimeMeasurement $pftm
     * @param VirtualMachineManager $vmm
     */
    public function __construct(QueueMeasurement $qm, VirtualMachineManager $vmm)
    {
        $this->queueMeasurement = $qm;
        $this->virtualMachineManager = $vmm;
    }

    /**
     * tell the provisioner manager to scale up or down.
     */
    public function policyDecision() 
    {
        $spinupCount = $this->virtualMachineManager->getSpinningUpCount("worker");
        $preparingCount = $this->virtualMachineManager->getPreparingCount("worker");
        $runningCount = $this->virtualMachineManager->getRunningCount("worker");
        $spindownCount = $this->virtualMachineManager->getSpinningDownCount("worker");

        $totalComing = $spinupCount + $preparingCount;
        $total = $spinupCount + $preparingCount + $runningCount;

        if ($total == 0) {
            $this->virtualMachineManager->start("worker");

            return;
        }

        // queue size over time
        $queueSize = $this->queueMeasurement->getQueueSize();

        if (false === $queueSize) {
            return;
        }

        // no previous measurement, or queue to small to make a decision
        if (null === $this->previousQueueSize || 0 === $this->previousQueueSize) {
            $this->previousQueueSize = $queueSize;

            return;
        }

        $ratio = $queueSize / $this->previousQueueSize;

        // queue size has grown 5% during the last interval
        if ($ratio >= 1.0 && $queueSize > 5) {

            // spin up only if we're not already spinning a worker up
            if($totalComing < 1) {
                $this->virtualMachineManager->start("worker");

                echo "Starting a worker. Ratio: " .  $ratio . ". Queuesize: " . $queueSize . "\n";
            }

        } else if ($queueSize <= 5) {

            // spinning down, if we are not already 
            if($spindownCount == 0 && $runningCount > 1) {
                $this->virtualMachineManager->stop("worker");

                echo "Stopping a worker. Ratio: " .  $ratio . ". Queuesize: " . $queueSize . "\n";
            }
        }

        $this->previousQueueSize = $queueSize;
    }
}
