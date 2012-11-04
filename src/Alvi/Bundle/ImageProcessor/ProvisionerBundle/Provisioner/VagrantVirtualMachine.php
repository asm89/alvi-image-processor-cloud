<?php

namespace Alvi\Bundle\ImageProcessor\ProvisionerBundle\Provisioner;

use Alvi\Bundle\ImageProcessor\ProvisionerBundle\VirtualMachine;

/**
 * Vagrant virtual machine.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class VagrantVirtualMachine extends VirtualMachine
{
    private $runDirectory;
    private $number;

    public function __construct($type, $memory = 256)
    {
        parent::__construct($type, $memory);
    }

    public static function create(VirtualMachine $machine)
    {
        $vm = new self($machine->getType(), $machine->getMemory());

        $vm->setFqdn($machine->getFqdn());
        $vm->setIp($machine->getIp());
        $vm->setId($machine->getId());
        $vm->setState($machine->getState());

        return $vm;
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
        $this->setFqdn($this->getType() . sprintf('%03d', $number));
    }
}
