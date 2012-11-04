<?php

namespace Alvi\Bundle\ImageProcessor\ProvisionerBundle\Deployer\Messaged\Command;

use Alvi\Bundle\ImageProcessor\ProvisionerBundle\VirtualMachine;

/**
 * Destroy a VM of the given type.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class DestroyCommand
{
    private $vm;

    /**
     * @param VirtualMachine $vm
     */
    public function __construct(VirtualMachine $vm)
    {
        $this->vm = $vm;
    }

    /**
     * @return VirtualMachine
     */
    public function getVirtualMachine()
    {
        return $this->vm;
    }
}
