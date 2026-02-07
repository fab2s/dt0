<?php

declare(strict_types=1);

/*
 * This file is part of fab2s/dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace Tests;

use Exception;
use fab2s\Dt0\Attribute\WithProp;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionException;
use Tests\Artifacts\DummyWithDt0;

class WithTest extends TestCase
{
    /**
     * @throws ReflectionException
     * @throws Exception
     */
    #[DataProvider('withProvider')]
    public function test_with(array $args)
    {
        $dto = DummyWithDt0::fromArray(['publicReadonlyString' => 'publicReadonlyString']);
        $this->assertSame('publicReadonlyString', $dto->publicReadonlyString);
        $dto->setProtectedStringWithGetter('protectedStringWithGetter')
            ->setProtectedStringWithoutGetter('protectedStringWithoutGetter')
        ;

        $this->assertSame([
            'publicReadonlyString'         => 'publicReadonlyString',
            'protectedStringWithGetter'    => 'protectedStringWithGetter',
            'protectedStringWithoutGetter' => 'protectedStringWithoutGetter',
        ], $dto->toArray());

        $dto->without('protectedStringWithoutGetter');

        $this->assertSame([
            'publicReadonlyString'      => 'publicReadonlyString',
            'protectedStringWithGetter' => 'protectedStringWithGetter',
        ], $dto->toArray());

        $dto->with('protectedStringWithoutGetter');

        $this->assertSame([
            'publicReadonlyString'         => 'publicReadonlyString',
            'protectedStringWithGetter'    => 'protectedStringWithGetter',
            'protectedStringWithoutGetter' => 'protectedStringWithoutGetter',
        ], $dto->toArray());

        $dto->without('protectedStringWithoutGetter')
            ->with('protectedStringWithoutGetter', false)
        ;

        $this->assertSame([
            'publicReadonlyString'         => 'publicReadonlyString',
            'protectedStringWithGetter'    => 'protectedStringWithGetter',
            'protectedStringWithoutGetter' => 'protectedStringWithoutGetter',
        ], $dto->toArray());

        $dto->without('protectedStringWithGetter');
        $this->assertSame([
            'publicReadonlyString'         => 'publicReadonlyString',
            'protectedStringWithoutGetter' => 'protectedStringWithoutGetter',
        ], $dto->toArray());

        $dto->with('protectedStringWithGetter', true);

        // can mess with order if we go at it ...
        $this->assertSame([
            'publicReadonlyString'         => 'publicReadonlyString',
            'protectedStringWithoutGetter' => 'protectedStringWithoutGetter',
            'protectedStringWithGetter'    => 'protectedStringWithGetter',
        ], $dto->toArray());

        $dto->without('protectedStringWithGetter')
            ->setProtectedStringWithGetter('protectedStringWithGetterFromProtected')
            ->with('protectedStringWithGetter', 'getProtectedStringWithProtectedGetter')
        ;

        $this->assertSame([
            'publicReadonlyString'         => 'publicReadonlyString',
            'protectedStringWithoutGetter' => 'protectedStringWithoutGetter',
            'protectedStringWithGetter'    => 'protectedStringWithGetterFromProtected',
        ], $dto->toArray());

        $dto->without('publicReadonlyString');
        $this->assertSame([
            'protectedStringWithoutGetter' => 'protectedStringWithoutGetter',
            'protectedStringWithGetter'    => 'protectedStringWithGetterFromProtected',
        ], $dto->toArray());

        $dto->clearWithout();
        $this->assertSame([
            'publicReadonlyString'         => 'publicReadonlyString',
            'protectedStringWithoutGetter' => 'protectedStringWithoutGetter',
            'protectedStringWithGetter'    => 'protectedStringWithGetterFromProtected',
        ], $dto->toArray());

        $dto->clearWith();
        $this->assertSame([
            'publicReadonlyString' => 'publicReadonlyString',
        ], $dto->toArray());

        $dto->with('extra', function (DummyWithDt0 $instance): string {
            return 'content:' . $instance->publicReadonlyString;
        });

        $this->assertSame([
            'publicReadonlyString' => 'publicReadonlyString',
            'extra'                => 'content:publicReadonlyString',
        ], $dto->toArray());

        $dto->with('with', WithProp::make(name: 'protectedStringWithoutGetter'));
        $this->assertSame([
            'publicReadonlyString' => 'publicReadonlyString',
            'extra'                => 'content:publicReadonlyString',
            'with'                 => 'protectedStringWithoutGetter',
        ], $dto->toArray());

        $dto->with('protectedStringWithoutGetter')
            ->only('protectedStringWithoutGetter')
        ;

        $this->assertSame([
            'protectedStringWithoutGetter' => 'protectedStringWithoutGetter',
        ], $dto->toArray());

        $this->assertSame($dto->toArray(), $dto->toJsonArray());

        // dd($dto->toArray());
    }

    public static function withProvider(): array
    {
        return [
            [[]]
        ];
    }
}
