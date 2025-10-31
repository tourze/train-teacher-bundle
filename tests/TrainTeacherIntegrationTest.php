<?php

namespace Tourze\TrainTeacherBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainTeacherBundle\Entity\Teacher;
use Tourze\TrainTeacherBundle\Entity\TeacherEvaluation;
use Tourze\TrainTeacherBundle\Entity\TeacherPerformance;
use Tourze\TrainTeacherBundle\Exception\DuplicateEvaluationException;
use Tourze\TrainTeacherBundle\Exception\TeacherNotFoundException;
use Tourze\TrainTeacherBundle\Service\EvaluationService;
use Tourze\TrainTeacherBundle\Service\PerformanceService;
use Tourze\TrainTeacherBundle\Service\TeacherService;
use Tourze\TrainTeacherBundle\TrainTeacherBundle;

/**
 * TrainTeacherBundle集成测试
 *
 * @internal
 */
#[CoversClass(TrainTeacherBundle::class)]
#[RunTestsInSeparateProcesses]
final class TrainTeacherIntegrationTest extends AbstractIntegrationTestCase
{
    private ContainerInterface $container;

    private TeacherService $teacherService;

    private EvaluationService $evaluationService;

    private PerformanceService $performanceService;

    protected function onSetUp(): void
    {
        $this->container = self::getContainer();

        // 获取服务
        $teacherService = $this->container->get(TeacherService::class);
        self::assertInstanceOf(TeacherService::class, $teacherService);
        $this->teacherService = $teacherService;

        $evaluationService = $this->container->get(EvaluationService::class);
        self::assertInstanceOf(EvaluationService::class, $evaluationService);
        $this->evaluationService = $evaluationService;

        $performanceService = $this->container->get(PerformanceService::class);
        self::assertInstanceOf(PerformanceService::class, $performanceService);
        $this->performanceService = $performanceService;
    }

    public function testServicesAreAvailable(): void
    {
        $this->assertNotNull($this->teacherService);
        $this->assertNotNull($this->evaluationService);
        $this->assertNotNull($this->performanceService);
    }

    public function testTeacherCrudOperations(): void
    {
        // 创建教师
        $teacherData = [
            'teacherName' => '张三',
            'teacherType' => '专职',
            'gender' => '男',
            'birthDate' => new \DateTimeImmutable('1980-01-01'),
            'idCard' => '110101198001011234',
            'phone' => '13800138000',
            'email' => 'zhangsan@example.com',
            'education' => '本科',
            'major' => '安全工程',
            'graduateSchool' => '北京理工大学',
            'graduateDate' => new \DateTimeImmutable('2002-07-01'),
            'workExperience' => 20,
            'specialties' => ['安全管理', '风险评估'],
            'teacherStatus' => '在职',
            'joinDate' => new \DateTimeImmutable('2005-03-01'),
        ];

        $teacher = $this->teacherService->createTeacher($teacherData);

        $this->assertEquals('张三', $teacher->getTeacherName());
        $this->assertEquals('专职', $teacher->getTeacherType());
        $this->assertNotEmpty($teacher->getId());
        $this->assertNotEmpty($teacher->getTeacherCode());

        // 读取教师
        $retrievedTeacher = $this->teacherService->getTeacherById($teacher->getId());
        $this->assertEquals($teacher->getId(), $retrievedTeacher->getId());
        $this->assertEquals('张三', $retrievedTeacher->getTeacherName());

        // 更新教师
        $updateData = [
            'teacherName' => '李四',
            'email' => 'lisi@example.com',
        ];
        $updatedTeacher = $this->teacherService->updateTeacher($teacher->getId(), $updateData);
        $this->assertEquals('李四', $updatedTeacher->getTeacherName());
        $this->assertEquals('lisi@example.com', $updatedTeacher->getEmail());

        // 删除教师
        $this->teacherService->deleteTeacher($teacher->getId());

        $this->expectException(TeacherNotFoundException::class);
        $this->teacherService->getTeacherById($teacher->getId());
    }

