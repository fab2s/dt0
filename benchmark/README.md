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

## Results (PHP 8.5, 10,000 iterations)

Absolute timings are hardware-dependent; the speedup ratios are the meaningful figure.

### Hydration & Round-trip

| Operation | Dt0 | spatie/laravel-data | Speedup |
|-----------|-----|---------------------|--------|
| Simple DTO (8 props, 5 casts) | 13.04 µs | 84.37 µs | **~6.5x faster** |
| Complex DTO (nested + arrays) | 56.89 µs | 272.4 µs | **~4.8x faster** |
| Round-trip (json→dto→json) | 19.53 µs | 143.8 µs | **~7.4x faster** |

### Repeated Serialization (same instance)

| Operation | Dt0 | spatie/laravel-data | Speedup |
|-----------|-----|---------------------|--------|
| toArray() (simple) | 0.457 µs | 52.03 µs | **~113.8x faster** |
| toArray() (nested) | 0.479 µs | 154.6 µs | **~322.7x faster** |
| toJson() | 0.275 µs | 52.23 µs | **~190x faster** |

## Understanding the Results

**Hydration (~6x faster)**: Dt0's compile-once architecture pre-computes property mappings, casters, and defaults. This is the baseline performance gain you get on every DTO creation.

**Repeated serialization (~100-300x faster)**: When serializing the same instance multiple times, Dt0 caches the output structure on first call. Subsequent calls reuse the cache. This applies to scenarios like:
- Logging the same DTO at multiple points
- Serializing for both response and event dispatch
- Caching serialized output

The nested DTO shows the largest speedup because spatie/laravel-data must traverse and transform the entire object graph on every call, while Dt0 simply returns the cached result.

**Single-use serialization (~6x faster)**: For one-shot serialization (create DTO, serialize once, discard), expect performance similar to hydration benchmarks.

The honest baseline is **~5-10x faster** across typical operations, with massive gains for repeated serialization scenarios.
