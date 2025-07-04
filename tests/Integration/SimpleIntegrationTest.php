<?php

namespace Tourze\TrainTeacherBundle\Tests\Integration;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Tourze\TrainTeacherBundle\Entity\Teacher;
use Tourze\TrainTeacherBundle\Entity\TeacherEvaluation;
use Tourze\TrainTeacherBundle\Entity\TeacherPerformance;
use Tourze\TrainTeacherBundle\Repository\TeacherEvaluationRepository;
use Tourze\TrainTeacherBundle\Repository\TeacherPerformanceRepository;
use Tourze\TrainTeacherBundle\Repository\TeacherRepository;
use Tourze\TrainTeacherBundle\Service\EvaluationService;
use Tourze\TrainTeacherBundle\Service\PerformanceService;
use Tourze\TrainTeacherBundle\Service\TeacherService;

/**
 * 简化的集成测试
 * 直接测试服务功能，不依赖Symfony Bundle
 */
class SimpleIntegrationTest extends TestCase
{
    private EntityManager $entityManager;
    private TeacherService $teacherService;
    private EvaluationService $evaluationService;
    private PerformanceService $performanceService;

    protected function setUp(): void
    {
        // 创建内存数据库
        $config = ORMSetup::createAttributeMetadataConfiguration(
            [__DIR__ . '/../../src/Entity'],
            true
        );

        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);

        $this->entityManager = new EntityManager($connection, $config);

        // 创建数据库表结构
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->createSchema($metadata);

        // 创建ManagerRegistry Mock
        /** @var ManagerRegistry&\PHPUnit\Framework\MockObject\MockObject $registry */
        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($this->entityManager);
        $registry->method('getManager')->willReturn($this->entityManager);

        // 创建Repository
        $teacherRepo = new TeacherRepository($registry);
        $evaluationRepo = new TeacherEvaluationRepository($registry);
        $performanceRepo = new TeacherPerformanceRepository($registry);

        // 创建服务
        $this->teacherService = new TeacherService($this->entityManager, $teacherRepo);
        $this->evaluationService = new EvaluationService($this->entityManager, $evaluationRepo, $this->teacherService);
        $this->performanceService = new PerformanceService($this->entityManager, $performanceRepo, $this->teacherService, $this->evaluationService);
    }

    protected function tearDown(): void
    {
        $this->entityManager->close();
    }

    public function test_teacher_crud_operations(): void
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
        
        $this->assertInstanceOf(Teacher::class, $teacher);
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
        
        $this->expectException(\Tourze\TrainTeacherBundle\Exception\TeacherNotFoundException::class);
        $this->teacherService->getTeacherById($teacher->getId());
    }

    public function test_teacher_evaluation_workflow(): void
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
        $this->assertEquals($teacher->getId(), $report['teacher']['id']);
    }

    public function test_teacher_performance_calculation(): void
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
        $this->assertEquals($teacher->getId(), $report['teacher']['id']);
    }

    public function test_multiple_teachers_ranking(): void
    {
        // 创建多个教师
        $teachers = [];
        for ($i = 1; $i <= 3; $i++) {
            $teacherData = [
                'teacherName' => "教师{$i}",
                'teacherType' => '专职',
                'gender' => '男',
                'birthDate' => new \DateTimeImmutable('1980-01-01'),
                'idCard' => "11010119800101123{$i}",
                'phone' => "1380013800{$i}",
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
        for ($i = 1; $i < count($ranking); $i++) {
            $this->assertGreaterThanOrEqual(
                $ranking[$i]['performanceScore'],
                $ranking[$i-1]['performanceScore']
            );
        }
    }

    public function test_evaluation_duplicate_prevention(): void
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
        $this->expectException(\Tourze\TrainTeacherBundle\Exception\DuplicateEvaluationException::class);
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