<?php

namespace Alvi\Bundle\ImageProcessor\ProvisionerBundle\Provisioner;

use Alvi\Bundle\ImageProcessor\ProvisionerBundle\VirtualMachine;
use Alvi\Bundle\ImageProcessor\ProvisionerBundle\VirtualMachineConfiguration;

/**
 * Vagrant virtual machine.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class VagrantVirtualMachine extends VirtualMachine
{
    private $runDirectory;
    private $number;

    public function __construct(VirtualMachineConfiguration $configuration)
    {
        parent::__construct($configuration);
    }

    public function getRunDirectory()
    {
        return $this->runDirectory;
    }

    public function setRunDirectory($runDirectory)
    {
        $this->runDirectory = $runDirectory;
    }

    public function getNumber()
    {
        return $this->number;
    }

    public function setNumber($number)
    {
        $this->number = $number;
        $this->setFqdn($this->getConfiguration()->getType() . sprintf('%03d', $number));
    }
}