    public function testTeacherEvaluationWorkflow(): void
    {
        // 创建教师
        $teacher = $this->createTestTeacher();

        // 提交评价
        $evaluationData = [
            'evaluatorType' => '学员',
            'evaluationType' => '课程评价',
            'evaluationItems' => ['教学态度', '专业水平', '沟通能力'],
            'evaluationScores' => [
                '教学态度' => 5,
                '专业水平' => 4.5,
                '沟通能力' => 4.8,
            ],
            'evaluationComments' => '教学认真负责，专业知识扎实',
            'suggestions' => ['建议增加实践案例'],
            'isAnonymous' => false,
        ];

        $evaluation = $this->evaluationService->submitEvaluation(
            $teacher->getId(),
            'student_001',
            $evaluationData
        );

        $this->assertInstanceOf(TeacherEvaluation::class, $evaluation);
        $this->assertEquals('学员', $evaluation->getEvaluatorType());
        $this->assertEquals('课程评价', $evaluation->getEvaluationType());
        $this->assertEquals(4.8, $evaluation->getOverallScore()); // 平均分

        // 获取评价统计
        $statistics = $this->evaluationService->getEvaluationStatistics($teacher->getId());
        $this->assertEquals(1, $statistics['total']);
        $this->assertEquals(1, $statistics['student']);
        $this->assertEquals(4.8, $statistics['averageScore']);

        // 生成评价报告
        $report = $this->evaluationService->generateEvaluationReport($teacher->getId());
        $this->assertArrayHasKey('teacher', $report);
        $this->assertArrayHasKey('statistics', $report);
        $this->assertIsArray($report['teacher']);
        $this->assertArrayHasKey('id', $report['teacher']);
        $this->assertEquals($teacher->getId(), $report['teacher']['id']);
    }

    public function testTeacherPerformanceCalculation(): void
    {
        // 创建教师
        $teacher = $this->createTestTeacher();

        // 添加一些评价数据
        $this->createTestEvaluations($teacher);

        // 计算绩效
        $period = new \DateTimeImmutable('2024-01-01');
        $performance = $this->performanceService->calculatePerformance($teacher->getId(), $period);

        $this->assertInstanceOf(TeacherPerformance::class, $performance);
        $this->assertEquals($teacher->getId(), $performance->getTeacher()->getId());
        $this->assertEquals($period->format('Y-m-d'), $performance->getPerformancePeriod()->format('Y-m-d'));
        $this->assertGreaterThan(0, $performance->getPerformanceScore());
        $this->assertNotEmpty($performance->getPerformanceLevel());

        // 获取绩效历史
        $history = $this->performanceService->getPerformanceHistory($teacher->getId());
        $this->assertCount(1, $history);
        $this->assertEquals($performance->getId(), $history[0]->getId());

        // 生成绩效报告
        $report = $this->performanceService->generatePerformanceReport($teacher->getId());
        $this->assertArrayHasKey('teacher', $report);
        $this->assertArrayHasKey('latestPerformance', $report);
        $this->assertIsArray($report['teacher']);
        $this->assertArrayHasKey('id', $report['teacher']);
        $this->assertEquals($teacher->getId(), $report['teacher']['id']);
    }

    public function testMultipleTeachersRanking(): void
    {
        // 创建多个教师
        $teachers = [];
        $integrationTimestamp = time();
        for ($i = 1; $i <= 3; ++$i) {
            $uniqueId = $integrationTimestamp + $i + 1000; // +1000 to avoid collision with other tests
            $teacherData = [
                'teacherName' => "集成排名教师{$uniqueId}",
                'teacherType' => '专职',
                'gender' => '男',
                'birthDate' => new \DateTimeImmutable('1980-01-01'),
                'idCard' => "110101198001011{$uniqueId}",
                'phone' => "137{$uniqueId}",
                'education' => '本科',
                'major' => '安全工程',
                'graduateSchool' => '北京理工大学',
                'graduateDate' => new \DateTimeImmutable('2002-07-01'),
                'workExperience' => 20,
                'teacherStatus' => '在职',
                'joinDate' => new \DateTimeImmutable('2005-03-01'),
            ];
            $teachers[] = $this->teacherService->createTeacher($teacherData);
        }

        // 为每个教师添加不同的评价
        foreach ($teachers as $index => $teacher) {
            $score = 4.0 + ($index * 0.3); // 不同的评分
            $evaluationData = [
                'evaluatorType' => '学员',
                'evaluationType' => '课程评价',
                'evaluationItems' => ['教学态度'],
                'evaluationScores' => ['教学态度' => $score],
                'evaluationComments' => "评价教师{$index}",
            ];

            $this->evaluationService->submitEvaluation(
                $teacher->getId(),
                "student_{$index}",
                $evaluationData
            );
        }

        // 计算所有教师的绩效
        $period = new \DateTimeImmutable('2024-01-01');
        foreach ($teachers as $teacher) {
            $this->performanceService->calculatePerformance($teacher->getId(), $period);
        }

        // 获取绩效排名
        $ranking = $this->performanceService->getPerformanceRanking(10);
        $this->assertGreaterThanOrEqual(3, count($ranking));

        // 验证排名是按分数降序排列的
        for ($i = 1; $i < count($ranking); ++$i) {
            $this->assertIsArray($ranking[$i]);
            $this->assertIsArray($ranking[$i - 1]);
            $this->assertArrayHasKey('performanceScore', $ranking[$i]);
            $this->assertArrayHasKey('performanceScore', $ranking[$i - 1]);
            $this->assertGreaterThanOrEqual(
                $ranking[$i]['performanceScore'],
                $ranking[$i - 1]['performanceScore']
            );
        }
    }

