<?php
declare(strict_types=1);

namespace Beauty\GRPC\Console;

use Beauty\Cli\Console\Contracts\CommandsRegistryInterface;
use Beauty\GRPC\Console\Commands\Grpc\InstallCommand;

class RegistryCommands implements CommandsRegistryInterface
{

    /**
     * @return \class-string[]
     */
    public static function commands(): array
    {
        return [
            InstallCommand::class,
        ];
    }
}