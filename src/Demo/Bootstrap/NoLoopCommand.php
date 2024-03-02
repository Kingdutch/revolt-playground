<?php

namespace Kingdutch\RevoltPlayground\Demo\Bootstrap;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Demonstrates incorrect usage of EventLoop::run in index.php.
 *
 * The execute function in this demo contains the code normally contained in the
 * index.php file.
 *
 * The handle will run correctly because it suspends itself and waits for the
 * things it's waiting for to end. However, the tasks in `send` and `terminate`
 * are never completed because the program exits before they can be processed.
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
 * ```
 */
#[AsCommand(name: 'playground:demo:bootstrap:no-loop')]
class NoLoopCommand extends BaseCommand {

  protected function execute(InputInterface $input, OutputInterface $output): int {
    // Normally you'd load your autoloader here.

    $kernel = new DrupalKernel("prod");

    $response = $kernel->handle(/* $request */);
    $response->send();

    $kernel->terminate(/* $request, */ $response);

    return BaseCommand::SUCCESS;
  }
}
