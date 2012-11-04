<?php

namespace Alvi\Bundle\ImageProcessor\ProvisionerBundle;

/**
 * Interface that should be implemented by provisioners.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
interface ProvisionerInterface
{
    /**
     * Provision a VM with the given configuration.
     *
     * @param VirtualMachineConfiguration $vm
     *
     * @return VirtualMachine
     */
    public function provision(VirtualMachineConfiguration $vmConfiguration);

    /**
     * Destroys a given VM.
     *
     * @param VirtualMachine
     */
    public function destroy(VirtualMachine $vm);
}
