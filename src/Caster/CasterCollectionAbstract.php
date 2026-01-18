<?php

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Caster;

use ArrayIterator;
use fab2s\Dt0\Dt0;
use fab2s\Dt0\Exception\CasterException;
use Traversable;

abstract class CasterCollectionAbstract extends CasterAbstract implements CasterCollectionInterface
{
    /**
     * @var array<class-string<CasterInterface>, CasterInterface>
     */
    protected array $casters = [];

    /**
     * @param CasterInterface|class-string<CasterInterface> ...$casters
     *
     * @throws CasterException
     */
    public function __construct(CasterInterface|string ...$casters)
    {
        foreach ($casters as $caster) {
            $this->push($caster);
        }
    }

    /**
     * @throws CasterException
     */
    public static function make(CasterInterface|string ...$casters): static
    {
        return new static(...$casters);
    }

    public function toCaster(): ?static
    {
        return $this->isEmpty() ? null : $this;
    }

    /**
     * @throws CasterException
     */
    public function push(CasterInterface|string $caster): static
    {
        if (is_string($caster)) {
            if (! is_subclass_of($caster, CasterInterface::class)) {
                throw new CasterException('Argument $caster must be a string or a CasterInterface instance');
            }

            $caster = new $caster;
        }

        $this->casters[get_class($caster)] = $caster;

        return $this;
    }

    public function isEmpty(): bool
    {
        return empty($this->casters);
    }

    public function count(): int
    {
        return count($this->casters);
    }

    public function has(CasterInterface|string $caster): bool
    {
        return isset($this->casters[Dt0::fqn($caster)]);
    }

    public function get(CasterInterface|string $caster): ?CasterInterface
    {
        return $this->casters[Dt0::fqn($caster)] ?? null;
    }

    public function delete(CasterInterface|string $caster): static
    {
        unset($this->casters[Dt0::fqn($caster)]);

        return $this;
    }

    public function cast(mixed $value, Dt0|array|null $data = null): mixed
    {
        foreach ($this->casters as $caster) {
            $value = $caster->cast($value, $data);
        }

        return $value;
    }

    public function setDeclaringFqn(string $declaringFqn): static
    {
        foreach ($this->casters as $caster) {
            $caster->setDeclaringFqn($declaringFqn);
        }

        return parent::setDeclaringFqn($declaringFqn);
    }

    public function setPropName(string $propName): static
    {
        foreach ($this->casters as $caster) {
            $caster->setPropName($propName);
        }

        return parent::setPropName($propName);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->casters);
    }
}
