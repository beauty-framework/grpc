<?php
declare(strict_types=1);

namespace Beauty\GRPC\Console\Commands\Grpc;

use Beauty\Cli\CLI;
use Beauty\Cli\Console\AbstractCommand;

class InstallCommand extends AbstractCommand
{

    /**
     * @return string
     */
    public function name(): string
    {
        return 'grpc:install';
    }

    /**
     * @return string|null
     */
    public function description(): string|null
    {
        return 'Install gRPC';
    }

    /**
     * @param array $args
     * @return int
     */
    public function handle(array $args): int
    {
        $base = base_path('/' . DIRECTORY_SEPARATOR);
        $this->info('Installing gRPC...');

        $generated = $base . 'generated/';
        if (!is_dir($generated)) {
            mkdir($generated, 0777, true);
            $this->success('Created gRPC generated directory');
        }

        $this->info('Copying files...');

        $stub = __DIR__ . '/../../../../stubs/grpc-worker.php';
        $target = $base . '/workers/grpc-worker.php';

        copy($stub, $target);

        $this->success('Copied grpc-worker.php');

        $stub = __DIR__ . '/../../../../stubs/grpc.php';
        $target = $base . '/config/grpc.php';

        copy($stub, $target);

        $this->success('Created grpc.php');

        $composer = json_decode(file_get_contents($base . '/composer.json'), true);
        $psr4 = &$composer['autoload']['psr-4'];

        if (!isset($psr4['GRPC\\'])) {
            $psr4['GRPC\\'] = 'generated/';
            file_put_contents(
                $base . '/composer.json',
                json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL
            );
            $this->info('Added GRPC\\ to composer.json. Run ');
            $this->success('composer dump-autoload');
        } else {
            $this->error('GRPC\\ already exists in composer.json');
        }

        $rrBinary = $base . '/vendor/bin/rr';

        if (file_exists($rrBinary) && !file_exists($base.'/protoc-gen-php-grpc')) {
            $this->info('Downloading protoc binary...');
            $result = shell_exec("$rrBinary download-protoc-binary 2>&1");
            $this->success('Protoc binary downloaded');
        } else {
            $this->error('Unable to download protoc binary');
        }

        /*$makefile = $base . '/makefile';

        $grpcTarget = <<<MAKE

        grpc:gen:
        \tdocker-compose exec -u www-data \$(APP_CONTAINER) protoc --plugin=protoc-gen-php-grpc \\
        \t\t--php_out=./generated \\
        \t\t--php-grpc_out=./generated \\
        \t\t\$(filter-out \$@,\$(MAKECMDGOALS))
        
        MAKE;

        if (file_exists($makefile)) {
            $content = file_get_contents($makefile);

            if (!str_contains($content, 'grpc:gen:')) {
                file_put_contents($makefile, rtrim($content) . "\n\n" . $grpcTarget);
                $this->success('Added grpc:gen target to Makefile');
            } else {
                $this->error('grpc:gen target already exists in Makefile');
            }
        } else {
            file_put_contents($makefile, $grpcTarget);
            $this->success('Created grpc target');
        }*/

        return CLI::SUCCESS;
    }
}