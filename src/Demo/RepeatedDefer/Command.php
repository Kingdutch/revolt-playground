<?php

namespace Kingdutch\RevoltPlayground\Demo\RepeatedDefer;

use Revolt\EventLoop;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Demonstrates how a repeated process can be scheduled around a request.
 *
 * One example usage in Drupal would be the cache-warmer where we want to
 * pre-warm caches if the main request is waiting for data. However, if the main
 * request can complete without loading any date (e.g. a fast-404 page) then
 * we shouldn't start any cache-warmers.
 *
 * This command has two modes of operation. In one mode it shows what happens
 * for a synchronous request, never running the scheduled async backgroudn task.
 * In the other mode it runs the request and shows how Revolt's event loop
 * prioritises deferred tasks over (repeating) delayed tasks.
 *
 * Running this without options will produce the following output:
 * ```
 * Performing 2 synchronous tasks that block for 1 second
 * Completed synchronous task 0
 * Completed synchronous task 1
 * Tasks complete, cancelling pre-warming.
 * ```
 *
 * Running this with the `--async` option will produce the following output:
 * ```
 * Performing 2 asynchronous tasks that complete after 1 second
 * Repeat triggered
 * Re-enabled callback
 * Repeat triggered
 * Completed asynchronous task 0
 * Re-enabled callback
 * Repeat triggered
 * Re-enabled callback
 * Repeat triggered
 * Completed asynchronous task 1
 * Tasks complete, cancelling pre-warming.
 * ```
 */
#[AsCommand(name: 'playground:demo:repeated-defer')]
class Command extends BaseCommand {

  protected function configure() {
    $this
      ->addOption("async");
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $callbackId = EventLoop::repeat(0, function ($callbackId) {
      echo "Repeat triggered\n";
      EventLoop::disable($callbackId);
      $this->asyncSleep(.5);
      // Eat the error if the repeat was cancelled.
      // Until: https://github.com/revoltphp/event-loop/issues/91.
      try {
        EventLoop::enable($callbackId);
        echo "Re-enabled callback\n";
      }
      catch (EventLoop\InvalidCallbackError $e) {}
    });

    $shouldAsync = $input->getOption('async');
    assert(is_bool($shouldAsync));

    $j = 2;
    if ($shouldAsync) {
      echo "Performing $j asynchronous tasks that complete after 1 second\n";
      for ($i = 0; $i < $j; $i++) {
        $this->asyncSleep(1);
        echo "Completed asynchronous task $i\n";
      }
    }
    else {
      echo "Performing $j synchronous tasks that block for 1 second\n";
      // @todo: Provide toggle between async.
      for ($i = 0; $i < $j; $i++) {
        sleep(1);
        echo "Completed synchronous task $i\n";
      }
    }

    echo "Tasks complete, cancelling pre-warming.\n";
    EventLoop::cancel($callbackId);

    EventLoop::run();

    return BaseCommand::SUCCESS;
  }

  protected function asyncSleep(float $seconds) : void {
    $suspension = EventLoop::getSuspension();
    EventLoop::delay($seconds, fn () => $suspension->resume());
    $suspension->suspend();
  }

}
