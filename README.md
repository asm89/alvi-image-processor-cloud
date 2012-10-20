alvi-image-processor-cloud
==========================

Distributed image processing in the cloud.

Installation
------------

Application installation. Using composer:

```bash
$ curl https://getcomposer.org/installer | php
$ composer.phar install --dev
```

Run a message producer/scheduler:

```bash
$ app/console alvi:image-processor:schedule
```

Run a worker process:

```bash
$ app/console rabbitmq:consumer upload_picture

```
