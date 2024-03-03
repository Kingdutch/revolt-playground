<?php

namespace Kingdutch\RevoltPlayground\Demo\BigPipe;

use Revolt\EventLoop;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function Drupal\Core\Async\stream;

/**
 * Big Pipe Demo.
 *
 * Provides an example of how to implement the logic that would need to be done
 * to resolve the different placeholders for the BigPipe implementation. BigPipe
 * processes different concurrent tasks that may complete at different speeds.
 * The overarching process will send the results of the tasks down the HTTP
 * connection as soon as they are ready.
 *
 * This implementation makes use of the `stream` helper function which executes
 * a set of operations concurrently while yielding with items as soon as they're
 * complete (which may be out of starting order).
 *
 * This will produce the following output:
 * ```
 * [main]      Scheduling placeholder 1 after 0.3 seconds.
 * [main]      Scheduling placeholder 2 after 0.1 seconds.
 * [main]      Scheduling placeholder 3 after 0.6 seconds.
 * [main]      Scheduling placeholder 4 after 0.2 seconds.
 * [async]     Placeholder 2 executed.
 * [async]     Placeholder 2 completed.
 * [main]      For 2 got success Placeholder 2 value.
 * [interrupt] Other work during concurrent batch of tasks
 * [async]     Placeholder 4 executed.
 * [async]     Placeholder 4 completed.
 * [main]      For 4 got success Placeholder 4 value.
 * [async]     Placeholder 1 executed.
 * [async]     Placeholder 1 completed.
 * [main]      For 1 got success Placeholder 1 value.
 * [async]     Placeholder 3 executed.
 * [async]     Placeholder 3 completed.
 * [main]      For 3 got success Placeholder 3 value.
 * ```
 */
#[AsCommand(name: 'playground:demo:big-pipe')]
class Command extends BaseCommand {

  protected function execute(InputInterface $input, OutputInterface $output): int {
    // The number of seconds it takes for each placeholder to render.
    // You can play with these values and add more placeholders to change the
    // outcome.
    $placeHolder_timings = [
      1 => 0.3,
      2 => 0.1,
      3 => 0.6,
      4 => 0.2,
    ];

    // Queue the operations to resolve the placeholders.
    $operations = [];
    foreach ($placeHolder_timings as $placeholder_id => $timing) {
      echo "[main]      Scheduling placeholder $placeholder_id after $timing seconds.\n";
      $operations[$placeholder_id] = fn() => $this->placeHolder($placeholder_id, $timing);
    }

    // Demonstrate that while we're processing the big pipe tasks we can also
    // still do other things.
    EventLoop::delay(0.2, function () { echo "[interrupt] Other work during concurrent batch of tasks\n"; });

    // Stream the results of operations as they become available (regardless of
    // the order of completion).
    foreach (stream($operations) as $placeholder_id => $result) {
      if ($result->isOk()) {
        echo "[main]      For $placeholder_id got success {$result->getValue()}\n";
      }
      else {
        echo "[main]      For $placeholder_id got exception {$result->getValue()->getMessage()}\n";
      }
    }

    EventLoop::run();

    return BaseCommand::SUCCESS;
  }

  protected function placeHolder(int $id, float $delay) : string {
    /** @var \Revolt\EventLoop\Suspension<string> $suspension */
    $suspension = EventLoop::getSuspension();
    EventLoop::delay($delay, function () use ($id, $suspension) {
      echo "[async]     Placeholder $id executed.\n";
      $suspension->resume("Placeholder $id value.");
      echo "[async]     Placeholder $id completed.\n";
    });
    return $suspension->suspend();
  }

}
