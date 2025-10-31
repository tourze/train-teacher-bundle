<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainTeacherBundle\Service\EvaluationService;

/**
 * @internal
 */
#[CoversClass(EvaluationService::class)]
#[RunTestsInSeparateProcesses]
final class EvaluationServiceTest extends AbstractIntegrationTestCase
{
    private EvaluationService $service;

    protected function onSetUp(): void
    {
        $service = self::getContainer()->get(EvaluationService::class);
        self::assertInstanceOf(EvaluationService::class, $service);
        $this->service = $service;
    }

    public function testGetEvaluationStatisticsThrowsExceptionForNonExistentTeacher(): void
    {
        $this->expectException(\Exception::class);
        $result = $this->service->getEvaluationStatistics('teacher-id');
    }

    public function testGetTopRatedTeachersReturnsArray(): void
    {
        $result = $this->service->getTopRatedTeachers(5);
        $this->assertLessThanOrEqual(5, count($result));
    }

    public function testGetTeacherEvaluationsThrowsExceptionForNonExistentTeacher(): void
    {
        $this->expectException(\Exception::class);
        $result = $this->service->getTeacherEvaluations('teacher-id');
    }

    public function testCalculateAverageEvaluationThrowsExceptionForNonExistentTeacher(): void
    {
        $this->expectException(\Exception::class);
        $result = $this->service->calculateAverageEvaluation('teacher-id');
    }

    public function testGetEvaluationsByTypeReturnsArray(): void
    {
        $result = $this->service->getEvaluationsByType('student');
        // 验证返回值是数组
        $this->assertGreaterThanOrEqual(0, count($result));
    }

    public function testGenerateEvaluationReport(): void
    {
        $this->expectException(\Exception::class);
        $this->service->generateEvaluationReport('non-existent-id');
    }

    public function testSubmitEvaluation(): void
    {
        $this->expectException(\Exception::class);
        $evaluationData = [
            'evaluatorType' => 'student',
            'evaluationType' => 'course',
            'evaluationScores' => ['teaching' => 4.5, 'communication' => 4.2],
            'evaluationComments' => 'Good teacher',
        ];
        $this->service->submitEvaluation('non-existent-teacher-id', 'evaluator-123', $evaluationData);
    }
}
