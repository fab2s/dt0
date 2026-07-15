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
| Simple DTO (8 props, 5 casts) | 2.6 µs | 17.1 µs | **~6.5x faster** |
| Complex DTO (nested + arrays) | 11.6 µs | 67.7 µs | **~5.8x faster** |
| Round-trip (json→dto→json) | 5.0 µs | 31.8 µs | **~6.4x faster** |

### Repeated Serialization (same instance)

| Operation | Dt0 | spatie/laravel-data | Speedup |
|-----------|-----|---------------------|--------|
| toArray() (simple) | 0.085 µs | 9.1 µs | **~107x faster** |
| toArray() (nested) | 0.103 µs | 30.1 µs | **~292x faster** |
| toJson() | 0.034 µs | 9.3 µs | **~277x faster** |

## Understanding the Results

**Hydration (~6x faster)**: Dt0's compile-once architecture pre-computes property mappings, casters, and defaults. This is the baseline performance gain you get on every DTO creation.

**Repeated serialization (~107-292x faster)**: When serializing the same instance multiple times, Dt0 caches the output structure on first call. Subsequent calls reuse the cache. This applies to scenarios like:
- Logging the same DTO at multiple points
- Serializing for both response and event dispatch
- Caching serialized output

The nested DTO shows the largest speedup (~292x) because spatie/laravel-data must traverse and transform the entire object graph on every call, while Dt0 simply returns the cached result.

**Single-use serialization (~6x faster)**: For one-shot serialization (create DTO, serialize once, discard), expect performance similar to hydration benchmarks.

The honest baseline is **~5-10x faster** across typical operations, with massive gains for repeated serialization scenarios.
