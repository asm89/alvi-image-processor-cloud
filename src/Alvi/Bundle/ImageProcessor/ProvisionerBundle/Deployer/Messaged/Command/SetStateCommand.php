<?php

namespace Alvi\Bundle\ImageProcessor\ProvisionerBundle\Deployer\Messaged\Command;

use Alvi\Bundle\ImageProcessor\ProvisionerBundle\VirtualMachine;

/**
 * Set the state of a VM.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class SetStateCommand
{
    private $vm;
    private $state;

    /**
     * @param VirtualMachine $vm
     * @param string         $state
     */
    public function __construct(VirtualMachine $vm, $state)
    {
        $this->vm    = $vm;
        $this->state = $state;
    }

    /**
     * @return VirtualMachine
     */
    public function getVirtualMachine()
    {
        return $this->vm;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }
}
