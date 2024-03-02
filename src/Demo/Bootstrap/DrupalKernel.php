<?php

declare(strict_types=1);

namespace Kingdutch\RevoltPlayground\Demo\Bootstrap;

use Revolt\EventLoop;

/**
 * An example Drupal Kernel that will perform some async tasks.
 */
class DrupalKernel {

  public function __construct(protected string $environment) {}

  public function handle() : Response {
    echo __CLASS__ . "::" . __FUNCTION__ . PHP_EOL;

    // Perform 3 async tasks before we complete.
    $count = 0;
    /** @var \Revolt\EventLoop\Suspension<\Kingdutch\RevoltPlayground\Demo\Bootstrap\Response> $suspension */
    $suspension = EventLoop::getSuspension();
    EventLoop::repeat(0.1, function ($callbackId) use (&$count, $suspension) {
      $count++;
      echo "Repeat $count\n";
      if ($count === 3) {
        EventLoop::cancel($callbackId);
        $suspension->resume(new Response());
      }
    });

    return $suspension->suspend();
  }

  public function terminate(/* $request, */ Response $response) : void {
    echo __CLASS__ . "::" . __FUNCTION__ . PHP_EOL;

    // Start some long-running task like search indexing.
    EventLoop::delay(2, function () {
      echo "Delayed task executed.\n";
    });
  }

}
