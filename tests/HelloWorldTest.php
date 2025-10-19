<?php declare(strict_types=1);

namespace spriebsch\projectTemplate;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(HelloWorld::class)]
class HelloWorldTest extends TestCase
{
    public function test_something(): void
    {
        $helloWorld = new HelloWorld();

        $this->assertSame('Hello World', $helloWorld->say());
    }
}
