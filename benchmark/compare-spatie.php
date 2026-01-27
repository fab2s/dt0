<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

require_once __DIR__ . '/../vendor/autoload.php';

use fab2s\Dt0\Attribute\Cast;
use fab2s\Dt0\Caster\ArrayOfCaster;
use fab2s\Dt0\Caster\DateTimeCaster;
use fab2s\Dt0\Caster\DateTimeFormatCaster;
use fab2s\Dt0\Caster\ScalarType;
use fab2s\Dt0\Dt0;
use fab2s\Dt0\Exception\Dt0Exception;
use Orchestra\Testbench\TestCase;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\LaravelDataServiceProvider;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

// ============================================================================
// Dt0 Definitions
// ============================================================================

class Dt0User extends Dt0
{
    #[Cast(renameFrom: 'user_id')]
    public readonly int $id;

    #[Cast(renameFrom: 'full_name')]
    public readonly string $name;
    public readonly string $email;

    #[Cast(in: DateTimeCaster::class, out: new DateTimeFormatCaster('Y-m-d'))]
    public readonly DateTimeImmutable $createdAt;

    #[Cast(in: DateTimeCaster::class, out: new DateTimeFormatCaster('Y-m-d H:i:s'))]
    public readonly ?DateTimeImmutable $updatedAt;

    #[Cast(default: 'user')]
    public readonly string $role;

    #[Cast(default: true)]
    public readonly bool $isActive;

    #[Cast(in: new ArrayOfCaster(ScalarType::string), default: [])]
    public readonly array $permissions;
}

class Dt0LineItem extends Dt0
{
    public readonly string $sku;
    public readonly string $name;
    public readonly int $quantity;
    public readonly float $unitPrice;

    #[Cast(default: 0.0)]
    public readonly float $discount;
}

class Dt0Address extends Dt0
{
    #[Cast(renameFrom: 'street_address')]
    public readonly string $street;
    public readonly string $city;

    #[Cast(renameFrom: 'postal_code')]
    public readonly ?string $zipCode;
    public readonly string $country;

    #[Cast(default: null)]
    public readonly ?string $state;
}

class Dt0Order extends Dt0
{
    #[Cast(renameFrom: 'order_id')]
    public readonly string $orderId;
    public readonly Dt0User $user;
    public readonly Dt0Address $shippingAddress;
    public readonly Dt0Address $billingAddress;

    #[Cast(in: new ArrayOfCaster(Dt0LineItem::class))]
    public readonly array $items;

    #[Cast(in: DateTimeCaster::class, out: new DateTimeFormatCaster('Y-m-d\TH:i:s\Z'))]
    public readonly DateTimeImmutable $placedAt;

    #[Cast(in: DateTimeCaster::class, out: new DateTimeFormatCaster('Y-m-d\TH:i:s\Z'), default: null)]
    public readonly ?DateTimeImmutable $shippedAt;

    #[Cast(default: 'pending')]
    public readonly string $status;

    #[Cast(default: 'USD')]
    public readonly string $currency;

    #[Cast(in: new ArrayOfCaster(ScalarType::string), default: [])]
    public readonly array $tags;
}

// ============================================================================
// Spatie Definitions (equivalent structure)
// ============================================================================

class SpatieUser extends Data
{
    public function __construct(
        #[MapInputName('user_id')]
        public int $id,
        #[MapInputName('full_name')]
        public string $name,
        public string $email,
        #[WithCast(DateTimeInterfaceCast::class)]
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'Y-m-d')]
        public DateTimeImmutable $createdAt,
        #[WithCast(DateTimeInterfaceCast::class)]
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'Y-m-d H:i:s')]
        public ?DateTimeImmutable $updatedAt = null,
        public string $role = 'user',
        public bool $isActive = true,
        public array $permissions = [],
    ) {}
}

class SpatieLineItem extends Data
{
    public function __construct(
        public string $sku,
        public string $name,
        public int $quantity,
        public float $unitPrice,
        public float $discount = 0.0,
    ) {}
}

class SpatieAddress extends Data
{
    public function __construct(
        #[MapInputName('street_address')]
        public string $street,
        public string $city,
        #[MapInputName('postal_code')]
        public ?string $zipCode,
        public string $country,
        public ?string $state = null,
    ) {}
}

class SpatieOrder extends Data
{
    public function __construct(
        #[MapInputName('order_id')]
        public string $orderId,
        public SpatieUser $user,
        public SpatieAddress $shippingAddress,
        public SpatieAddress $billingAddress,
        #[DataCollectionOf(SpatieLineItem::class)]
        public array $items,
        #[WithCast(DateTimeInterfaceCast::class)]
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'Y-m-d\TH:i:sP')]
        public DateTimeImmutable $placedAt,
        #[WithCast(DateTimeInterfaceCast::class)]
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'Y-m-d\TH:i:sP')]
        public ?DateTimeImmutable $shippedAt = null,
        public string $status = 'pending',
        public string $currency = 'USD',
        public array $tags = [],
    ) {}
}

// ============================================================================
// Bootstrap Laravel via Testbench
// ============================================================================

