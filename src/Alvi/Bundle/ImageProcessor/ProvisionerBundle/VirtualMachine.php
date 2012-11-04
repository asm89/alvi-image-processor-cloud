<?php

namespace Alvi\Bundle\ImageProcessor\ProvisionerBundle;

/**
 * Class representing a running virtual machine.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class VirtualMachine
{
    private $fqdn;
    private $ip;
    private $state;
    private $id;
    private $type;
    private $memory;

    const STATE_DESTROYING   = 'destroying';
    const STATE_DESTROYED    = 'destroyed';
    const STATE_FAILED       = 'failed';
    const STATE_PREPARING    = 'preparing';
    const STATE_RUNNING      = 'running';
    const STATE_SPINNINGDOWN = 'spinningup';
    const STATE_SPINNINGUP   = 'spinningdown';

    /**
     * Constructor.
     *
     * @param string  $type
     * @param integer $memory In MB
     */
    public function __construct($type, $memory = 256)
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

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
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

    public function isBooted()
    {
        return null !== $this->ip && null !== $this->fqdn;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState($state)
    {
        $this->state = $state;
    }
}
