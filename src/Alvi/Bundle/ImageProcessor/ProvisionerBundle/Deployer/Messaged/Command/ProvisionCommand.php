<?php

namespace Alvi\Bundle\ImageProcessor\ProvisionerBundle\Deployer\Messaged\Command;

use Alvi\Bundle\ImageProcessor\ProvisionerBundle\VirtualMachine;

/**
 * Provision a VM of the given type.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class ProvisionCommand
{
    private $vm;

    /**
     * @param string $type
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
