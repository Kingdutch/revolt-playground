#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

use Kingdutch\RevoltPlayground\Demo\SuspensionOrder\Command as DemoSuspensionOrderCommand;
use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new DemoSuspensionOrderCommand());

$application->run();
