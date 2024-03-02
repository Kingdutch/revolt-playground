<?php

namespace Kingdutch\RevoltPlayground\Demo\SuspensionOrder;

use Revolt\EventLoop;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Demonstrates that suspensions are shared within a Fiber.
 *
 * The demo below will call `getSuspension` twice which may cause people to
 * think they can schedule multiple separate tracks of data. However, within the
 * same Fiber the suspension will actually be shared. This means that depending
 * on how long tasks take, the return value of a suspend may not provide you
 * with the expected return value.
 *
 * For the below code a developer may expect to receive:
 * ```
 * Suspending on suspension 1
 * Got Suspension 1 after 0.5s
 * Suspending on suspension 2
 * Got Suspension 2 after 0.1s
 * ```
 *
 * However instead they'll receive:
 * ```
 * Suspending on suspension 1
 * Got Suspension 2 after 0.1s
 * Suspending on suspension 2
 * Got Suspension 1 after 0.5s
 * ```
 */
#[AsCommand(name: 'playground:demo:suspension-order')]
class Command extends BaseCommand {

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $suspension1 = EventLoop::getSuspension();
    $suspension2 = EventLoop::getSuspension();
    // Proof we actually got the same object, the rest of the code demonstrates
    // the slightly unintuitive behaviour.
    assert($suspension1 === $suspension2);

    EventLoop::delay(0.5, fn () => $suspension1->resume("Suspension 1 after 0.5s"));
    EventLoop::delay(0.1, fn () => $suspension2->resume("Suspension 2 after 0.1s"));

    echo "Suspending on suspension 1\n";
    echo "Got " . $suspension1->suspend() . "\n";
    echo "Suspending on suspension 2\n";
    echo "Got " . $suspension2->suspend() . "\n";

    EventLoop::run();

    return BaseCommand::SUCCESS;
  }
}
