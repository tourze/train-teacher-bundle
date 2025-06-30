<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Integration\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tourze\TrainTeacherBundle\Service\PerformanceService;
use Tourze\TrainTeacherBundle\Tests\Integration\IntegrationTestKernel;

class PerformanceServiceTest extends KernelTestCase
{
    private PerformanceService $service;

    protected static function getKernelClass(): string
    {
        return IntegrationTestKernel::class;
    }

    protected function setUp(): void
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $this->service = static::getContainer()->get(PerformanceService::class);
    }

    public function testServiceIsInstantiable(): void
    {
        $this->assertInstanceOf(PerformanceService::class, $this->service);
    }

    public function testGetPerformanceStatisticsReturnsArray(): void
    {
        $result = $this->service->getPerformanceStatistics();
        $this->assertArrayHasKey('totalCount', $result);
    }

    public function testGetPerformanceHistoryReturnsArray(): void
    {
        $result = $this->service->getPerformanceHistory('teacher-id');
        $this->assertContainsOnly('object', $result);
    }

    public function testGetPerformanceRankingByPeriodReturnsArray(): void
    {
        $result = $this->service->getPerformanceRankingByPeriod(new \DateTime(), 10);
        $this->assertLessThanOrEqual(10, count($result));
    }

    public function testGeneratePerformanceReportReturnsArray(): void
    {
        $result = $this->service->generatePerformanceReport('teacher-id');
        $this->assertArrayHasKey('reportData', $result);
    }

    public function testCalculatePerformanceThrowsExceptionForInvalidData(): void
    {
        $this->expectException(\Exception::class);
        $this->service->calculatePerformance('invalid-teacher-id', new \DateTime());
    }
}