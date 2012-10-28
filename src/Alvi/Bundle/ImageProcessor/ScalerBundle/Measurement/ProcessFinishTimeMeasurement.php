<?php

namespace Alvi\Bundle\ImageProcessor\ScalerBundle\Measurement;

/**
 * Retrieve measurements for the Scaler policy
 *
 * @author Vincent <vincentvanbeek@mac.com>
 */
class ProcessFinishTimeMeasurement
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
     * return int average if their are measurements other wise return false
     */
    public function getMovingAverageFinishTime() {
        $commandFinishTime = "?target=movingAverage(stats.timers.alvi.jobs_finish_time.mean,10)&format=json&from=-1minutes";
        $averageFinishTimeJsonData = $this->graphiteAPI->getDataFromGraphiteCommand($commandFinishTime);
        if(isset($averageFinishTimeJsonData[0]) && $averageFinishTimeJsonData[0]->datapoints) {
            $averageFinishTime = $this->calculateMovingAverageTime($averageFinishTimeJsonData[0]->datapoints);   
        }
        else {
            return false;
        }
        return $averageFinishTime;
    }
    
    /**
     * return int average if their are measurements other wise return false
     */
    public function getMovingAverageProcessTime() {
        $commandProcessTime = "?target=movingAverage(stats.timers.alvi.jobs_process_time.mean,10)&format=json&from=-1minutes";
        $averageProcessTimeJsonData = $this->graphiteAPI->getDataFromGraphiteCommand($commandProcessTime);
        if(isset($averageProcessTimeJsonData[0]) && isset($averageProcessTimeJsonData[0]->datapoints)) {
            $averageProcessTime = $this->calculateMovingAverageTime($averageProcessTimeJsonData[0]->datapoints);
        }
        else {
            return false;
        }
        return $averageProcessTime;
    }
    
    /**
     * @param datapointsarray $data
     * return int average if more than 0 measurements other wise return false
     */
    private function calculateMovingAverageTime($data) {
        //calculate the average finish time
        $averageFinishTime = 0;
        $i = 0;
        foreach ($data as $finishTime) {
            if (isset($finishTime[0]) && $finishTime[0] != "") {
                $averageFinishTime += $finishTime[0];
                $i++;
            }
        }
        if ($i != 0) {
            $averageFinishTime = $averageFinishTime/$i;
        } else {
            return false;
        }
        return $averageFinishTime;
    }
}