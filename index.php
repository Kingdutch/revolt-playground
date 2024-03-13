#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

use Kingdutch\RevoltPlayground\Demo\BigPipe\Command as DemoBigPipeCommand;
use Kingdutch\RevoltPlayground\Demo\Bootstrap\NoLoopCommand as DemoBootstrapNoLoopCommand;
use Kingdutch\RevoltPlayground\Demo\Bootstrap\WithLoopCommand as DemoBootstrapWithLoopCommand;
use Kingdutch\RevoltPlayground\Demo\RepeatedDefer\Command as DemoRepeatedDeferCommand;
use Kingdutch\RevoltPlayground\Demo\Stacktrace\Command as DemoStacktraceCommand;
use Kingdutch\RevoltPlayground\Demo\SuspensionOrder\Command as DemoSuspensionOrderCommand;
use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new DemoBigPipeCommand());
$application->add(new DemoBootstrapNoLoopCommand());
$application->add(new DemoBootstrapWithLoopCommand());
$application->add(new DemoRepeatedDeferCommand());
$application->add(new DemoStacktraceCommand());
$application->add(new DemoSuspensionOrderCommand());

$application->run();
