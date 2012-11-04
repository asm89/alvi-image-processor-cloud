alvi-image-processor-cloud
==========================

Distributed image processing in the cloud.

Installation
------------

> All console commands are assumed to be run from the root level of the project.

Application installation. Using composer:

```bash
$ curl https://getcomposer.org/installer | php
$ composer.phar install --dev
```


Running
-------

Start an initial "master" node.

```bash
$ cd vagrant
/vagrant $ vagrant up
```

Start a "deployer" process on the host machine. This process will consume
command messages to start and stop virtual machines.

```bash
$ app/console rabbitmq:consumer deployer
```

On the master node start submitting jobs:
```bash
$ cd vagrant
/vagrant $ vagrant ssh
# now you're in the VM, /data contains the app
$ cd /data
/data $ app/console alvi:image-processor:jobSubmit
```

Start job submit from a previous recorded file
```bash
/data $ app/console alvi:image-processor:jobSubmit --workloadFilepath /data/normalWorkload.log --openWorkload true
```