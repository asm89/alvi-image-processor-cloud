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
    
    /**
     * @param graphiteAPI $api
     */
    public function __construct(GraphiteAPI $api)
    {
        $this->graphiteAPI = $api;
    }
    
    /**
     * returns int, the moving average of the queue size of the last minute
     */
    public function getMovingAverageQueueSize() {
        //TODO queue size url toevoegen
        $commandQueueSize = "?target=movingAverage(stats.timers.alvi.jobs_finish_time.mean,10)&format=json&from=-1minutes";
        $averageQueueSizeJsonData = $this->graphiteAPI->getDataFromGraphiteCommand($commandFinishTime);
        if(isset($averageQueueSizeJsonData[0]) && $averageQueueSizeJsonData[0]->datapoints) {
            $averageQueueSize = $this->calculateMovingAverage($averageQueueSizeJsonData[0]->datapoints);   
        }
        else {
            return false;
        }
        return $averageQueueSize;
    }
    
    /**
     * @param datapoints array $data
     * return int average if more than 0 measurements other wise return false
     */
    private function calculateMovingAverage($data) {
        //calculate the average finish time
        $total = 0;
        $i = 0;
        foreach ($data as $item) {
            if (isset($item[0]) && $item[0] != "") {
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