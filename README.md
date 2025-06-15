# Beauty gRPC

Support for gRPC services in the [Beauty Framework](https://github.com/beauty-framework/app), powered by [RoadRunner GRPC plugin](https://docs.roadrunner.dev/docs/plugins/grpc).

## ✨ Installation

Via composer:
```bash
make composer require beauty-framework/grpc
# or manually, if you don't use docker
composer require beauty-framework/grpc
```

Run the following command:

```bash
make beauty grpc:install
# or manually
./beauty grpc:install
```

```bash
make composer du
# or manually
composer dump-autoload
```

This will:

* 📁 Create the `generated/` directory for compiled gRPC PHP classes
* 🧪 Add `"GRPC\\": "generated/"` to `composer.json` autoload
* 📝 Copy the `grpc-worker.php` stub into the `workers/` directory
* 🔌 Run `vendor/bin/rr download-protoc-binary` to install the `protoc` binary

Add command to `makefile`:
```makefile
grpcgen:
	docker-compose exec -u www-data $(APP_CONTAINER) protoc --plugin=protoc-gen-php-grpc \
		--php_out=./generated \
		--php-grpc_out=./generated \
		$(filter-out $@,$(MAKECMDGOALS))
```

and to `PHONY` block append this command name: `grpcgen`

---

## 🛠 Usage

### 1. Compile your `.proto` files:

```bash
make grpcgen proto/helloworld.proto
```

To compile multiple:

```bash
make grpcgen proto/*.proto
```

Alternatively, use manually:

```bash
docker-compose exec -u www-data app protoc \
  --plugin=protoc-gen-php-grpc \
  --php_out=./generated \
  --php-grpc_out=./generated \
  proto/helloworld.proto
```

---

### 2. Configure `.rr.yaml`

```yaml
grpc:
  listen: tcp://127.0.0.1:9001
  pool:
    command: "php workers/grpc-worker.php"
  proto:
    - "proto/helloworld.proto"
```

---

### 3. Start the server

```bash
make stop && make up
# or manually
./vendor/bin/rr serve
```

This will start the gRPC server at `127.0.0.1:9001`.

---

## 🔧 Example Service

```php
namespace App\Controllers\GRPC;

use GRPC\Greeter\GreeterInterface;
use GRPC\Greeter\HelloRequest;
use GRPC\Greeter\HelloReply;
use Spiral\RoadRunner\GRPC\ContextInterface;

class Greeter implements GreeterInterface
{
    public function SayHello(ContextInterface $ctx, HelloRequest $in): HelloReply
    {
        return new HelloReply([
            'message' => 'Hello ' . $in->getName(),
        ]);
    }
}
```

---

## 📎 Documentation

Official RoadRunner gRPC plugin docs:
🔗 [https://docs.roadrunner.dev/docs/plugins/grpc](https://docs.roadrunner.dev/docs/plugins/grpc)

---

## 🛋 Notes

* Services are automatically discovered from `app/Controllers/GRPC/**/*.php` via `#[GrpcService(...)]`
* All generated classes are stored in `generated/`
* Re-run `make grpcgen` whenever you change `.proto` files

---

## ❤️ Stack

* [roadrunner-php/grpc](https://github.com/roadrunner-php/grpc)
* [google/protobuf-php](https://github.com/protocolbuffers/protobuf-php)
* [beauty-framework/app](https://github.com/beauty-framework/app)
