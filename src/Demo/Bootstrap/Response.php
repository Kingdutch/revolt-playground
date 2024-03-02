<?php

declare(strict_types=1);

namespace Kingdutch\RevoltPlayground\Demo\Bootstrap;

use Revolt\EventLoop;

/**
 * An example response that performs async send.
 */
class Response {

  public function send() : void {
    echo __CLASS__ . "::" . __FUNCTION__ . PHP_EOL;

    // We must perform some processing based on external data before we can
    // continue with the rest of the application which is kernel clean-up.
    // To do that we create a suspension....
    $suspension = EventLoop::getSuspension();
    EventLoop::delay(0.2, function () use ($suspension) {
      echo "Sending part 1/2\n";

      EventLoop::delay(0.2, function () {
        echo "Sending part 2/2\n";
      });

      // ... which we resume after sending the first part.
      $suspension->resume();
    });

    $suspension->suspend();
  }

}
