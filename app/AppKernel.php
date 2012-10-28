<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new OldSound\RabbitMqBundle\OldSoundRabbitMqBundle(),
            new Beberlei\Bundle\MetricsBundle\BeberleiMetricsBundle(),

            new Alvi\Bundle\ImageProcessor\JobSubmissionBundle\AlviImageProcessorJobSubmissionBundle(),
            new Alvi\Bundle\ImageProcessor\MonitoringBundle\AlviImageProcessorMonitoringBundle(),
            new Alvi\Bundle\ImageProcessor\ScalerBundle\AlviImageProcessorScalerBundle(),
            new Alvi\Bundle\ImageProcessor\SchedulerBundle\AlviImageProcessorSchedulerBundle(),
            new Alvi\Bundle\ImageProcessor\WorkerBundle\AlviImageProcessorWorkerBundle(),
        );

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
