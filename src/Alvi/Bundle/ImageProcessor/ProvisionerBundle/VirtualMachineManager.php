<?php

namespace Alvi\Bundle\ImageProcessor\ProvisionerBundle;

use Alvi\Bundle\ImageProcessor\ZookeeperBundle\Zookeeper;

/**
 * Exposes an API for adding/removing virtual machines.
 *
 * The state of the virtual machines is kept in zookeeper for now. The
 * following structure is assumed:
 *     /nodes/spinup/worker001
 *     /nodes/spindown/worker002
 *     /nodes/running/worker003
 *
 * Each file contains the serialized VirtualMachine class.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class VirtualMachineManager
{
    private $deployer;

    /**
     * @param DeployerInterface $deployer
     * @param Zookeeper         $zookeeper
     */
    public function __construct(DeployerInterface $deployer, Zookeeper $zookeeper)
    {
        $this->deployer  = $deployer;
        $this->zookeeper = $zookeeper;
    }

    /**
     * @param string $type
     *
     * @return integer
     */
    public function getPreparingCount($type)
    {
        return $this->getNumberByType($type, '/nodes/preparing');
    }

    /**
     * @param string $type
     *
     * @return integer
     */
    public function getRunningCount($type)
    {
        return $this->getNumberByType($type, '/nodes/running');
    }

    /**
     * @param string $type
     *
     * @return integer
     */
    public function getSpinningUpCount($type)
    {
        return $this->getNumberByType($type, '/nodes/spinningup');
    }

    /**
     * @param string $type
     *
     * @return integer
     */
    public function getSpinningDownCount($type)
    {
        return $this->getNumberByType($type, '/nodes/spinningdown');
    }

    /**
     * @param string $type
     * @param path   $path Path in zookeeper containing the VM nodes
     *
     * @return integer
     */
    private function getNumberByType($type, $path)
    {
        $children = $this->getByType($type, $path);

        return array_reduce($children,
            function($total, $child) { return $total + 1; },
            0
        );
    }

    /**
     * @param string $type
     * @param path   $path Path in zookeeper containing the VM nodes
     *
     * @return array
     */
    private function getByType($type, $path)
    {
        if (null === $this->zookeeper->get($path)) {
            return array();
        }

        $children = $this->zookeeper->getChildren($path);

        return array_filter($children, function($child) use ($type) { return false !== strpos($child, $type); });
    }


    /**
     * @param string $type
     *
     * @return array
     */
    public function getRunningVirtualMachinesByType($type)
    {
        $vms = $this->getByType($type, '/nodes/running');

        return array_map(function($vm) { return unserialize($vm); }, $vms);
    }

    /**
     * @param VirtualMachine $vm
     * @param string         $state
     */
    public function setMachineState(VirtualMachine $vm, $state)
    {
        if (null !== $vm->getState()) {
            $path = $this->createStatePath($vm->getState(), $vm->getId());
            $this->zookeeper->delete($path);
        }

        $vm->setState($state);

        $path = $this->createStatePath($vm->getState(), $vm->getId());
        $this->zookeeper->set($path, serialize($vm));
    }

    /**
     * @param string $state
     * @param string $fqdn
     *
     * @return string
     */
    private function createStatePath($state, $fqdn)
    {
        return sprintf('/nodes/%s/%s', $state, $fqdn);
    }

    /**
     * @param string $type
     */
    public function start($type)
    {
        // todo: get memory from configuration?
        $virtualMachine = new VirtualMachine($type);

        // todo: better uuid? semi unique id, trying to prevent collision
        $i = $this->getPreparingCount($type) + 1;
        $uuid = $i . '_' . (microtime(true) * 10000);
        $virtualMachine->setId($type . $uuid);

        $this->setMachineState($virtualMachine, VirtualMachine::STATE_PREPARING);

        $this->deployer->provision($virtualMachine);
    }

    /**
     * @param string $type
     */
    public function stop($type)
    {
        $vms = $this->getByType('worker', '/nodes/running');

        if (!count($vms)) {
            // todo: log?
            // nothing to stop
            return;
        }

        $virtualMachine = unserialize($this->zookeeper->get('/nodes/running/' . current($vms)));

        // todo: loop until the worker queue is empty or whatever?
        $this->setMachineState($virtualMachine, VirtualMachine::STATE_DESTROYING);

        $this->deployer->destroy($virtualMachine);
    }
}
