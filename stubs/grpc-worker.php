<?php
declare(strict_types=1);

use Beauty\Core\Container\ContainerManager;
use Spiral\RoadRunner\GRPC\Invoker;
use Spiral\RoadRunner\GRPC\Server;

/** @var object{containerManager: ContainerManager, routerConfig: array, middlewares: array} $application */
$application = require __DIR__ . '/../bootstrap/kernel.php';
$grpcConfig = require base_path('config/grpc.php');

$di = $application->containerManager->getContainer();

$server = new Server(
    new Invoker(), [
    'debug' => true,
]);

$registry = new \Beauty\GRPC\GrpcServiceRegistry(
    $di,
    $server,
);

try {
    $registry->registerFromAttributes($grpcConfig['services']);

    $server->serve(\Spiral\RoadRunner\Worker::create());
} catch (Throwable $e) {
    var_dump($e);
}