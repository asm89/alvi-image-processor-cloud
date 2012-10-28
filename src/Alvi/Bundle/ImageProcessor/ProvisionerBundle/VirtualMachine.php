<?php

namespace Alvi\Bundle\ImageProcessor\ProvisionerBundle;

/**
 * Class representing a running virtual machine.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class VirtualMachine
{
    private $configuration;
    private $fqdn;
    private $ip;

    /**
     * Constructor.
     *
     * @param VirtualMachineConfiguration $configuration
     */
    public function __construct(VirtualMachineConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getFqdn()
    {
        return $this->fqdn;
    }

    public function setFqdn($fqdn)
    {
        $this->fqdn = $fqdn;
    }

    public function getIp()
    {
        return $this->ip;
    }

    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }
}
