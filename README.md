# Revolt Playground

This repository contains some example usages of the Revolt event loop. The focus on this is mostly
on use-cases that currently exist within Drupal and the changes we might need to make within the 
Drupal framework to integrate with the Revolt event-loop.

## Installation and Usage
The repository is set-up as a Symfony Console application with each demo being a separate 
sub-command. The idea is that being able to see how code runs and play with it is the best way 
to demonstrate what happens with asynchronous things.

```shell
git clone git@github.com:Kingdutch/revolt-playground.git
cd revolt-playground
composer install
./index.php list playground
```

## Included Demo's

The following demos are included. All the code that is specific to the demo can be found in the 
`src/Demo/<demoname>` directories. Code that is expected to land in Drupal core and is needed in 
more than one demo can be found in the `Drupal` directory.

### Bootstrap
The bootstrap demo shows how the start of a webserver request's `index.php` (and similarly how 
command line applications like Drush) will need to be adjusted to ensure that asynchronous 
processes can properly finish.

It contains the `demo:bootstrap:no-loop` command which shows how some async tasks are not 
completed because the process exits too quickly. The `demo:bootstrap:with-loop` command shows 
how adding `EventLoop::run()` at the end of the request ensures that ongoing async tasks can finish.

### Suspension Order
The `demo:suspension-order` exists to show that the usage of `getSuspension` might be slightly 
counter-intuitive and that it can not be used to guarantee ordering of handling the results of 
tasks. 

To ensure results are handled in a specific order, higher-level primitives must be created. 
Amp's concurrent function is one such example but [similar behaviour can easily be added to 
Drupal without external dependencies](https://github.com/revoltphp/event-loop/issues/56#issuecomment-1053667638).
