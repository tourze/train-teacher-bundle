<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\TrainTeacherBundle\Entity\Teacher;
use Tourze\TrainTeacherBundle\Entity\TeacherEvaluation;
use Tourze\TrainTeacherBundle\Repository\TeacherEvaluationRepository;

/**
 * @internal
 */
#[CoversClass(TeacherEvaluationRepository::class)]
#[RunTestsInSeparateProcesses]
final class TeacherEvaluationRepositoryTest extends AbstractRepositoryTestCase
{
    private TeacherEvaluationRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(TeacherEvaluationRepository::class);
    }

    protected function createNewEntity(): TeacherEvaluation
    {
        static $teacherCounter = 0;
        $this->assertIsInt($teacherCounter);
        ++$teacherCounter;

        $teacher = new Teacher();
        $teacher->setId('test-teacher-' . time() . '-' . $teacherCounter);
        $teacher->setTeacherCode('T' . $teacherCounter . uniqid());
        $teacher->setTeacherName('测试教师' . $teacherCounter);
        $teacher->setTeacherType('专职');
        $teacher->setTeacherStatus('在职');
        $teacher->setGender('男');
        $teacher->setBirthDate(new \DateTimeImmutable('1980-01-01'));
        $teacher->setIdCard('11010119800101' . sprintf('%04d', $teacherCounter));
        $teacher->setPhone('138' . sprintf('%08d', $teacherCounter));
        $teacher->setEducation('本科');
        $teacher->setMajor('安全工程');
        $teacher->setGraduateSchool('北京理工大学');
        $teacher->setGraduateDate(new \DateTimeImmutable('2002-07-01'));
        $teacher->setWorkExperience(20);
        $teacher->setJoinDate(new \DateTimeImmutable('2005-03-01'));
        self::getEntityManager()->persist($teacher);
        self::getEntityManager()->flush();

        static $evalCounter = 0;
        $this->assertIsInt($evalCounter);
        ++$evalCounter;

        $entity = new TeacherEvaluation();
        // 使用时间戳 + 计数器确保排序的可预测性
        $entity->setId('test-eval-' . time() . '-' . str_pad((string) $evalCounter, 3, '0', STR_PAD_LEFT));
        $entity->setTeacher($teacher);
        $entity->setEvaluatorType('peer');
        $entity->setEvaluatorId('test-evaluator-' . $evalCounter);
        $entity->setEvaluationType('课程评价');
        $entity->setEvaluationDate(new \DateTimeImmutable());
        $entity->setOverallScore(8.5);
        $entity->setEvaluationStatus('completed');
        $entity->setIsAnonymous(false);

        return $entity;
    }

    /**
     * @return TeacherEvaluationRepository
     */
    protected function getRepository(): TeacherEvaluationRepository
    {
        return $this->repository;
    }

    public function testFindByDateRange(): void
    {
        $startDate = new \DateTime('-30 days');
        $endDate = new \DateTime();
        $result = $this->repository->findByDateRange($startDate, $endDate);
        $this->assertIsArray($result);
    }

    public function testFindByEvaluatorType(): void
    {
        $result = $this->repository->findByEvaluatorType('student');
        $this->assertIsArray($result);
    }

    public function testFindByTeacher(): void
    {
        $teacher = $this->getMockBuilder(Teacher::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $result = $this->repository->findByTeacher($teacher);
        $this->assertIsArray($result);
    }

    public function testFindRecentEvaluations(): void
    {
        $teacher = $this->getMockBuilder(Teacher::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $result = $this->repository->findRecentEvaluations($teacher, 'course', 30);
        $this->assertIsArray($result);
    }

    public function testGetAverageScore(): void
    {
        // 创建教师和多个评价
        $evaluation1 = $this->createNewEntity();
        $evaluation1->setOverallScore(8.0);
        $this->repository->save($evaluation1);

        $teacher = $evaluation1->getTeacher();
        $this->assertInstanceOf(Teacher::class, $teacher);

        $evaluation2 = new TeacherEvaluation();
        $evaluation2->setId('test-eval2-' . uniqid());
        $evaluation2->setTeacher($teacher);
        $evaluation2->setEvaluatorType('student');
        $evaluation2->setEvaluatorId('student-001');
        $evaluation2->setEvaluationType('课程评价');
        $evaluation2->setEvaluationDate(new \DateTimeImmutable());
        $evaluation2->setOverallScore(9.0);
        $evaluation2->setEvaluationStatus('completed');
        $this->repository->save($evaluation2);

        $evaluation3 = new TeacherEvaluation();
        $evaluation3->setId('test-eval3-' . uniqid());
        $evaluation3->setTeacher($teacher);
        $evaluation3->setEvaluatorType('manager');
        $evaluation3->setEvaluatorId('manager-001');
        $evaluation3->setEvaluationType('年度评价');
        $evaluation3->setEvaluationDate(new \DateTimeImmutable());
        $evaluation3->setOverallScore(7.0);
        $evaluation3->setEvaluationStatus('completed');
        $this->repository->save($evaluation3);

        // 测试平均分计算
        $averageScore = $this->repository->getAverageScore($teacher);
        $this->assertEqualsWithDelta(8.0, $averageScore, 0.1, '平均分应该是8.0');
    }

    public function testGetAverageScoreByEvaluatorType(): void
    {
        // 创建教师和不同类型的评价
        $evaluation1 = $this->createNewEntity();
        $evaluation1->setEvaluatorType('student');
        $evaluation1->setOverallScore(9.0);
        $this->repository->save($evaluation1);

        $teacher = $evaluation1->getTeacher();
        $this->assertInstanceOf(Teacher::class, $teacher);

        $evaluation2 = new TeacherEvaluation();
        $evaluation2->setId('test-eval-student2-' . uniqid());
        $evaluation2->setTeacher($teacher);
        $evaluation2->setEvaluatorType('student');
        $evaluation2->setEvaluatorId('student-002');
        $evaluation2->setEvaluationType('课程评价');
        $evaluation2->setEvaluationDate(new \DateTimeImmutable());
        $evaluation2->setOverallScore(7.0);
        $evaluation2->setEvaluationStatus('completed');
        $this->repository->save($evaluation2);

        // 创建不同类型的评价
        $evaluation3 = new TeacherEvaluation();
        $evaluation3->setId('test-eval-manager-' . uniqid());
        $evaluation3->setTeacher($teacher);
        $evaluation3->setEvaluatorType('manager');
        $evaluation3->setEvaluatorId('manager-001');
        $evaluation3->setEvaluationType('年度评价');
        $evaluation3->setEvaluationDate(new \DateTimeImmutable());
        $evaluation3->setOverallScore(8.5);
        $evaluation3->setEvaluationStatus('completed');
        $this->repository->save($evaluation3);

        // 测试学生评价的平均分
        $studentAverage = $this->repository->getAverageScoreByEvaluatorType($teacher, 'student');
        $this->assertEqualsWithDelta(8.0, $studentAverage, 0.1, '学生评价平均分应该是8.0');

        // 测试管理层评价的平均分
        $managerAverage = $this->repository->getAverageScoreByEvaluatorType($teacher, 'manager');
        $this->assertEqualsWithDelta(8.5, $managerAverage, 0.1, '管理层评价平均分应该是8.5');

        // 测试不存在的评价者类型
        $nonExistentAverage = $this->repository->getAverageScoreByEvaluatorType($teacher, 'nonexistent');
        $this->assertEquals(0.0, $nonExistentAverage);
    }

    public function testGetEvaluationStatistics(): void
    {
        // 创建教师和多种类型的评价
        $evaluation1 = $this->createNewEntity();
        $evaluation1->setEvaluatorType('学员');
        $evaluation1->setOverallScore(8.0);
        $this->repository->save($evaluation1);

        $teacher = $evaluation1->getTeacher();
        $this->assertInstanceOf(Teacher::class, $teacher);

        // 创建更多不同类型的评价
        $evaluationTypes = [
            ['学员', 9.0],
            ['同行', 7.5],
            ['管理层', 8.5],
            ['学员', 8.5],
        ];

        foreach ($evaluationTypes as $index => [$type, $score]) {
            $evaluation = new TeacherEvaluation();
            $evaluation->setId('test-eval-stat-' . $index . '-' . uniqid());
            $evaluation->setTeacher($teacher);
            $evaluation->setEvaluatorType($type);
            $evaluation->setEvaluatorId($type . '-' . ($index + 1));
            $evaluation->setEvaluationType('课程评价');
            $evaluation->setEvaluationDate(new \DateTimeImmutable());
            $evaluation->setOverallScore($score);
            $evaluation->setEvaluationStatus('completed');
            $this->repository->save($evaluation);
        }

        $statistics = $this->repository->getEvaluationStatistics($teacher);

        $this->assertIsArray($statistics);
        $this->assertArrayHasKey('total', $statistics);
        $this->assertArrayHasKey('student', $statistics);
        $this->assertArrayHasKey('peer', $statistics);
        $this->assertArrayHasKey('manager', $statistics);
        $this->assertArrayHasKey('averageScore', $statistics);

        $this->assertEquals(5, $statistics['total']); // 总共5个评价
        $this->assertEquals(3, $statistics['student']); // 3个学生评价
        $this->assertEquals(1, $statistics['peer']); // 1个同行评价
        $this->assertEquals(1, $statistics['manager']); // 1个管理层评价
        $this->assertGreaterThan(0, $statistics['averageScore']);
    }

    public function testGetTopRatedTeachers(): void
    {
        // 创建多个教师和评价
        $teachers = [];
        $expectedScores = [9.5, 8.5, 7.5, 9.0, 8.0];

        foreach ($expectedScores as $index => $score) {
            $evaluation = $this->createNewEntity();
            $evaluation->setOverallScore($score);
            $this->repository->save($evaluation);

            $teacher = $evaluation->getTeacher();
            $this->assertInstanceOf(Teacher::class, $teacher);
            $teacher->setTeacherName('高分教师' . ($index + 1));
            self::getEntityManager()->persist($teacher);
            self::getEntityManager()->flush();

            $teachers[] = $teacher;
        }

        $topRated = $this->repository->getTopRatedTeachers(3);
        $this->assertIsArray($topRated);
        $this->assertLessThanOrEqual(3, count($topRated));

        // 验证按平均分降序排列
        $previousScore = PHP_FLOAT_MAX;
        foreach ($topRated as $item) {
            $this->assertIsArray($item);
            $this->assertArrayHasKey('avgScore', $item);
            $this->assertArrayHasKey('teacherName', $item);
            $this->assertArrayHasKey('teacherCode', $item);

            $avgScore = $item['avgScore'];
            $this->assertIsNumeric($avgScore);
            $currentScore = (float) $avgScore;
            $this->assertLessThanOrEqual(
                $previousScore,
                $currentScore,
                '教师排名应该按平均分降序排列'
            );
            $previousScore = $currentScore;
        }
    }

    public function testHasEvaluated(): void
    {
        // 创建评价
        $evaluation = $this->createNewEntity();
        $evaluation->setEvaluatorId('unique-evaluator-001');
        $evaluation->setEvaluationType('特定课程评价');
        $this->repository->save($evaluation);

        $teacher = $evaluation->getTeacher();
        $this->assertInstanceOf(Teacher::class, $teacher);

        // 测试已评价的情况
        $hasEvaluated = $this->repository->hasEvaluated(
            $teacher,
            'unique-evaluator-001',
            '特定课程评价'
        );
        $this->assertTrue($hasEvaluated, '应该检测到评价者已经评价过');

        // 测试未评价的情况
        $hasNotEvaluated = $this->repository->hasEvaluated(
            $teacher,
            'unique-evaluator-002',
            '特定课程评价'
        );
        $this->assertFalse($hasNotEvaluated, '应该检测到评价者未评价过');

        // 测试不同评价类型
        $differentType = $this->repository->hasEvaluated(
            $teacher,
            'unique-evaluator-001',
            '年度评价'
        );
        $this->assertFalse($differentType, '不同评价类型应该返回false');
    }

    public function testFindByDateRangeWithRealData(): void
    {
        // 创建不同日期的评价
        $startDate = new \DateTimeImmutable('-10 days');
        $endDate = new \DateTimeImmutable('-1 day');

        $evaluation1 = $this->createNewEntity();
        $evaluation1->setEvaluationDate(new \DateTimeImmutable('-5 days'));
        $this->repository->save($evaluation1);

        $evaluation2 = $this->createNewEntity();
        $evaluation2->setEvaluationDate(new \DateTimeImmutable('-20 days')); // 超出范围
        $this->repository->save($evaluation2);

        $evaluation3 = $this->createNewEntity();
        $evaluation3->setEvaluationDate(new \DateTimeImmutable('-3 days'));
        $this->repository->save($evaluation3);

        $results = $this->repository->findByDateRange($startDate, $endDate);
        $this->assertIsArray($results);

        // 验证结果在指定日期范围内
        foreach ($results as $evaluation) {
            $this->assertInstanceOf(TeacherEvaluation::class, $evaluation);
            $evalDate = $evaluation->getEvaluationDate();
            $this->assertGreaterThanOrEqual($startDate, $evalDate);
            $this->assertLessThanOrEqual($endDate, $evalDate);
        }

        // 至少应该包含evaluation1和evaluation3
        $foundEvalIds = array_map(fn ($eval) => $eval->getId(), $results);
        $this->assertContains($evaluation1->getId(), $foundEvalIds);
        $this->assertContains($evaluation3->getId(), $foundEvalIds);
        $this->assertNotContains($evaluation2->getId(), $foundEvalIds);
    }

    public function testFindRecentEvaluationsWithRealData(): void
    {
        // 创建教师
        $teacher = new Teacher();
        $teacher->setId('recent-eval-teacher-' . uniqid());
        $teacher->setTeacherCode('RET001');
        $teacher->setTeacherName('最近评价测试教师');
        $teacher->setTeacherType('专职');
        $teacher->setGender('女');
        $teacher->setBirthDate(new \DateTimeImmutable('1985-03-15'));
        $teacher->setIdCard('110101198503159999');
        $teacher->setPhone('13900139999');
        $teacher->setEducation('硕士');
        $teacher->setMajor('教育学');
        $teacher->setGraduateSchool('北京师范大学');
        $teacher->setGraduateDate(new \DateTimeImmutable('2008-07-01'));
        $teacher->setWorkExperience(15);
        $teacher->setTeacherStatus('在职');
        $teacher->setJoinDate(new \DateTimeImmutable('2008-09-01'));
        self::getEntityManager()->persist($teacher);
        self::getEntityManager()->flush();

        // 创建最近的评价
        $recentEvaluation = new TeacherEvaluation();
        $recentEvaluation->setId('recent-eval1-' . uniqid());
        $recentEvaluation->setTeacher($teacher);
        $recentEvaluation->setEvaluatorType('student');
        $recentEvaluation->setEvaluatorId('student-recent-001');
        $recentEvaluation->setEvaluationType('特定课程');
        $recentEvaluation->setEvaluationDate(new \DateTimeImmutable('-5 days'));
        $recentEvaluation->setOverallScore(8.5);
        $recentEvaluation->setEvaluationStatus('completed');
        $this->repository->save($recentEvaluation);

        // 创建较早的评价
        $oldEvaluation = new TeacherEvaluation();
        $oldEvaluation->setId('old-eval1-' . uniqid());
        $oldEvaluation->setTeacher($teacher);
        $oldEvaluation->setEvaluatorType('student');
        $oldEvaluation->setEvaluatorId('student-old-001');
        $oldEvaluation->setEvaluationType('特定课程');
        $oldEvaluation->setEvaluationDate(new \DateTimeImmutable('-60 days'));
        $oldEvaluation->setOverallScore(7.5);
        $oldEvaluation->setEvaluationStatus('completed');
        $this->repository->save($oldEvaluation);

        // 创建不同类型的评价
        $differentTypeEval = new TeacherEvaluation();
        $differentTypeEval->setId('diff-eval1-' . uniqid());
        $differentTypeEval->setTeacher($teacher);
        $differentTypeEval->setEvaluatorType('student');
        $differentTypeEval->setEvaluatorId('student-diff-001');
        $differentTypeEval->setEvaluationType('年度评价');
        $differentTypeEval->setEvaluationDate(new \DateTimeImmutable('-3 days'));
        $differentTypeEval->setOverallScore(9.0);
        $differentTypeEval->setEvaluationStatus('completed');
        $this->repository->save($differentTypeEval);

        // 测试获取最近30天的特定类型评价
        $recentEvaluations = $this->repository->findRecentEvaluations($teacher, '特定课程', 30);
        $this->assertIsArray($recentEvaluations);

        // 应该包含最近的评价，不包含较早的评价和不同类型的评价
        $foundRecentIds = array_map(fn ($eval) => $eval->getId(), $recentEvaluations);
        $this->assertContains($recentEvaluation->getId(), $foundRecentIds);
        $this->assertNotContains($oldEvaluation->getId(), $foundRecentIds);
        $this->assertNotContains($differentTypeEval->getId(), $foundRecentIds);

        // 验证按时间倒序排列
        $previousDate = null;
        foreach ($recentEvaluations as $evaluation) {
            $currentDate = $evaluation->getEvaluationDate();
            if (null !== $previousDate) {
                $this->assertGreaterThanOrEqual(
                    $currentDate,
                    $previousDate,
                    '最近评价应该按时间倒序排列'
                );
            }
            $previousDate = $currentDate;
        }
    }

    public function testSaveAndRemove(): void
    {
        // 测试保存功能
        $evaluation = $this->createNewEntity();
        $evaluation->setOverallScore(9.2);
        $evaluation->setEvaluationComments('优秀的教学表现');

        // 保存但不刷新
        $this->repository->save($evaluation, false);
        self::getEntityManager()->flush();

        $this->assertNotNull($evaluation->getId());

        // 测试删除功能
        $evaluationId = $evaluation->getId();
        $this->repository->remove($evaluation);

        $deletedEvaluation = $this->repository->find($evaluationId);
        $this->assertNull($deletedEvaluation, '评价记录应该被删除');
    }
}
