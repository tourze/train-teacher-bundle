<?php

namespace Tourze\TrainTeacherBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainTeacherBundle\Entity\Teacher;
use Tourze\TrainTeacherBundle\Exception\DuplicateEvaluationException;
use Tourze\TrainTeacherBundle\Exception\TeacherNotFoundException;
use Tourze\TrainTeacherBundle\Service\EvaluationService;
use Tourze\TrainTeacherBundle\Service\PerformanceService;
use Tourze\TrainTeacherBundle\Service\TeacherService;

/**
 * 教师服务集成测试
 * 使用 AbstractIntegrationTestCase 进行完整的集成测试
 *
 * @internal
 */
#[CoversClass(TeacherService::class)]
#[RunTestsInSeparateProcesses]
final class SimpleIntegrationTest extends AbstractIntegrationTestCase
{
    private TeacherService $teacherService;

    private EvaluationService $evaluationService;

    private PerformanceService $performanceService;

    protected function onSetUp(): void
    {
        // 使用集成测试框架获取服务
        $teacherService = self::getContainer()->get(TeacherService::class);
        self::assertInstanceOf(TeacherService::class, $teacherService);
        $this->teacherService = $teacherService;

        $evaluationService = self::getContainer()->get(EvaluationService::class);
        self::assertInstanceOf(EvaluationService::class, $evaluationService);
        $this->evaluationService = $evaluationService;

        $performanceService = self::getContainer()->get(PerformanceService::class);
        self::assertInstanceOf(PerformanceService::class, $performanceService);
        $this->performanceService = $performanceService;
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
        $timestamp = time();
        for ($i = 1; $i <= 3; ++$i) {
            $uniqueId = $timestamp + $i;
            $teacherData = [
                'teacherName' => "排名教师{$uniqueId}",
                'teacherType' => '专职',
                'gender' => '男',
                'birthDate' => new \DateTimeImmutable('1980-01-01'),
                'idCard' => "110101198001011{$uniqueId}",
                'phone' => "138{$uniqueId}",
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

    public function testChangeTeacherStatus(): void
    {
        $teacher = $this->createTestTeacher();
        $originalStatus = $teacher->getTeacherStatus();

        // 改变教师状态
        $updatedTeacher = $this->teacherService->changeTeacherStatus(
            $teacher->getId(),
            '休假'
        );

        $this->assertEquals('休假', $updatedTeacher->getTeacherStatus());
        $this->assertNotEquals($originalStatus, $updatedTeacher->getTeacherStatus());
    }

    public function testCreateTeacher(): void
    {
        $teacherData = [
            'teacherName' => '王五',
            'teacherType' => '专职',
            'gender' => '男',
            'birthDate' => new \DateTimeImmutable('1980-01-01'),
            'idCard' => '110101198001018888',
            'phone' => '13800138888',
            'education' => '本科',
            'major' => '安全工程',
            'graduateSchool' => '北京理工大学',
            'graduateDate' => new \DateTimeImmutable('2002-07-01'),
            'workExperience' => 20,
            'teacherStatus' => '在职',
            'joinDate' => new \DateTimeImmutable('2005-03-01'),
        ];

        $teacher = $this->teacherService->createTeacher($teacherData);

        $this->assertEquals('王五', $teacher->getTeacherName());
        $this->assertEquals('专职', $teacher->getTeacherType());
        $this->assertNotEmpty($teacher->getId());
        $this->assertNotEmpty($teacher->getTeacherCode());
    }

    public function testUpdateTeacher(): void
    {
        $teacher = $this->createTestTeacher();
        $originalName = $teacher->getTeacherName();

        $updateData = [
            'teacherName' => '更新后的姓名',
            'email' => 'updated@example.com',
        ];

        $updatedTeacher = $this->teacherService->updateTeacher($teacher->getId(), $updateData);

        $this->assertEquals('更新后的姓名', $updatedTeacher->getTeacherName());
        $this->assertEquals('updated@example.com', $updatedTeacher->getEmail());
        $this->assertNotEquals($originalName, $updatedTeacher->getTeacherName());
    }

    public function testDeleteTeacher(): void
    {
        $teacher = $this->createTestTeacher();
        $teacherId = $teacher->getId();

        // 确保教师存在
        $existingTeacher = $this->teacherService->getTeacherById($teacherId);
        $this->assertNotNull($existingTeacher);

        // 删除教师
        $this->teacherService->deleteTeacher($teacherId);

        // 验证已删除
        $this->expectException(TeacherNotFoundException::class);
        $this->teacherService->getTeacherById($teacherId);
    }

    public function testSearchTeachers(): void
    {
        // 创建几个测试教师
        $searchTimestamp = time();
        $uniqueName1 = "搜索张{$searchTimestamp}";
        $uniqueName2 = "搜索李{$searchTimestamp}";

        $teacher1 = $this->createTestTeacher();
        $teacher1->setTeacherName($uniqueName1);
        self::getEntityManager()->flush();

        $teacherData2 = [
            'teacherName' => $uniqueName2,
            'teacherType' => '兼职',
            'gender' => '女',
            'birthDate' => new \DateTimeImmutable('1985-05-15'),
            'idCard' => "110101198505151{$searchTimestamp}",
            'phone' => "139{$searchTimestamp}",
            'education' => '硕士',
            'major' => '心理学',
            'graduateSchool' => '北京师范大学',
            'graduateDate' => new \DateTimeImmutable('2008-07-01'),
            'workExperience' => 15,
            'teacherStatus' => '在职',
            'joinDate' => new \DateTimeImmutable('2010-03-01'),
        ];
        $this->teacherService->createTeacher($teacherData2);

        // 搜索测试
        $results = $this->teacherService->searchTeachers($uniqueName1);
        $this->assertCount(1, $results);
        $this->assertEquals($uniqueName1, $results[0]->getTeacherName());

        // 模糊搜索
        $results = $this->teacherService->searchTeachers("搜索李{$searchTimestamp}");
        $this->assertCount(1, $results);
        $this->assertEquals($uniqueName2, $results[0]->getTeacherName());

        // 空搜索结果
        $results = $this->teacherService->searchTeachers('不存在的教师');
        $this->assertCount(0, $results);
    }
}
