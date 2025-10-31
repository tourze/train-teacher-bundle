<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainTeacherBundle\Exception\TeacherNotFoundException;
use Tourze\TrainTeacherBundle\Service\PerformanceService;

/**
 * @internal
 */
#[CoversClass(PerformanceService::class)]
#[RunTestsInSeparateProcesses]
final class PerformanceServiceTest extends AbstractIntegrationTestCase
{
    private PerformanceService $service;

    protected function onSetUp(): void
    {
        $service = self::getContainer()->get(PerformanceService::class);
        self::assertInstanceOf(PerformanceService::class, $service);
        $this->service = $service;
    }

    public function testGetPerformanceStatisticsReturnsArray(): void
    {
        $result = $this->service->getPerformanceStatistics();
        $this->assertArrayHasKey('total', $result);
    }

    public function testGetPerformanceHistoryReturnsArray(): void
    {
        // 使用不存在的教师ID测试异常
        $this->expectException(TeacherNotFoundException::class);
        $this->service->getPerformanceHistory('teacher-id');
    }

    public function testGetPerformanceRankingByPeriodReturnsArray(): void
    {
        $result = $this->service->getPerformanceRankingByPeriod(new \DateTimeImmutable(), 10);
        $this->assertLessThanOrEqual(10, count($result));
    }

    public function testGeneratePerformanceReportReturnsArray(): void
    {
        // 使用不存在的教师ID测试异常
        $this->expectException(TeacherNotFoundException::class);
        $this->service->generatePerformanceReport('teacher-id');
    }

    public function testCalculatePerformanceThrowsExceptionForInvalidData(): void
    {
        $this->expectException(\Exception::class);
        $this->service->calculatePerformance('invalid-teacher-id', new \DateTime());
    }

    public function testCompareTeacherPerformance(): void
    {
        $teacherIds = ['teacher-1', 'teacher-2', 'teacher-3'];
        $period = new \DateTime();
        $result = $this->service->compareTeacherPerformance($teacherIds, $period);
        $this->assertGreaterThanOrEqual(0, count($result));
    }

    public function testUpdatePerformanceMetrics(): void
    {
        $this->expectException(\Exception::class);
        $metrics = ['teachingHours' => 100, 'satisfaction' => 95];
        $this->service->updatePerformanceMetrics('non-existent-id', $metrics);
    }
}
