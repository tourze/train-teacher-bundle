<?php

namespace Tourze\TrainTeacherBundle\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class IntegrationTestCase extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return IntegrationTestKernel::class;
    }

    protected function setUp(): void
    {
        parent::setUp();
        
        $kernel = static::createKernel();
        $kernel->boot();
        $kernel->createSchema();
    }
}