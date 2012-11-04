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
    public function __construct(ProvisionerInterface $provisioner, Zookeeper $zookeeper)
    {
        $this->provisioner = $provisioner;
        $this->zookeeper   = $zookeeper;
    }

    public function getRunning($type)
    {
        return $this->getNumberByType($type, '/nodes/running');
    }

    public function getSpinningUp($type)
    {
        return $this->getNumberByType($type, '/nodes/spinningup');
    }

    public function getSpinningDown($type)
    {
        return $this->getNumberByType($type, '/nodes/spinningdown');
    }

    private function getNumberByType($type, $path)
    {
        if (null === $this->zookeeper->get($path)) {
            return 0;
        }

        $children = $this->zookeeper->getChildren($path);

        return array_reduce($children,
            function($total, $child) use ($type) { return $total + (false !== strpos($child, $type) ? 1 : 0); },
            0
        );
    }

    public function start($type)
    {
        // message deployer
    }

    public function stop($type)
    {
        // message deployer
    }
}
