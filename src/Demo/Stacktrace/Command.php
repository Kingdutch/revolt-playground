<?php

namespace Kingdutch\RevoltPlayground\Demo\Stacktrace;

use Revolt\EventLoop;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function Drupal\Core\Async\stream;

/**
 * Demonstrates what stacktraces look like on the Revolt event loop.
 *
 * The demo below will schedule two tasks in a stream and will cause the second
 * task to throw an exception in order to show whether the error can easily be
 * traced back to the cause.
 * 
 * This will produce the following output (when run with the `-v` flag):
 * ```
 * [main] Executed operation successfully. Output: 'Success'
 *
 * In Command.php line 43:
 *
 * [RuntimeException]
 * Something went wrong!
 *
 *
 * Exception trace:
 * at revolt-playground/src/Demo/Stacktrace/Command.php:43
 * Kingdutch\RevoltPlayground\Demo\Stacktrace\Command->throwException() at revolt-playground/src/Demo/Stacktrace/Command.php:25
 * Kingdutch\RevoltPlayground\Demo\Stacktrace\Command->Kingdutch\RevoltPlayground\Demo\Stacktrace\{closure}() at revolt-playground/Drupal/Core/Async/functions.php:41
 * Drupal\Core\Async\{closure}() at revolt-playground/vendor/revolt/event-loop/src/EventLoop/Internal/AbstractDriver.php:597
 * Revolt\EventLoop\Internal\AbstractDriver->Revolt\EventLoop\Internal\{closure}() at n/a:n/a
 * Fiber->resume() at revolt-playground/vendor/revolt/event-loop/src/EventLoop/Internal/AbstractDriver.php:497
 * Revolt\EventLoop\Internal\AbstractDriver->invokeCallbacks() at revolt-playground/vendor/revolt/event-loop/src/EventLoop/Internal/AbstractDriver.php:553
 * Revolt\EventLoop\Internal\AbstractDriver->Revolt\EventLoop\Internal\{closure}() at n/a:n/a
 * Fiber->resume() at revolt-playground/vendor/revolt/event-loop/src/EventLoop/Internal/AbstractDriver.php:94
 * Revolt\EventLoop\Internal\AbstractDriver->Revolt\EventLoop\Internal\{closure}() at revolt-playground/vendor/revolt/event-loop/src/EventLoop/Internal/DriverSuspension.php:117
 * Revolt\EventLoop\Internal\DriverSuspension->suspend() at revolt-playground/Drupal/Core/Async/functions.php:55
 * Drupal\Core\Async\stream() at revolt-playground/src/Demo/Stacktrace/Command.php:28
 * Kingdutch\RevoltPlayground\Demo\Stacktrace\Command->execute() at revolt-playground/vendor/symfony/console/Command/Command.php:279
 * Symfony\Component\Console\Command\Command->run() at revolt-playground/vendor/symfony/console/Application.php:1031
 * Symfony\Component\Console\Application->doRunCommand() at revolt-playground/vendor/symfony/console/Application.php:318
 * Symfony\Component\Console\Application->doRun() at revolt-playground/vendor/symfony/console/Application.php:169
 * Symfony\Component\Console\Application->run() at revolt-playground/index.php:21
 * ```
 */
#[AsCommand(name: 'playground:demo:stacktrace')]
class Command extends BaseCommand {

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $operations = [
      fn () => "Success",
      fn () => $this->throwException(),
    ];

    foreach (stream($operations) as $result) {
      // Rethrow the exception that was caught if any.
      if ($result->isError()) {
        throw $result->getValue();
      }

      echo "[main] Executed operation successfully. Output: '" . $result->getValue() . "'\n";
    }

    EventLoop::run();

    return BaseCommand::SUCCESS;
  }

  protected function throwException() : never {
    throw new \RuntimeException("Something went wrong!");
  }

}
