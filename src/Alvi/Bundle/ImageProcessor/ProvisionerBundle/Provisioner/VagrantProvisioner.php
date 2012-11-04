<?php

namespace Alvi\Bundle\ImageProcessor\ProvisionerBundle\Provisioner;

use Alvi\Bundle\ImageProcessor\ProvisionerBundle\ProvisionerInterface;
use Alvi\Bundle\ImageProcessor\ProvisionerBundle\VirtualMachineConfiguration;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Provision virtual machines using vagrant.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class VagrantProvisioner implements ProvisionerInterface
{
    const IP_PREFIX = '172.';
    const TIMEOUT = 300;

    private $runDirectory;
    private $basefile;
    private $envHome = null;

    /**
     * @param string $runDirectory
     * @param string $basefile     Path to base vagrant file
     * @param string $envHome      Home directory for vagrant (defaults to user home)
     */
    public function __construct($runDirectory, $basefile, $envHome = null)
    {
        $this->runDirectory = $runDirectory;
        $this->basefile     = $basefile;

        if (null === $envHome) {
            if (!isset($_SERVER['HOME'])) {
                throw new \RuntimeException('Could not determine HOME environment to use with vagrant.');
            }

            $envHome = $_SERVER['HOME'];
        }

        $this->envHome = $envHome;
    }

    /**
     * @param VirtualMachineConfiguration $vm
     */
    public function provision(VirtualMachineConfiguration $virtualMachineConfiguration)
    {
        $virtualMachine = new VagrantVirtualMachine($virtualMachineConfiguration);

        $this->assignNumber($virtualMachine);
        $this->createRunDirectory($virtualMachine);
        $this->createVagrantfile($virtualMachine);
        $this->bootVirtualMachine($virtualMachine);
        $this->determineIp($virtualMachine);

        return $virtualMachine;
    }

    private function determineIp(VagrantVirtualMachine $virtualMachine)
    {
        $builder = $this->createProcessBuilder($virtualMachine, array('vagrant', 'ssh', '-c', 'hostname -I'));
        $process = $builder->getProcess();
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException('Unable to determine ip from virtual machine.');
        }

        $possibleIps = explode(' ', $process->getOutput()); // assume '1.1.1.1 2.2.2.2' output

        foreach ($possibleIps as $ip) {
            if (0 === strpos($ip, self::IP_PREFIX)) {
                $virtualMachine->setIp($ip);
                break;
            }
        }

        if (null === $virtualMachine->getIp()) {
            throw new \RuntimeException(sprintf('No ip with prefix "%s" found in "%s".', self::IP_PREFIX, $process->getOutput()));
        }
    }

    private function bootVirtualMachine(VagrantVirtualMachine $virtualMachine)
    {
        // vagrant up!
        $builder = $this->createProcessBuilder($virtualMachine, array('vagrant', 'up'));

        $process = $builder->getProcess();
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }
    }

    /**
     * Create a process builder relative ready to run vagrant commands.
     *
     * @param VagrantVirtualMachine $virtualMachine
     * @param array $arguments
     */
    private function createProcessBuilder(VagrantVirtualMachine $virtualMachine, array $arguments)
    {
        return ProcessBuilder::create($arguments)
            ->setEnv('HOME', $this->envHome) // vagrant needs a HOME
            ->setWorkingDirectory($virtualMachine->getRunDirectory())
            ->setTimeout(self::TIMEOUT);
    }


    /**
     * @param VagrantVirtualMachine $virtualMachine
     */
    private function createVagrantfile(VagrantVirtualMachine $virtualMachine)
    {
        $basefile = file_get_contents($this->basefile);

        $type = $virtualMachine->getConfiguration()->getType();

        $fqdn = sprintf('%s%03d', $type, $virtualMachine->getNumber());

        $vagrantfile = str_replace(
            array('%vm_memory%',                                    '%vm_type%', '%vm_fqdn%'),
            array($virtualMachine->getConfiguration()->getMemory(), $type,       $fqdn),
            $basefile
        );

        file_put_contents($virtualMachine->getRunDirectory() . '/Vagrantfile', $vagrantfile);
    }

    /**
     * @param string $type
     *
     * @return string New run directory for the type
     */
    private function createRunDirectory(VagrantVirtualMachine $virtualMachine)
    {
        $typeDirectory = $this->getTypeDirectory($virtualMachine->getConfiguration()->getType());

        $filesystem = new Filesystem();
        if (!$filesystem->exists($typeDirectory)) {
            $filesystem->mkdir($typeDirectory);
        }

        $runDirectory = $typeDirectory . '/' . sprintf('%03d', $virtualMachine->getNumber());

        $filesystem->mkdir($runDirectory);

        $virtualMachine->setRunDirectory($runDirectory);
    }

    /**
     * @param string $type
     *
     * @return integer
     */
    private function assignNumber(VagrantVirtualMachine $virtualMachine)
    {
        $typeDirectory = $this->getTypeDirectory($virtualMachine->getConfiguration()->getType());

        $filesystem = new Filesystem();
        if (!$filesystem->exists($typeDirectory)) {
            $virtualMachine->setNumber(1);

            return;
        }

        $directories = Finder::create()
            ->directories()
            ->in($typeDirectory)
            ->sortByName();

        // find the number of the latest worker
        foreach ($directories as $directory) {
            $number = (int) $directory->getBasename();
        }

        $virtualMachine->setNumber($number + 1);
    }

    /**
     * @param string $type
     *
     * @return string
     */
    private function getTypeDirectory($type)
    {
        return $this->runDirectory . '/' . $type;
    }
}
