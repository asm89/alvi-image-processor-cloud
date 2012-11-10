<?php

namespace Alvi\Bundle\ImageProcessor\ScalerBundle\Measurement;

/**
 * Retrieve measurements for the Scaler policy
 *
 * @author Vincent <vincentvanbeek@mac.com>
 */
class QueueMeasurement
{

    private $graphiteAPI;
    private $rabbitMq;
    
    /**
     * @param graphiteAPI $api
     */
    public function __construct(GraphiteAPI $api, RabbitMQAPI $rabbitMq)
    {
        $this->graphiteAPI = $api;
        $this->rabbitMq = $rabbitMq;
    }
    
    /**
     * returns int, the moving average of the queue size of the last minute
     */
    public function getMovingAverageQueueSize() {
        $command = "?target=movingAverage(stats.timers.alvi.queue.size.upload-picture.mean,100)&format=json&from=-5minutes";
        return $this->executeAverageCommand($command);
    }

    /**
     * @return integer
     */
    public function getQueueSize()
    {
        $data = $this->rabbitMq->executeApiCall("queues/%2f/upload-picture");

        if (false === $data) {
            return false;
        }

        return (int) ($data['messages'] - $data['messages_unacknowledged']);
    }
    
    /**
     * returns int, the incoming job rate
     */
    public function getMovingAverageIncomingJobRate() {
        $command = "?target=movingAverage(stats.timers.alvi.queue.incomming_rate.upload-picture.mean,100)&format=json&from=-5minutes";
        return $this->executeAverageCommand($command);
    }
    
    /**
     * returns int, the consuming job rate
     */
    public function getMovingAverageConsumingJobRate() {
        $command = "?target=movingAverage(stats.timers.alvi.queue.delivery_rate.upload-picture.mean,100)&format=json&from=-5minutes";
        return $this->executeAverageCommand($command);
    }
    
    /**
     * @param String $command (Graphite rest API command)
     * return int moving 
     */
    private function executeAverageCommand($command) {
        $averageJsonData = $this->graphiteAPI->getDataFromGraphiteCommand($command);
        if(isset($averageJsonData[0]) && isset($averageJsonData[0]->datapoints)) {
            $average = $this->calculateAverage($averageJsonData[0]->datapoints);   
        }
        else {
            return false;
        }
        return $average;
    }
    
    /**
     * @param datapoints array $data
     * return int average if more than 0 measurements other wise return false
     */
    private function calculateAverage($data) {
        //calculate the average finish time
        $total = 0;
        $i = 0;
        foreach ($data as $item) {
            if(isset($item[0]) && ($item[0] == '0' || $item[0] >= 1)){
                $total += $item[0];
                $i++;
            }
        }
        if ($i != 0) {
            $average = $total/$i;
        } else {
            return false;
        }
        return $average;
    }
}
