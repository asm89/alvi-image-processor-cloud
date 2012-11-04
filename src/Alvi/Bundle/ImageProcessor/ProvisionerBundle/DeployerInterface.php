<?php

namespace Alvi\Bundle\ImageProcessor\ProvisionerBundle;

use Alvi\Bundle\ImageProcessor\ProvisionerBundle\VirtualMachine;

/**
 * Interface for classes responsible for deployment.
 *
 * Deployer implementations are responsible for the actual provisioning and
 * destroying of VMs and registering the appropriate VM states with the
 * VirtualMachineManager.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
interface DeployerInterface
{
    /**
     * Provision and register a VM of the given type.
     *
     * @param string $type
     */
    public function provision(VirtualMachine $vm);

    /**
     * Destroy and unregister a VM of the given type.
     *
     * @param VirtualMachine $vm
     */
    public function destroy(VirtualMachine $vm);
}
