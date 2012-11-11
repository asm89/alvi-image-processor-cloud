alvi-image-processor-cloud
==========================

A proof of concept application for distributed image processing in the cloud.
The application was created for the 2012 Cloud Computing course of the Delft
University of Technology.

> Note: all code found in this repository is highly experimental and in **prototype** shape.

What is this?
-------------
This is an application used for evaluating the design of a cloud computing
system for a course at the Delft University of Technology. It is able to
autonomously start and stop virtual machines as the load of the system varies.
It combines a Symfony2 PHP application together with:

- [Apache ZooKeeper](http://zookeeper.apache.org/)
- [Graphite](http://graphite.wikidot.com/)
- [Puppet](http://puppetlabs.com/)
- [RabbitMQ](http://www.rabbitmq.com/)
- [Statsd](https://github.com/etsy/statsd)
- [Vagrant](http://vagrantup.com)
- [VirtualBox](https://www.virtualbox.org/)

The high level overview of the system can be found below.

![System design](https://github.com/asm89/alvi-image-processor-cloud/raw/master/doc/system-design.png)

All in all the system does not actually process any images, instead it
primarily keeps your CPU busy while drawing some graphs. The image processing
is simulated by letting the workers of the system sleep for a certain amount of
time. More information on the application can be found in the accompanying
[*report*](https://github.com/downloads/asm89/alvi-image-processor-cloud/report.pdf).

![Queuerate graph](https://github.com/asm89/alvi-image-processor-cloud/raw/master/doc/queuerate.png)


What to do now?
---------------
If you enjoy the graph above you can go on and actually setup the system
(recommended ;) ) with the instructions below. An alternative would be watching a
video of our primary runs: https://vimeo.com/53266455.


Installation
------------

In order to run the system [Vagrant](http://vagrantup.com/) and PHP have to be
installed on the host computer.

> All console commands are assumed to be run from the root level of the project.

Start by cloning the repository. After cloning initialize the submodules:

```bash
$ git submodule update --init
```

The dependencies of the application can be installing using composer:

```bash
$ curl https://getcomposer.org/installer | php
$ composer.phar install --dev
```

Running
-------

Although most of the application is automated, there are a few steps to perform
in order to run the application.

# 1) Start an initial "master" node.

Run the following command and get some coffee or watch puppet deploy the master
node stack.

```bash
$ cd vagrant
/vagrant $ vagrant up
```

# 2) Start a "deployer" process on the host machine
The deployer process will consume command messages to start and stop virtual
machines.

```bash
$ app/console rabbitmq:consumer deployer
```

# 3) Start a "scaler" policy on the master node

```bash
$ cd vagrant
/vagrant $ vagrant ssh
# now you're in the VM, /data contains the app
vagrant@master $ /data/app/console alvi:image-processor:scaler --scalerpolicy queuesize
```

Now you have to wait until the worker comes up. You can do this by checking out
the graphs page (see below) or looking at the VirtualBox gui.

# 4) Finally start a benchmark

```bash
$ cd vagrant
/vagrant $ vagrant ssh
# now you're in the VM, /data contains the app
vagrant@master $ /data/app/console alvi:image-processor:jobSubmit /data/burstWorkload.log
```

# 5) Watch some graphs!

Open the `graphs.html` file in the `doc` directory in your browser and watch
the graphs as the system scales up and down.

# Cleaning up

When you are done with playing with the system, open the VirtualBox gui to
remove all created virtual machines that were not already destroyed.

# Others

Checkout the `/data/app/console` command for other possible commands such as
inspecting the contents of zookeeper.
