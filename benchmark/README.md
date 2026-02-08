# Dt0 Benchmarks

## Running the Benchmark

```shell
php benchmark/compare-spatie.php
```

The benchmark uses orchestra/testbench to bootstrap Laravel for spatie/laravel-data.

## What's Being Measured

The benchmark uses realistic DTOs with:
- Property renaming (`renameFrom`)
- DateTime casting with input/output formatters
- Arrays of nested DTOs
- Multiple defaults
- Scalar array casting

| Test | Description |
|------|-------------|
| Simple DTO (8 props, 5 casts) | User DTO with renaming, dates, arrays, defaults |
| Complex DTO (nested + arrays) | Order with user, 2 addresses, 3 line items |
| toArray() / toJson() | Serialization with output transformers |
| Round-trip | JSON → DTO → JSON |

## Results (PHP 8.4, 10,000 iterations)

### Hydration & Round-trip

| Operation | Dt0 | spatie/laravel-data | Speedup |
|-----------|-----|---------------------|--------|
| Simple DTO (8 props, 5 casts) | 136.9 µs | 1,117 µs | **~8.2x faster** |
| Complex DTO (nested + arrays) | 711.5 µs | 3,494 µs | **~4.9x faster** |
| Round-trip (json→dto→json) | 246.8 µs | 1,960 µs | **~7.9x faster** |

### Repeated Serialization (same instance)

| Operation | Dt0 | spatie/laravel-data | Speedup |
|-----------|-----|---------------------|--------|
| toArray() (simple) | 3.5 µs | 625.8 µs | **~178.8x faster** |
| toArray() (nested) | 3.6 µs | 1,978 µs | **~549.3x faster** |
| toJson() | 2.4 µs | 627.5 µs | **~261.5x faster** |

## Understanding the Results

**Hydration (~5-8x faster)**: Dt0's compile-once architecture pre-computes property mappings, casters, and defaults. This is the baseline performance gain you get on every DTO creation.

**Repeated serialization (~178-549x faster)**: When serializing the same instance multiple times, Dt0 caches the output structure on first call. Subsequent calls reuse the cache. This applies to scenarios like:
- Logging the same DTO at multiple points
- Serializing for both response and event dispatch
- Caching serialized output

The nested DTO shows the largest speedup (~549x) because spatie/laravel-data must traverse and transform the entire object graph on every call, while Dt0 simply returns the cached result.

**Single-use serialization (~10x faster)**: For one-shot serialization (create DTO, serialize once, discard), expect performance similar to hydration benchmarks.

The honest baseline is **~5-10x faster** across typical operations, with massive gains for repeated serialization scenarios.
