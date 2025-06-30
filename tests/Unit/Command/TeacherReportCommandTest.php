<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Unit\Command;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\TrainTeacherBundle\Command\TeacherReportCommand;
use Tourze\TrainTeacherBundle\Exception\InvalidReportTypeException;
use Tourze\TrainTeacherBundle\Repository\TeacherRepository;
use Tourze\TrainTeacherBundle\Service\EvaluationService;
use Tourze\TrainTeacherBundle\Service\PerformanceService;
use Tourze\TrainTeacherBundle\Service\TeacherService;

/**
 * 教师报告命令测试
 */
class TeacherReportCommandTest extends TestCase
{
    private TeacherService&MockObject $teacherService;
    private EvaluationService&MockObject $evaluationService;
    private PerformanceService&MockObject $performanceService;
    private TeacherRepository&MockObject $teacherRepository;
    private TeacherReportCommand $command;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->teacherService = $this->createMock(TeacherService::class);
        $this->evaluationService = $this->createMock(EvaluationService::class);
        $this->performanceService = $this->createMock(PerformanceService::class);
        $this->teacherRepository = $this->createMock(TeacherRepository::class);
        
        $this->command = new TeacherReportCommand(
            $this->teacherService,
            $this->evaluationService,
            $this->performanceService,
            $this->teacherRepository
        );

        $application = new Application();
        $application->add($this->command);
        $this->commandTester = new CommandTester($this->command);
    }

    public function testExecutePerformanceReport(): void
    {
        $this->performanceService
            ->expects($this->once())
            ->method('getPerformanceRankingByPeriod')
            ->willReturn([]);

        $this->commandTester->execute([
            'report-type' => 'performance',
            '--period' => '2024-01'
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithInvalidReportType(): void
    {
        $this->commandTester->execute([
            'report-type' => 'invalid-type'
        ]);

        $this->assertEquals(1, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('无效的报告类型', $this->commandTester->getDisplay());
    }

    public function testExecuteWithInvalidOutputFormat(): void
    {
        $this->commandTester->execute([
            'report-type' => 'performance',
            '--output-format' => 'invalid-format'
        ]);

        $this->assertEquals(1, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('无效的输出格式', $this->commandTester->getDisplay());
    }

    public function testExecuteEvaluationReport(): void
    {
        $this->teacherRepository
            ->expects($this->once())
            ->method('findBy')
            ->willReturn([]);

        $this->commandTester->execute([
            'report-type' => 'evaluation',
            '--period' => '2024-01'
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteStatisticsReport(): void
    {
        $this->teacherService
            ->expects($this->once())
            ->method('getTeacherStatistics')
            ->willReturn([]);

        $this->performanceService
            ->expects($this->once())
            ->method('getPerformanceStatistics')
            ->willReturn([]);

        $this->commandTester->execute([
            'report-type' => 'statistics',
            '--period' => '2024-01'
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteSummaryReport(): void
    {
        $this->teacherRepository
            ->expects($this->once())
            ->method('findBy')
            ->willReturn([]);

        $this->evaluationService
            ->expects($this->once())
            ->method('getTopRatedTeachers')
            ->willReturn([]);

        $this->commandTester->execute([
            'report-type' => 'summary',
            '--period' => '2024-01'
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }
} 