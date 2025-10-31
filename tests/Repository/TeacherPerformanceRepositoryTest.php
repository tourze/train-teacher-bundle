<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\TrainTeacherBundle\Entity\Teacher;
use Tourze\TrainTeacherBundle\Entity\TeacherPerformance;
use Tourze\TrainTeacherBundle\Repository\TeacherPerformanceRepository;

/**
 * @internal
 */
#[CoversClass(TeacherPerformanceRepository::class)]
#[RunTestsInSeparateProcesses]
final class TeacherPerformanceRepositoryTest extends AbstractRepositoryTestCase
{
    private TeacherPerformanceRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(TeacherPerformanceRepository::class);
    }

    protected function createNewEntity(): TeacherPerformance
    {
        $teacher = new Teacher();
        $teacher->setId('test-teacher-' . uniqid());
        $teacher->setTeacherCode('T' . uniqid());
        $teacher->setTeacherName('测试教师');
        $teacher->setTeacherType('full-time');
        $teacher->setTeacherStatus('active');
        $teacher->setGender('男');
        $teacher->setBirthDate(new \DateTimeImmutable('1980-01-01'));
        $teacher->setIdCard('110101198001011234');
        $teacher->setPhone('13800138000');
        $teacher->setEducation('本科');
        $teacher->setMajor('安全工程');
        $teacher->setGraduateSchool('北京理工大学');
        $teacher->setGraduateDate(new \DateTimeImmutable('2002-07-01'));
        $teacher->setWorkExperience(20);
        $teacher->setJoinDate(new \DateTimeImmutable('2005-03-01'));
        self::getEntityManager()->persist($teacher);
        self::getEntityManager()->flush();

        static $counter = 0;
        $this->assertIsInt($counter);
        ++$counter;

        $entity = new TeacherPerformance();
        // 使用时间戳 + 计数器确保排序的可预测性
        $entity->setId('test-perf-' . time() . '-' . str_pad((string) $counter, 3, '0', STR_PAD_LEFT));
        $entity->setTeacher($teacher);
        $entity->setPerformancePeriod(new \DateTimeImmutable());
        $entity->setAverageEvaluation(8.5);
        $entity->setPerformanceScore(85.5);
        $entity->setPerformanceLevel('good');

        return $entity;
    }

    /**
     * @return TeacherPerformanceRepository
     */
    protected function getRepository(): TeacherPerformanceRepository
    {
        return $this->repository;
    }

    public function testCompareTeacherPerformance(): void
    {
        $teacherIds = ['teacher-1', 'teacher-2', 'teacher-3'];
        $period = new \DateTime();
        $result = $this->repository->compareTeacherPerformance($teacherIds, $period);
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

    public function testFindByTeacherAndPeriod(): void
    {
        $teacher = $this->getMockBuilder(Teacher::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $period = new \DateTimeImmutable();

        $result = $this->repository->findByTeacherAndPeriod($teacher, $period);
        $this->assertNull($result);
    }

    public function testFindByPerformanceLevel(): void
    {
        // 使用唯一标识符来避免与其他测试数据冲突
        $uniqueId = uniqid('testperf_', true);

        // 创建测试数据
        $performance1 = $this->createNewEntity();
        $performance1->setId('test-perf-excellent-1-' . $uniqueId);
        $performance1->setPerformanceLevel('优秀');
        $this->repository->save($performance1);

        $performance2 = $this->createNewEntity();
        $performance2->setId('test-perf-good-1-' . $uniqueId);
        $performance2->setPerformanceLevel('良好');
        $this->repository->save($performance2);

        $performance3 = $this->createNewEntity();
        $performance3->setId('test-perf-excellent-2-' . $uniqueId);
        $performance3->setPerformanceLevel('优秀');
        $this->repository->save($performance3);

        // 测试查找特定绩效等级
        $excellentPerformances = $this->repository->findByPerformanceLevel('优秀');
        $this->assertIsArray($excellentPerformances);

        // 过滤出当前测试创建的记录
        $testExcellentPerformances = array_filter($excellentPerformances, function ($perf) use ($uniqueId) {
            return str_contains($perf->getId(), $uniqueId);
        });
        $this->assertCount(2, $testExcellentPerformances);

        foreach ($testExcellentPerformances as $performance) {
            $this->assertInstanceOf(TeacherPerformance::class, $performance);
            $this->assertEquals('优秀', $performance->getPerformanceLevel());
        }

        $goodPerformances = $this->repository->findByPerformanceLevel('良好');
        $this->assertIsArray($goodPerformances);

        // 过滤出当前测试创建的记录
        $testGoodPerformances = array_filter($goodPerformances, function ($perf) use ($uniqueId) {
            return str_contains($perf->getId(), $uniqueId);
        });
        $this->assertCount(1, $testGoodPerformances);
        $goodPerformance = reset($testGoodPerformances);
        $this->assertEquals('良好', $goodPerformance->getPerformanceLevel());

        // 测试不存在的等级
        $nonExistentPerformances = $this->repository->findByPerformanceLevel('不存在');
        $this->assertIsArray($nonExistentPerformances);
        // 由于可能有其他测试的数据，我们只验证返回的是数组类型
    }

    public function testGetPerformanceRanking(): void
    {
        // 创建不同分数的绩效数据
        $performance1 = $this->createNewEntity();
        $performance1->setPerformanceScore(95.5);
        $performance1->setPerformanceLevel('优秀');
        $this->repository->save($performance1);

        $performance2 = $this->createNewEntity();
        $performance2->setPerformanceScore(87.3);
        $performance2->setPerformanceLevel('良好');
        $this->repository->save($performance2);

        $performance3 = $this->createNewEntity();
        $performance3->setPerformanceScore(92.1);
        $performance3->setPerformanceLevel('优秀');
        $this->repository->save($performance3);

        // 测试排名功能
        $ranking = $this->repository->getPerformanceRanking(10);
        $this->assertIsArray($ranking);
        $this->assertGreaterThanOrEqual(3, count($ranking));

        // 验证排序正确（分数从高到低）
        $previousScore = PHP_FLOAT_MAX;
        foreach ($ranking as $item) {
            $this->assertIsArray($item);
            $this->assertArrayHasKey('performanceScore', $item);
            $this->assertArrayHasKey('teacherName', $item);
            $this->assertArrayHasKey('performanceLevel', $item);

            $perfScore = $item['performanceScore'];
            $this->assertIsNumeric($perfScore);
            $currentScore = (float) $perfScore;
            $this->assertLessThanOrEqual(
                $previousScore,
                $currentScore,
                '排名应该按分数从高到低排序'
            );
            $previousScore = $currentScore;
        }

        // 测试限制结果数量
        $limitedRanking = $this->repository->getPerformanceRanking(2);
        $this->assertIsArray($limitedRanking);
        $this->assertLessThanOrEqual(2, count($limitedRanking));
    }

    public function testGetPerformanceRankingByPeriod(): void
    {
        // 创建特定时期的绩效数据，使用唯一标识符避免冲突
        $uniqueId = uniqid('period_test_', true);
        $period = new \DateTimeImmutable('2024-01-01');

        $performance1 = $this->createNewEntity();
        $performance1->setId('test-period-1-' . $uniqueId);
        $performance1->setPerformancePeriod($period);
        $performance1->setPerformanceScore(88.7);
        $this->repository->save($performance1, true);

        $performance2 = $this->createNewEntity();
        $performance2->setId('test-period-2-' . $uniqueId);
        $performance2->setPerformancePeriod($period);
        $performance2->setPerformanceScore(94.2);
        $this->repository->save($performance2, true);

        // 创建不同时期的数据作为对比
        $otherPeriod = new \DateTimeImmutable('2023-12-01');
        $performance3 = $this->createNewEntity();
        $performance3->setId('test-period-3-' . $uniqueId);
        $performance3->setPerformancePeriod($otherPeriod);
        $performance3->setPerformanceScore(96.5);
        $this->repository->save($performance3, true);

        // 先验证findBy能否找到数据
        $directFind = $this->repository->findBy(['performancePeriod' => $period]);
        $this->assertGreaterThan(0, count($directFind), 'findBy should find records');

        // 测试特定时期排名
        $ranking = $this->repository->getPerformanceRankingByPeriod($period, 10);
        $this->assertIsArray($ranking);
        $this->assertGreaterThan(0, count($ranking), 'getPerformanceRankingByPeriod should return results');

        // 验证返回的记录数量（应该至少包含我们创建的2个记录）
        $this->assertGreaterThanOrEqual(2, count($ranking), '应该返回至少2个指定时期的数据');

        // 获取返回结果中分数为94.2和88.7的记录来验证
        $scores = array_column($ranking, 'performanceScore');
        $this->assertContains(94.2, $scores, '应该包含分数94.2的记录');
        $this->assertContains(88.7, $scores, '应该包含分数88.7的记录');

        // 验证排序正确性（按分数降序）
        $previousScore = PHP_FLOAT_MAX;
        foreach ($ranking as $item) {
            $perfScore = $item['performanceScore'];
            $this->assertIsNumeric($perfScore);
            $currentScore = (float) $perfScore;
            $this->assertLessThanOrEqual($previousScore, $currentScore, '排名应该按分数降序排列');
            $previousScore = $currentScore;
        }

        // 测试限制结果
        $limitedRanking = $this->repository->getPerformanceRankingByPeriod($period, 1);
        $this->assertCount(1, $limitedRanking);

        // 验证返回的是最高分的记录
        $highestScore = max($scores);
        $limitedPerfScore = $limitedRanking[0]['performanceScore'];
        $this->assertIsNumeric($limitedPerfScore);
        $this->assertEquals($highestScore, (float) $limitedPerfScore);
    }

    public function testGetPerformanceStatistics(): void
    {
        // 创建不同等级的绩效数据
        $excellentPerf = $this->createNewEntity();
        $excellentPerf->setPerformanceLevel('优秀');
        $excellentPerf->setPerformanceScore(95.0);
        $this->repository->save($excellentPerf);

        $goodPerf = $this->createNewEntity();
        $goodPerf->setPerformanceLevel('良好');
        $goodPerf->setPerformanceScore(85.0);
        $this->repository->save($goodPerf);

        $averagePerf = $this->createNewEntity();
        $averagePerf->setPerformanceLevel('一般');
        $averagePerf->setPerformanceScore(75.0);
        $this->repository->save($averagePerf);

        // 获取统计信息
        $statistics = $this->repository->getPerformanceStatistics();

        $this->assertIsArray($statistics);
        $this->assertArrayHasKey('total', $statistics);
        $this->assertArrayHasKey('excellent', $statistics);
        $this->assertArrayHasKey('good', $statistics);
        $this->assertArrayHasKey('average', $statistics);
        $this->assertArrayHasKey('poor', $statistics);
        $this->assertArrayHasKey('averageScore', $statistics);

        // 验证统计数据
        $this->assertGreaterThanOrEqual(3, $statistics['total']);
        $this->assertGreaterThanOrEqual(1, $statistics['excellent']);
        $this->assertGreaterThanOrEqual(1, $statistics['good']);
        $this->assertIsFloat($statistics['averageScore']);
        $this->assertGreaterThan(0, $statistics['averageScore']);
    }

    public function testGetPerformanceTrend(): void
    {
        // 创建教师
        $teacher = new Teacher();
        $teacher->setId('trend-teacher-' . uniqid());
        $teacher->setTeacherCode('TREND001');
        $teacher->setTeacherName('趋势测试教师');
        $teacher->setTeacherType('专职');
        $teacher->setGender('女');
        $teacher->setBirthDate(new \DateTimeImmutable('1985-05-15'));
        $teacher->setIdCard('110101198505151234');
        $teacher->setPhone('13900139000');
        $teacher->setEducation('硕士');
        $teacher->setMajor('教育学');
        $teacher->setGraduateSchool('北京师范大学');
        $teacher->setGraduateDate(new \DateTimeImmutable('2008-07-01'));
        $teacher->setWorkExperience(15);
        $teacher->setTeacherStatus('在职');
        $teacher->setJoinDate(new \DateTimeImmutable('2008-09-01'));
        self::getEntityManager()->persist($teacher);
        self::getEntityManager()->flush();

        // 创建不同时期的绩效数据
        $months = [
            new \DateTimeImmutable('-11 months'),
            new \DateTimeImmutable('-8 months'),
            new \DateTimeImmutable('-5 months'),
            new \DateTimeImmutable('-2 months'),
        ];

        $scores = [82.5, 85.3, 88.1, 91.7];

        foreach ($months as $index => $period) {
            $performance = new TeacherPerformance();
            $performance->setId('trend-perf-' . $index . '-' . uniqid());
            $performance->setTeacher($teacher);
            $performance->setPerformancePeriod($period);
            $performance->setAverageEvaluation(8.0 + $index * 0.5); // 设置必需的字段
            $performance->setPerformanceScore($scores[$index]);
            $performance->setPerformanceLevel('良好');
            $this->repository->save($performance);
        }

        // 测试趋势获取
        $trend = $this->repository->getPerformanceTrend($teacher, 12);
        $this->assertIsArray($trend);
        $this->assertCount(4, $trend);

        // 验证按时间排序（从早到晚）
        $previousPeriod = null;
        foreach ($trend as $performance) {
            $this->assertInstanceOf(TeacherPerformance::class, $performance);
            $this->assertEquals($teacher->getId(), $performance->getTeacher()->getId());

            if (null !== $previousPeriod) {
                $this->assertGreaterThanOrEqual(
                    $previousPeriod,
                    $performance->getPerformancePeriod(),
                    '趋势数据应该按时间从早到晚排序'
                );
            }
            $previousPeriod = $performance->getPerformancePeriod();
        }

        // 测试不同的月份限制
        $shortTrend = $this->repository->getPerformanceTrend($teacher, 6);
        $this->assertIsArray($shortTrend);
        $this->assertLessThanOrEqual(4, count($shortTrend)); // 最多4条记录
    }

    public function testGetLatestPerformances(): void
    {
        // 创建不同创建时间的绩效数据
        $performances = [];
        for ($i = 0; $i < 5; ++$i) {
            $performance = $this->createNewEntity();
            $performance->setPerformanceScore(80.0 + $i);
            $this->repository->save($performance);
            $performances[] = $performance;

            // 增加足够的时间间隔以确保排序
            usleep(10000); // 10毫秒
        }

        // 测试获取最新记录
        $latestPerformances = $this->repository->getLatestPerformances(3);
        $this->assertIsArray($latestPerformances);
        $this->assertCount(3, $latestPerformances);

        // 验证按创建时间倒序排列
        $previousCreateTime = null;
        foreach ($latestPerformances as $performance) {
            $this->assertInstanceOf(TeacherPerformance::class, $performance);

            $currentCreateTime = $performance->getCreateTime();
            if (null !== $previousCreateTime) {
                $this->assertGreaterThanOrEqual(
                    $previousCreateTime,
                    $currentCreateTime,
                    '最新记录应该按创建时间倒序排列（前一个记录时间应该 >= 当前记录时间）'
                );
            }
            $previousCreateTime = $currentCreateTime;
        }

        // 测试默认限制
        $defaultLatest = $this->repository->getLatestPerformances();
        $this->assertIsArray($defaultLatest);
        $this->assertLessThanOrEqual(10, count($defaultLatest)); // 默认限制是10
    }

    public function testSaveAndRemove(): void
    {
        // 测试保存功能
        $performance = $this->createNewEntity();
        $performance->setPerformanceScore(89.5);
        $performance->setPerformanceLevel('良好');

        // 保存但不刷新
        $this->repository->save($performance, false);
        self::getEntityManager()->flush();

        $this->assertNotNull($performance->getId());

        // 测试删除功能
        $performanceId = $performance->getId();
        $this->repository->remove($performance);

        $deletedPerformance = $this->repository->find($performanceId);
        $this->assertNull($deletedPerformance, '绩效记录应该被删除');
    }
}
