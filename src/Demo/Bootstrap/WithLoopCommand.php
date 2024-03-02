<?php

namespace Kingdutch\RevoltPlayground\Demo\Bootstrap;

use Revolt\EventLoop;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Demonstrates the addition of `EventLoop::run` to catch long running tasks.
 *
 * The execute function in this demo contains the code normally contained in the
 * index.php file, which is the same as in `NoLoopCommand` but instead adds a
 * call to EventLoop::run().
 *
 * This will produce the following output:
 * ```
 * Kingdutch\RevoltPlayground\Demo\Bootstrap\DrupalKernel::handle
 * Repeat 1
 * Repeat 2
 * Repeat 3
 * Kingdutch\RevoltPlayground\Demo\Bootstrap\Response::send
 * Sending part 1/2
 * Kingdutch\RevoltPlayground\Demo\Bootstrap\DrupalKernel::terminate
 * Sending part 2/2
 * Delayed task executed.
 * ```
 */
#[AsCommand(name: 'playground:demo:bootstrap:with-loop')]
class WithLoopCommand extends BaseCommand {

  protected function execute(InputInterface $input, OutputInterface $output): int {
    // Normally you'd load your autoloader here.

    $kernel = new DrupalKernel("prod");

    $response = $kernel->handle(/* $request */);
    $response->send();

    $kernel->terminate(/* $request, */ $response);

    EventLoop::run();

    return BaseCommand::SUCCESS;
  }

}