class BenchmarkRunner extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [LaravelDataServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('data.validation_strategy', 'disabled');
    }

    public function bootstrap(): void
    {
        $this->setUpTheTestEnvironment();
    }

    /**
     * @throws Dt0Exception
     * @throws ReflectionException
     */
    public function runBenchmarks(): void
    {
        echo "# Dt0 vs spatie/laravel-data Benchmark\n\n";
        echo 'PHP ' . PHP_VERSION . ", 10,000 iterations per test\n\n";

        // Test inputs
        $userInput = [
            'user_id'     => 1,
            'full_name'   => 'John Doe',
            'email'       => 'john@example.com',
            'createdAt'   => '2024-01-15T10:30:00+00:00',
            'updatedAt'   => '2024-06-20T15:45:00+00:00',
            'permissions' => ['read', 'write', 'delete'],
        ];

        $orderInput = [
            'order_id' => 'ORD-2024-001234',
            'user'     => [
                'user_id'     => 42,
                'full_name'   => 'Jane Smith',
                'email'       => 'jane@example.com',
                'createdAt'   => '2023-03-10T08:00:00+00:00',
                'updatedAt'   => '2024-01-15T12:30:00+00:00',
                'role'        => 'premium',
                'isActive'    => true,
                'permissions' => ['read', 'write'],
            ],
            'shippingAddress' => [
                'street_address' => '123 Main Street',
                'city'           => 'Boston',
                'postal_code'    => '02101',
                'country'        => 'USA',
                'state'          => 'MA',
            ],
            'billingAddress' => [
                'street_address' => '456 Oak Avenue',
                'city'           => 'Cambridge',
                'postal_code'    => '02139',
                'country'        => 'USA',
                'state'          => 'MA',
            ],
            'items' => [
                ['sku' => 'WIDGET-001', 'name' => 'Blue Widget', 'quantity' => 2, 'unitPrice' => 29.99, 'discount' => 5.00],
                ['sku' => 'GADGET-042', 'name' => 'Super Gadget', 'quantity' => 1, 'unitPrice' => 149.99],
                ['sku' => 'THING-007', 'name' => 'Mystery Thing', 'quantity' => 3, 'unitPrice' => 9.99, 'discount' => 1.50],
            ],
            'placedAt' => '2024-01-15T14:30:00+00:00',
            'tags'     => ['priority', 'gift-wrap', 'express'],
        ];

        $dt0User     = Dt0User::fromArray($userInput);
        $spatieUser  = SpatieUser::from($userInput);
        $dt0Order    = Dt0Order::fromArray($orderInput);
        $spatieOrder = SpatieOrder::from($orderInput);
        $userJson    = json_encode($userInput);

        // Hydration benchmarks
        $hydrationResults = [
            $this->compare('Simple DTO (8 props, 5 casts)', fn () => Dt0User::fromArray($userInput), fn () => SpatieUser::from($userInput)),
            $this->compare('Complex DTO (nested + arrays)', fn () => Dt0Order::fromArray($orderInput), fn () => SpatieOrder::from($orderInput)),
            $this->compare('Round-trip (json→dto→json)', fn () => Dt0User::fromJson($userJson)->toJson(), fn () => SpatieUser::from(json_decode($userJson, true))->toJson()),
        ];

        // Serialization benchmarks
        $serializationResults = [
            $this->compare('toArray() (simple)', fn () => $dt0User->toArray(), fn () => $spatieUser->toArray()),
            $this->compare('toArray() (nested)', fn () => $dt0Order->toArray(), fn () => $spatieOrder->toArray()),
            $this->compare('toJson()', fn () => $dt0User->toJson(), fn () => $spatieUser->toJson()),
        ];

        // Output markdown tables
        echo "## Hydration & Round-trip\n\n";
        $this->printMarkdownTable($hydrationResults);

        echo "\n## Repeated Serialization (same instance)\n\n";
        $this->printMarkdownTable($serializationResults);

        echo "\nMemory peak: " . $this->formatBytes(memory_get_peak_usage(true)) . "\n";
    }

    private function compare(string $name, callable $dt0Fn, callable $spatieFn, int $iterations = 10000): array
    {
        $dt0    = $this->benchmark($dt0Fn, $iterations);
        $spatie = $this->benchmark($spatieFn, $iterations);
        $ratio  = round($spatie['per_op_us'] / $dt0['per_op_us'], 1);

        return [
            'name'   => $name,
            'dt0'    => $dt0['per_op_us'],
            'spatie' => $spatie['per_op_us'],
            'ratio'  => $ratio,
        ];
    }

    private function benchmark(callable $fn, int $iterations = 10000): array
    {
        // Warmup
        for ($i = 0; $i < min(100, $iterations / 10); $i++) {
            $fn();
        }

        gc_collect_cycles();
        $start = hrtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $fn();
        }

        $end = hrtime(true);

        $timeMs = ($end - $start) / 1_000_000;
        $perOp  = $timeMs         / $iterations;

        return [
            'per_op_us' => round($perOp * 1000, 1),
        ];
    }

    private function printMarkdownTable(array $results): void
    {
        echo "| Operation | Dt0 | spatie/laravel-data | Speedup |\n";
        echo "|-----------|-----|---------------------|--------|\n";

        foreach ($results as $row) {
            $dt0Str    = $row['dt0']    >= 1000 ? number_format($row['dt0']) . ' µs' : $row['dt0'] . ' µs';
            $spatieStr = $row['spatie'] >= 1000 ? number_format($row['spatie']) . ' µs' : $row['spatie'] . ' µs';
            $speedup   = "**~{$row['ratio']}x faster**";

            echo "| {$row['name']} | {$dt0Str} | {$spatieStr} | {$speedup} |\n";
        }
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i     = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}

// Run the benchmark
$runner = new BenchmarkRunner('benchmark');
$runner->bootstrap();
$runner->runBenchmarks();
