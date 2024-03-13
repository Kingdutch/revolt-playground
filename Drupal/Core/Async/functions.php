<?php
/**
 * @file
 * Contains async helper functions for the Drupal framework.
 *
 * Functions in this file are there to provide common actions in async
 * programming.
 */

declare(strict_types=1);

namespace Drupal\Core\Async;

use Drupal\Core\Result;
use Revolt\EventLoop;

/**
 * Stream a set of operations that execute concurrently.
 *
 * Will kick off a set of simultaneous operations and yield whenever an
 * operation has finished.
 *
 * @template KeyT
 * @template ValueT
 *
 * @param array<KeyT, callable() : ValueT> $operations
 *   The set of operations to execute simultaneously.
 *
 * @return \Generator<KeyT, \Drupal\Core\Result<ValueT, \Throwable>>
 *   A generator that will yield the key and value whenever an operation is
 *   completed.
 */
function stream(array $operations) : \Generator {
  /** @var \Revolt\EventLoop\Suspension<array{KeyT, \Drupal\Core\Result<ValueT, \Throwable>}> $suspension */
  $suspension = EventLoop::getSuspension();

  // Kick of all the events that happen.
  foreach ($operations as $key => $operation) {
    EventLoop::defer(function () use ($suspension, $key, $operation) {
      try {
        $result = $operation();
        $suspension->resume([$key, Result::ok($result)]);
      }
      catch (\Throwable $e) {
        $suspension->resume([$key, Result::error($e)]);
      }
    });
  }

  // We must suspend the same number of times that we queued operations to make
  // sure we complete them all.
  for ($i=0,$j=count($operations);$i<$j;$i++) {
    // We don't need to try/catch here since we ensure errors thrown by the
    // operation itself are caught earlier.
    [$key, $result] = $suspension->suspend();
    yield $key => $result;
  }
}

/**
 * Executes a set operations concurrently and return the results when done.
 *
 * @template KeyT of int|string
 * @template ValueT
 *
 * @param array<KeyT, callable() : ValueT> $operations
 *
 * @return array<KeyT, \Drupal\Core\Result<ValueT, \Throwable>>
 */
function concurrently(array $operations) : array {
  /** @var \Revolt\EventLoop\Suspension<array{KeyT, \Drupal\Core\Result<ValueT, \Throwable>}> $suspension */
  $suspension = EventLoop::getSuspension();
  $remaining = \count($operations);

  $results = [];
  foreach ($operations as $key => $operation) {
    // Pre-fill the arrays to ensure that the order of results and errors are
    // preserved regardless of the order in which operations completed.
    $results[$key] = NULL;
    EventLoop::queue(function () use ($key, $operation, $suspension, &$results, &$remaining) {
      try {
        $results[$key] = Result::ok($operation());
      } catch (\Throwable $e) {
        $results[$key] = Result::error($e);
      }

      if (--$remaining === 0) {
        $suspension->resume();
      }
    });
  }

  $suspension->suspend();
  assert(!in_array(NULL, $results, TRUE), "Incorrect implementation of " . __FUNCTION__ . ": resume called before all operations were completed.");

  return $results;
}
