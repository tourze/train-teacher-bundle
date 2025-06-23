<?php

namespace Tourze\TrainTeacherBundle\Tests\Unit\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\TrainTeacherBundle\Entity\Teacher;
use Tourze\TrainTeacherBundle\Entity\TeacherEvaluation;
use Tourze\TrainTeacherBundle\Exception\DuplicateEvaluationException;
use Tourze\TrainTeacherBundle\Repository\TeacherEvaluationRepository;
use Tourze\TrainTeacherBundle\Service\EvaluationService;
use Tourze\TrainTeacherBundle\Service\TeacherService;

/**
 * EvaluationService单元测试
 */
class EvaluationServiceTest extends TestCase
{
    private EvaluationService $evaluationService;
    private EntityManagerInterface&MockObject $entityManager;
    private TeacherEvaluationRepository&MockObject $evaluationRepository;
    private TeacherService&MockObject $teacherService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->evaluationRepository = $this->createMock(TeacherEvaluationRepository::class);
        $this->teacherService = $this->createMock(TeacherService::class);
        
        $this->evaluationService = new EvaluationService(
            $this->entityManager,
            $this->evaluationRepository,
            $this->teacherService
        );
    }

    public function test_submit_evaluation_success(): void
    {
        $teacherId = 'teacher_123';
        $evaluatorId = 'student_001';
        $evaluationData = [
            'evaluatorType' => '学员',
            'evaluationType' => '课程评价',
            'evaluationItems' => ['教学态度', '专业水平'],
            'evaluationScores' => [
                '教学态度' => 5,
                '专业水平' => 4.5,
            ],
            'evaluationComments' => '教学认真负责',
            'suggestions' => ['建议增加实践案例'],
            'isAnonymous' => false,
        ];

        $teacher = new Teacher();
        $teacher->setId($teacherId);

        $this->teacherService
            ->expects($this->once())
            ->method('getTeacherById')
            ->with($teacherId)
            ->willReturn($teacher);

        $this->evaluationRepository
            ->expects($this->once())
            ->method('hasEvaluated')
            ->with($teacher, $evaluatorId, '课程评价')
            ->willReturn(false);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(TeacherEvaluation::class));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $evaluation = $this->evaluationService->submitEvaluation($teacherId, $evaluatorId, $evaluationData);

        $this->assertInstanceOf(TeacherEvaluation::class, $evaluation);
        $this->assertEquals($teacher, $evaluation->getTeacher());
        $this->assertEquals($evaluatorId, $evaluation->getEvaluatorId());
        $this->assertEquals('学员', $evaluation->getEvaluatorType());
        $this->assertEquals('课程评价', $evaluation->getEvaluationType());
        $this->assertEquals(4.8, $evaluation->getOverallScore()); // round((5 + 4.5) / 2, 1) = 4.8
    }

    public function test_submit_evaluation_throws_exception_for_duplicate(): void
    {
        $teacherId = 'teacher_123';
        $evaluatorId = 'student_001';
        $evaluationData = [
            'evaluatorType' => '学员',
            'evaluationType' => '课程评价',
        ];

        $teacher = new Teacher();
        $teacher->setId($teacherId);

        $this->teacherService
            ->expects($this->once())
            ->method('getTeacherById')
            ->with($teacherId)
            ->willReturn($teacher);

        $this->evaluationRepository
            ->expects($this->once())
            ->method('hasEvaluated')
            ->with($teacher, $evaluatorId, '课程评价')
            ->willReturn(true);

        $this->expectException(DuplicateEvaluationException::class);
        $this->expectExceptionMessage('您已经对该教师进行过此类型的评价');

        $this->evaluationService->submitEvaluation($teacherId, $evaluatorId, $evaluationData);
    }

    public function test_calculate_average_evaluation(): void
    {
        $teacherId = 'teacher_123';
        $teacher = new Teacher();
        $teacher->setId($teacherId);

        $this->teacherService
            ->expects($this->once())
            ->method('getTeacherById')
            ->with($teacherId)
            ->willReturn($teacher);

        $this->evaluationRepository
            ->expects($this->once())
            ->method('getAverageScore')
            ->with($teacher)
            ->willReturn(4.5);

        $average = $this->evaluationService->calculateAverageEvaluation($teacherId);

        $this->assertEquals(4.5, $average);
    }

    public function test_get_evaluation_statistics(): void
    {
        $teacherId = 'teacher_123';
        $teacher = new Teacher();
        $teacher->setId($teacherId);

        $baseStatistics = [
            'total' => 10,
            'student' => 5,
            'peer' => 3,
            'manager' => 2,
            'averageScore' => 4.3,
        ];

        $this->teacherService
            ->expects($this->once())
            ->method('getTeacherById')
            ->with($teacherId)
            ->willReturn($teacher);

        $this->evaluationRepository
            ->expects($this->once())
            ->method('getEvaluationStatistics')
            ->with($teacher)
            ->willReturn($baseStatistics);

        $this->evaluationRepository
            ->expects($this->exactly(4))
            ->method('getAverageScoreByEvaluatorType')
            ->willReturnMap([
                [$teacher, '学员', 4.5],
                [$teacher, '同行', 4.2],
                [$teacher, '管理层', 4.8],
                [$teacher, '自我', 4.0],
            ]);

        $statistics = $this->evaluationService->getEvaluationStatistics($teacherId);

        $this->assertEquals(10, $statistics['total']);
        $this->assertEquals(5, $statistics['student']);
        $this->assertEquals(4.5, $statistics['studentAverage']);
        $this->assertEquals(4.2, $statistics['peerAverage']);
        $this->assertEquals(4.8, $statistics['managerAverage']);
        $this->assertEquals(4.0, $statistics['selfAverage']);
    }

    public function test_generate_evaluation_report(): void
    {
        $teacherId = 'teacher_123';
        $teacher = new Teacher();
        $teacher->setId($teacherId);
        $teacher->setTeacherName('张三');
        $teacher->setTeacherCode('T001');
        $teacher->setTeacherType('专职');

        $evaluations = [
            $this->createMockEvaluation(4.5),
            $this->createMockEvaluation(4.8),
        ];

        $statistics = [
            'total' => 2,
            'averageScore' => 4.65,
        ];

        $this->teacherService
            ->expects($this->exactly(2))
            ->method('getTeacherById')
            ->with($teacherId)
            ->willReturn($teacher);

        $this->evaluationRepository
            ->expects($this->once())
            ->method('findByTeacher')
            ->with($teacher)
            ->willReturn($evaluations);

        $this->evaluationRepository
            ->expects($this->once())
            ->method('getEvaluationStatistics')
            ->with($teacher)
            ->willReturn($statistics);

        $this->evaluationRepository
            ->expects($this->exactly(4))
            ->method('getAverageScoreByEvaluatorType')
            ->willReturn(4.5);

        $report = $this->evaluationService->generateEvaluationReport($teacherId);

        $this->assertArrayHasKey('teacher', $report);
        $this->assertArrayHasKey('statistics', $report);
        $this->assertArrayHasKey('trend', $report);
        $this->assertArrayHasKey('strengths', $report);
        $this->assertArrayHasKey('weaknesses', $report);
        $this->assertArrayHasKey('suggestions', $report);
        $this->assertArrayHasKey('evaluationCount', $report);
        $this->assertArrayHasKey('reportGeneratedAt', $report);

        $this->assertEquals($teacherId, $report['teacher']['id']);
        $this->assertEquals('张三', $report['teacher']['name']);
        $this->assertEquals('T001', $report['teacher']['code']);
        $this->assertEquals('专职', $report['teacher']['type']);
        $this->assertEquals(2, $report['evaluationCount']);
        $this->assertInstanceOf(\DateTime::class, $report['reportGeneratedAt']);
    }

    public function test_get_top_rated_teachers(): void
    {
        $limit = 5;
        $topTeachers = [
            ['teacherId' => 'teacher_1', 'averageScore' => 4.8],
            ['teacherId' => 'teacher_2', 'averageScore' => 4.7],
        ];

        $this->evaluationRepository
            ->expects($this->once())
            ->method('getTopRatedTeachers')
            ->with($limit)
            ->willReturn($topTeachers);

        $result = $this->evaluationService->getTopRatedTeachers($limit);

        $this->assertSame($topTeachers, $result);
    }

    public function test_get_teacher_evaluations(): void
    {
        $teacherId = 'teacher_123';
        $teacher = new Teacher();
        $teacher->setId($teacherId);

        $evaluations = [
            $this->createMockEvaluation(4.5),
            $this->createMockEvaluation(4.8),
        ];

        $this->teacherService
            ->expects($this->once())
            ->method('getTeacherById')
            ->with($teacherId)
            ->willReturn($teacher);

        $this->evaluationRepository
            ->expects($this->once())
            ->method('findByTeacher')
            ->with($teacher)
            ->willReturn($evaluations);

        $result = $this->evaluationService->getTeacherEvaluations($teacherId);

        $this->assertSame($evaluations, $result);
    }

    public function test_get_evaluations_by_type(): void
    {
        $evaluatorType = '学员';
        $evaluations = [
            $this->createMockEvaluation(4.5),
            $this->createMockEvaluation(4.8),
        ];

        $this->evaluationRepository
            ->expects($this->once())
            ->method('findByEvaluatorType')
            ->with($evaluatorType)
            ->willReturn($evaluations);

        $result = $this->evaluationService->getEvaluationsByType($evaluatorType);

        $this->assertSame($evaluations, $result);
    }

    public function test_get_evaluations_by_date_range(): void
    {
        $startDate = new \DateTime('2024-01-01');
        $endDate = new \DateTime('2024-01-31');
        $evaluations = [
            $this->createMockEvaluation(4.5),
            $this->createMockEvaluation(4.8),
        ];

        $this->evaluationRepository
            ->expects($this->once())
            ->method('findByDateRange')
            ->with($startDate, $endDate)
            ->willReturn($evaluations);

        $result = $this->evaluationService->getEvaluationsByDateRange($startDate, $endDate);

        $this->assertSame($evaluations, $result);
    }

    public function test_submit_evaluation_with_default_values(): void
    {
        $teacherId = 'teacher_123';
        $evaluatorId = 'student_001';
        $evaluationData = [
            'evaluatorType' => '学员',
            'evaluationScores' => ['教学态度' => 5],
        ];

        $teacher = new Teacher();
        $teacher->setId($teacherId);

        $this->teacherService
            ->expects($this->once())
            ->method('getTeacherById')
            ->willReturn($teacher);

        // hasEvaluated只在有evaluationType时才调用
        $this->evaluationRepository
            ->expects($this->never())
            ->method('hasEvaluated');

        $this->entityManager
            ->expects($this->once())
            ->method('persist');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $evaluation = $this->evaluationService->submitEvaluation($teacherId, $evaluatorId, $evaluationData);

        $this->assertEquals('已提交', $evaluation->getEvaluationStatus());
        $this->assertInstanceOf(\DateTimeImmutable::class, $evaluation->getEvaluationDate());
    }

    public function test_submit_evaluation_with_empty_scores(): void
    {
        $teacherId = 'teacher_123';
        $evaluatorId = 'student_001';
        $evaluationData = [
            'evaluatorType' => '学员',
            'evaluationScores' => [],
        ];

        $teacher = new Teacher();
        $teacher->setId($teacherId);

        $this->teacherService
            ->expects($this->once())
            ->method('getTeacherById')
            ->willReturn($teacher);

        // hasEvaluated只在有evaluationType时才调用
        $this->evaluationRepository
            ->expects($this->never())
            ->method('hasEvaluated');

        $this->entityManager
            ->expects($this->once())
            ->method('persist');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $evaluation = $this->evaluationService->submitEvaluation($teacherId, $evaluatorId, $evaluationData);

        $this->assertEquals(0.0, $evaluation->getOverallScore());
    }

    /**
     * 创建模拟评价对象
     */
    private function createMockEvaluation(float $score): TeacherEvaluation&MockObject
    {
        $evaluation = $this->createMock(TeacherEvaluation::class);
        $evaluation->method('getOverallScore')->willReturn($score);
        $evaluation->method('getEvaluationScores')->willReturn(['教学态度' => $score]);
        $evaluation->method('getSuggestions')->willReturn(['建议1', '建议2']);
        $evaluation->method('getEvaluationDate')->willReturn(new \DateTimeImmutable());
        return $evaluation;
    }
} 