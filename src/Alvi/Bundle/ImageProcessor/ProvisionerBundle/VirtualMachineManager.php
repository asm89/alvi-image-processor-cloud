<?php

namespace Alvi\Bundle\ImageProcessor\ProvisionerBundle;

/**
 * Exposes an API for adding/removing virtual machines.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class VirtualMachineManager
{
    private $running;
    private $spinningUp;
    private $spinningDown;

    public function getRunning($type)
    {
        return $this->running;
    }

    public function getSpinningUp($type)
    {
        return $this->spinningUp;
    }

    public function getSpinningDown($type)
    {
        return $this->spinningDown;
    }

    public function setRunning($running)
    {
        $this->running = $running;
    }

    public function setSpinningUp($x)
    {
        $this->spinningUp = $x;
    }

    public function setSpinningDown($x)
    {
        $this->spinningDown = $x;
    }

    public function start($type)
    {
        $this->running++;
    }

    public function stop($type)
    {
        $this->running--;
    }
}
