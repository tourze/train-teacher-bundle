<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\TrainTeacherBundle\TrainTeacherBundle;

class TrainTeacherBundleTest extends TestCase
{
    public function testIsInstantiable(): void
    {
        $bundle = new TrainTeacherBundle();
        $this->assertInstanceOf(TrainTeacherBundle::class, $bundle);
    }

    public function testExtendsBundle(): void
    {
        $bundle = new TrainTeacherBundle();
        $this->assertInstanceOf(Bundle::class, $bundle);
    }

    public function testGetName(): void
    {
        $bundle = new TrainTeacherBundle();
        $this->assertEquals('TrainTeacherBundle', $bundle->getName());
    }

    public function testGetPath(): void
    {
        $bundle = new TrainTeacherBundle();
        $path = $bundle->getPath();
        $this->assertStringContainsString('train-teacher-bundle', $path);
    }
}