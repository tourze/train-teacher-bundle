<?php

namespace Tourze\TrainTeacherBundle\Tests\Unit\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\TrainTeacherBundle\Entity\Teacher;
use Tourze\TrainTeacherBundle\Entity\TeacherPerformance;
use Tourze\TrainTeacherBundle\Repository\TeacherPerformanceRepository;
use Tourze\TrainTeacherBundle\Service\EvaluationService;
use Tourze\TrainTeacherBundle\Service\PerformanceService;
use Tourze\TrainTeacherBundle\Service\TeacherService;

/**
 * PerformanceService单元测试
 */
class PerformanceServiceTest extends TestCase
{
    private PerformanceService $performanceService;
    private EntityManagerInterface&MockObject $entityManager;
    private TeacherPerformanceRepository&MockObject $performanceRepository;
    private TeacherService&MockObject $teacherService;
    private EvaluationService&MockObject $evaluationService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->performanceRepository = $this->createMock(TeacherPerformanceRepository::class);
        $this->teacherService = $this->createMock(TeacherService::class);
        $this->evaluationService = $this->createMock(EvaluationService::class);
        
        $this->performanceService = new PerformanceService(
            $this->entityManager,
            $this->performanceRepository,
            $this->teacherService,
            $this->evaluationService
        );
    }

    public function test_calculate_performance_creates_new_record(): void
    {
        $teacherId = 'teacher_123';
        $period = new \DateTime('2024-01-01');
        $teacher = new Teacher();
        $teacher->setId($teacherId);

        $this->teacherService
            ->expects($this->once())
            ->method('getTeacherById')
            ->with($teacherId)
            ->willReturn($teacher);

        $this->performanceRepository
            ->expects($this->once())
            ->method('findByTeacherAndPeriod')
            ->with($teacher, $period)
            ->willReturn(null);

        $this->evaluationService
            ->expects($this->once())
            ->method('calculateAverageEvaluation')
            ->with($teacherId)
            ->willReturn(4.5);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(TeacherPerformance::class));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $performance = $this->performanceService->calculatePerformance($teacherId, $period);

        $this->assertInstanceOf(TeacherPerformance::class, $performance);
        $this->assertEquals($teacher, $performance->getTeacher());
        $this->assertEquals($period, $performance->getPerformancePeriod());
        $this->assertEquals(4.5, $performance->getAverageEvaluation());
        $this->assertGreaterThan(0, $performance->getPerformanceScore());
        $this->assertNotEmpty($performance->getPerformanceLevel());
    }

    public function test_calculate_performance_updates_existing_record(): void
    {
        $teacherId = 'teacher_123';
        $period = new \DateTime('2024-01-01');
        $teacher = new Teacher();
        $teacher->setId($teacherId);

        $existingPerformance = new TeacherPerformance();
        $existingPerformance->setId('performance_123');
        $existingPerformance->setTeacher($teacher);
        $existingPerformance->setPerformancePeriod($period);

        $this->teacherService
            ->expects($this->once())
            ->method('getTeacherById')
            ->with($teacherId)
            ->willReturn($teacher);

        $this->performanceRepository
            ->expects($this->once())
            ->method('findByTeacherAndPeriod')
            ->with($teacher, $period)
            ->willReturn($existingPerformance);

        $this->evaluationService
            ->expects($this->once())
            ->method('calculateAverageEvaluation')
            ->with($teacherId)
            ->willReturn(4.2);

        $this->entityManager
            ->expects($this->never())
            ->method('persist');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $performance = $this->performanceService->calculatePerformance($teacherId, $period);

        $this->assertSame($existingPerformance, $performance);
        $this->assertEquals(4.2, $performance->getAverageEvaluation());
    }

    public function test_update_performance_metrics(): void
    {
        $performanceId = 'performance_123';
        $metrics = [
            'teachingHours' => 120,
            'studentSatisfaction' => 4.5,
            'courseCompletionRate' => 0.95,
        ];

        $performance = new TeacherPerformance();
        $performance->setId($performanceId);
        $performance->setAverageEvaluation(4.3);

        $this->performanceRepository
            ->expects($this->once())
            ->method('find')
            ->with($performanceId)
            ->willReturn($performance);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $result = $this->performanceService->updatePerformanceMetrics($performanceId, $metrics);

        $this->assertSame($performance, $result);
        $this->assertEquals($metrics, $result->getPerformanceMetrics());
        $this->assertGreaterThan(0, $result->getPerformanceScore());
        $this->assertNotEmpty($result->getPerformanceLevel());
    }

    public function test_update_performance_metrics_throws_exception_for_nonexistent_performance(): void
    {
        $performanceId = 'nonexistent_performance';
        $metrics = ['teachingHours' => 120];

        $this->performanceRepository
            ->expects($this->once())
            ->method('find')
            ->with($performanceId)
            ->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('绩效记录不存在: nonexistent_performance');

        $this->performanceService->updatePerformanceMetrics($performanceId, $metrics);
    }

    public function test_get_performance_history(): void
    {
        $teacherId = 'teacher_123';
        $teacher = new Teacher();
        $teacher->setId($teacherId);

        $performances = [
            $this->createMockPerformance(4.5),
            $this->createMockPerformance(4.3),
        ];

        $this->teacherService
            ->expects($this->once())
            ->method('getTeacherById')
            ->with($teacherId)
            ->willReturn($teacher);

        $this->performanceRepository
            ->expects($this->once())
            ->method('findByTeacher')
            ->with($teacher)
            ->willReturn($performances);

        $result = $this->performanceService->getPerformanceHistory($teacherId);

        $this->assertSame($performances, $result);
    }

    public function test_compare_teacher_performance(): void
    {
        $teacherIds = ['teacher_1', 'teacher_2', 'teacher_3'];
        $period = new \DateTime('2024-01-01');
        $comparison = [
            ['teacherId' => 'teacher_1', 'performanceScore' => 4.5],
            ['teacherId' => 'teacher_2', 'performanceScore' => 4.3],
            ['teacherId' => 'teacher_3', 'performanceScore' => 4.7],
        ];

        $this->performanceRepository
            ->expects($this->once())
            ->method('compareTeacherPerformance')
            ->with($teacherIds, $period)
            ->willReturn($comparison);

        $result = $this->performanceService->compareTeacherPerformance($teacherIds, $period);

        $this->assertSame($comparison, $result);
    }

    public function test_generate_performance_report_with_data(): void
    {
        $teacherId = 'teacher_123';
        $teacher = new Teacher();
        $teacher->setId($teacherId);
        $teacher->setTeacherName('张三');
        $teacher->setTeacherCode('T001');
        $teacher->setTeacherType('专职');

        $performances = [
            $this->createMockPerformance(4.5),
            $this->createMockPerformance(4.3),
        ];

        $trend = [
            ['period' => '2024-01', 'score' => 4.5],
            ['period' => '2023-12', 'score' => 4.3],
        ];

        $this->teacherService
            ->expects($this->once())
            ->method('getTeacherById')
            ->with($teacherId)
            ->willReturn($teacher);

        $this->performanceRepository
            ->expects($this->once())
            ->method('findByTeacher')
            ->with($teacher)
            ->willReturn($performances);

        $this->performanceRepository
            ->expects($this->once())
            ->method('getPerformanceTrend')
            ->with($teacher, 12)
            ->willReturn($trend);

        $report = $this->performanceService->generatePerformanceReport($teacherId);

        $this->assertArrayHasKey('teacher', $report);
        $this->assertArrayHasKey('latestPerformance', $report);
        $this->assertArrayHasKey('trend', $report);
        $this->assertArrayHasKey('analysis', $report);
        $this->assertArrayHasKey('performanceCount', $report);
        $this->assertArrayHasKey('reportGeneratedAt', $report);

        $this->assertEquals($teacherId, $report['teacher']['id']);
        $this->assertEquals('张三', $report['teacher']['name']);
        $this->assertEquals('T001', $report['teacher']['code']);
        $this->assertEquals('专职', $report['teacher']['type']);
        $this->assertEquals(2, $report['performanceCount']);
        $this->assertInstanceOf(\DateTime::class, $report['reportGeneratedAt']);
    }

    public function test_generate_performance_report_without_data(): void
    {
        $teacherId = 'teacher_123';
        $teacher = new Teacher();
        $teacher->setId($teacherId);
        $teacher->setTeacherName('张三');
        $teacher->setTeacherCode('T001');

        $this->teacherService
            ->expects($this->once())
            ->method('getTeacherById')
            ->with($teacherId)
            ->willReturn($teacher);

        $this->performanceRepository
            ->expects($this->once())
            ->method('findByTeacher')
            ->with($teacher)
            ->willReturn([]);

        $report = $this->performanceService->generatePerformanceReport($teacherId);

        $this->assertArrayHasKey('teacher', $report);
        $this->assertArrayHasKey('message', $report);
        $this->assertArrayHasKey('reportGeneratedAt', $report);

        $this->assertEquals($teacherId, $report['teacher']['id']);
        $this->assertEquals('张三', $report['teacher']['name']);
        $this->assertEquals('T001', $report['teacher']['code']);
        $this->assertEquals('暂无绩效数据', $report['message']);
        $this->assertInstanceOf(\DateTime::class, $report['reportGeneratedAt']);
    }

    public function test_get_performance_ranking(): void
    {
        $limit = 10;
        $ranking = [
            ['teacherId' => 'teacher_1', 'performanceScore' => 4.8],
            ['teacherId' => 'teacher_2', 'performanceScore' => 4.7],
            ['teacherId' => 'teacher_3', 'performanceScore' => 4.5],
        ];

        $this->performanceRepository
            ->expects($this->once())
            ->method('getPerformanceRanking')
            ->with($limit)
            ->willReturn($ranking);

        $result = $this->performanceService->getPerformanceRanking($limit);

        $this->assertSame($ranking, $result);
    }

    public function test_get_performance_ranking_by_period(): void
    {
        $period = new \DateTime('2024-01-01');
        $limit = 5;
        $ranking = [
            ['teacherId' => 'teacher_1', 'performanceScore' => 4.8],
            ['teacherId' => 'teacher_2', 'performanceScore' => 4.7],
        ];

        $this->performanceRepository
            ->expects($this->once())
            ->method('getPerformanceRankingByPeriod')
            ->with($period, $limit)
            ->willReturn($ranking);

        $result = $this->performanceService->getPerformanceRankingByPeriod($period, $limit);

        $this->assertSame($ranking, $result);
    }

    public function test_get_performance_statistics(): void
    {
        $statistics = [
            'total' => 100,
            'excellent' => 20,
            'good' => 50,
            'average' => 25,
            'poor' => 5,
            'averageScore' => 4.2,
        ];

        $this->performanceRepository
            ->expects($this->once())
            ->method('getPerformanceStatistics')
            ->willReturn($statistics);

        $result = $this->performanceService->getPerformanceStatistics();

        $this->assertSame($statistics, $result);
    }

    /**
     * 创建模拟绩效对象
     */
    private function createMockPerformance(float $score): TeacherPerformance&MockObject
    {
        $performance = $this->createMock(TeacherPerformance::class);
        $performance->method('getPerformanceScore')->willReturn($score);
        $performance->method('getPerformanceLevel')->willReturn('优秀');
        $performance->method('getAverageEvaluation')->willReturn($score);
        $performance->method('getPerformanceMetrics')->willReturn([
            'teachingHours' => 120,
            'studentSatisfaction' => $score,
        ]);
        $performance->method('getAchievements')->willReturn(['优秀教师']);
        $performance->method('getPerformancePeriod')->willReturn(new \DateTime('2024-01-01'));
        return $performance;
    }
} 