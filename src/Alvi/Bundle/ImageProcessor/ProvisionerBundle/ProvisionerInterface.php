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
     * Provision a given VM.
     *
     * @param VirtualMachine $vm
     *
     * @return VirtualMachine
     */
    public function provision(VirtualMachine $vm);

    /**
     * Destroys a given VM.
     *
     * @param VirtualMachine
     */
    public function destroy(VirtualMachine $vm);
}
