<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Integration\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tourze\TrainTeacherBundle\Service\EvaluationService;
use Tourze\TrainTeacherBundle\Tests\Integration\IntegrationTestKernel;

class EvaluationServiceTest extends KernelTestCase
{
    private EvaluationService $service;

    protected static function getKernelClass(): string
    {
        return IntegrationTestKernel::class;
    }

    protected function setUp(): void
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $this->service = static::getContainer()->get(EvaluationService::class);
    }

    public function testServiceIsInstantiable(): void
    {
        $this->assertInstanceOf(EvaluationService::class, $this->service);
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
        $this->assertContainsOnly('object', $result);
    }
}