    public function testTeacherSearchFunctionality(): void
    {
        // 创建测试教师
        $searchTimestamp = time() + 2000; // +2000 to avoid collision
        $teachers = [
            ['teacherName' => '张三', 'idCard' => "110101198001011{$searchTimestamp}", 'phone' => "136{$searchTimestamp}1"],
            ['teacherName' => '李四', 'idCard' => "110101198001012{$searchTimestamp}", 'phone' => "136{$searchTimestamp}2"],
            ['teacherName' => '王五', 'idCard' => "110101198001013{$searchTimestamp}", 'phone' => "136{$searchTimestamp}3"],
        ];

        foreach ($teachers as $teacherData) {
            $fullData = array_merge([
                'teacherType' => '专职',
                'gender' => '男',
                'birthDate' => new \DateTimeImmutable('1980-01-01'),
                'education' => '本科',
                'major' => '安全工程',
                'graduateSchool' => '北京理工大学',
                'graduateDate' => new \DateTimeImmutable('2002-07-01'),
                'workExperience' => 20,
                'teacherStatus' => '在职',
                'joinDate' => new \DateTimeImmutable('2005-03-01'),
            ], $teacherData);

            $this->teacherService->createTeacher($fullData);
        }

        // 搜索教师
        $searchResults = $this->teacherService->searchTeachers('张三');
        $this->assertCount(1, $searchResults);
        $this->assertEquals('张三', $searchResults[0]->getTeacherName());

        // 按类型获取教师（只验证至少包含我们创建的教师）
        $fullTimeTeachers = $this->teacherService->getTeachersByType('专职');
        $this->assertGreaterThanOrEqual(3, count($fullTimeTeachers));

        // 按状态获取教师（只验证至少包含我们创建的教师）
        $activeTeachers = $this->teacherService->getTeachersByStatus('在职');
        $this->assertGreaterThanOrEqual(3, count($activeTeachers));

        // 获取统计信息（只验证至少包含我们创建的教师）
        $statistics = $this->teacherService->getTeacherStatistics();
        $this->assertGreaterThanOrEqual(3, $statistics['total']);
        $this->assertGreaterThanOrEqual(3, $statistics['active']);
    }

    public function testEvaluationDuplicatePrevention(): void
    {
        $teacher = $this->createTestTeacher();

        $evaluationData = [
            'evaluatorType' => '学员',
            'evaluationType' => '课程评价',
            'evaluationItems' => ['教学态度'],
            'evaluationScores' => ['教学态度' => 5],
        ];

        // 第一次评价应该成功
        $evaluation1 = $this->evaluationService->submitEvaluation(
            $teacher->getId(),
            'student_001',
            $evaluationData
        );
        $this->assertInstanceOf(TeacherEvaluation::class, $evaluation1);

        // 同一评价者对同一教师的同一类型评价应该抛出异常
        $this->expectException(DuplicateEvaluationException::class);
        $this->evaluationService->submitEvaluation(
            $teacher->getId(),
            'student_001',
            $evaluationData
        );
    }

    /**
     * 创建测试教师
     */
    private function createTestTeacher(): Teacher
    {
        $teacherData = [
            'teacherName' => '测试教师',
            'teacherType' => '专职',
            'gender' => '男',
            'birthDate' => new \DateTimeImmutable('1980-01-01'),
            'idCard' => '110101198001011234',
            'phone' => '13800138000',
            'email' => 'test@example.com',
            'education' => '本科',
            'major' => '安全工程',
            'graduateSchool' => '北京理工大学',
            'graduateDate' => new \DateTimeImmutable('2002-07-01'),
            'workExperience' => 20,
            'specialties' => ['安全管理', '风险评估'],
            'teacherStatus' => '在职',
            'joinDate' => new \DateTimeImmutable('2005-03-01'),
        ];

        return $this->teacherService->createTeacher($teacherData);
    }

    /**
     * 创建测试评价数据
     */
    private function createTestEvaluations(Teacher $teacher): void
    {
        $evaluationTypes = [
            ['type' => '学员', 'evaluator' => 'student_001', 'score' => 4.5],
            ['type' => '同行', 'evaluator' => 'peer_001', 'score' => 4.3],
            ['type' => '管理层', 'evaluator' => 'manager_001', 'score' => 4.7],
        ];

        foreach ($evaluationTypes as $evalType) {
            $evaluationData = [
                'evaluatorType' => $evalType['type'],
                'evaluationType' => '综合评价',
                'evaluationItems' => ['综合表现'],
                'evaluationScores' => ['综合表现' => $evalType['score']],
                'evaluationComments' => "来自{$evalType['type']}的评价",
            ];

            $this->evaluationService->submitEvaluation(
                $teacher->getId(),
                $evalType['evaluator'],
                $evaluationData
            );
        }
    }
}
