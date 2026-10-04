<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Database;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Facade;

$capsule = Database::boot();
$container = $capsule->getContainer();
$manager = $capsule->getDatabaseManager();

// Даём фасаду Schema доступ к соединению Capsule
Facade::setFacadeApplication($container);
$container->instance('db', $manager);
$container->bind('db.schema', fn ($app) => $app['db']->connection()->getSchemaBuilder());

$repository = new DatabaseMigrationRepository($manager, 'migrations');
if (!$repository->repositoryExists()) {
  $repository->createRepository();
}

$migrator = new Migrator($repository, $manager, new Filesystem());

$paths = [__DIR__ . '/../database/migrations'];
$action = $argv[1] ?? 'migrate';

if ($action === 'rollback') {
  $migrator->rollback($paths);
} else {
  $migrator->run($paths);
}
