<?php

namespace Alvi\Bundle\ImageProcessor\ProvisionerBundle\Deployer\Messaged\Command;

/**
 * Provision a VM of the given type.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class ProvisionCommand
{
    private $type;

    /**
     * @param string $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
