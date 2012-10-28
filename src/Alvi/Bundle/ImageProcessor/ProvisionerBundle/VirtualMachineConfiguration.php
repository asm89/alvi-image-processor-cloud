<?php

namespace Alvi\Bundle\ImageProcessor\ProvisionerBundle;

/**
 * Class representing a virtual machine configuration.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class VirtualMachineConfiguration
{
    /**
     * Constructor.
     *
     * @param string  $type
     * @param integer $memory In MB
     */
    public function __construct($type, $memory = 1024)
    {
        $this->type   = $type;
        $this->memory = $memory;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getMemory()
    {
        return $this->memory;
    }
}
