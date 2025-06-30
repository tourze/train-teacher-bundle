<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Integration\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tourze\TrainTeacherBundle\Service\TeacherService;
use Tourze\TrainTeacherBundle\Tests\Integration\IntegrationTestKernel;

class TeacherServiceTest extends KernelTestCase
{
    private TeacherService $service;

    protected static function getKernelClass(): string
    {
        return IntegrationTestKernel::class;
    }

    protected function setUp(): void
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $this->service = static::getContainer()->get(TeacherService::class);
    }

    public function testServiceIsInstantiable(): void
    {
        $this->assertInstanceOf(TeacherService::class, $this->service);
    }

    public function testGetTeacherStatisticsReturnsArray(): void
    {
        $result = $this->service->getTeacherStatistics();
        $this->assertArrayHasKey('totalCount', $result);
    }

    public function testGetTeacherByIdThrowsExceptionForNonExistentTeacher(): void
    {
        $this->expectException(\Exception::class);
        $this->service->getTeacherById('non-existent-id');
    }

    public function testGetTeachersByStatusReturnsArray(): void
    {
        $result = $this->service->getTeachersByStatus('active');
        $this->assertContainsOnly('object', $result);
    }

    public function testGetRecentTeachersReturnsArray(): void
    {
        $result = $this->service->getRecentTeachers(5);
        $this->assertLessThanOrEqual(5, count($result));
    }

    public function testSearchTeachersReturnsArray(): void
    {
        $result = $this->service->searchTeachers('test');
        $this->assertContainsOnly('object', $result);
    }
}