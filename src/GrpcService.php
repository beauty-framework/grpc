<?php
declare(strict_types=1);

namespace Beauty\GRPC;

#[\Attribute(\Attribute::TARGET_CLASS)]
class GrpcService
{
    /**
     * @param string $name
     */
    public function __construct(public string $name) {}
}