<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TrainTeacherBundle\Entity\Teacher;
use Tourze\TrainTeacherBundle\Entity\TeacherPerformance;

/**
 * TeacherPerformance实体单元测试
 *
 * @internal
 */
#[CoversClass(TeacherPerformance::class)]
final class TeacherPerformanceTest extends AbstractEntityTestCase
{
    private TeacherPerformance $performance;

    private Teacher $teacher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->performance = new TeacherPerformance();
        $this->teacher = new Teacher();
    }

    protected function createEntity(): object
    {
        return new TeacherPerformance();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'id' => ['id', 'perf_123'],
            'performancePeriod' => ['performancePeriod', new \DateTime('2024-01-01')],
            'performanceScore' => ['performanceScore', 85.5],
            'performanceLevel' => ['performanceLevel', '优秀'],
            'totalCourses' => ['totalCourses', 20],
            'totalHours' => ['totalHours', 160],
            'studentCount' => ['studentCount', 300],
            'averageScore' => ['averageScore', 4.5],
            'completionRate' => ['completionRate', 95.5],
            'satisfactionRate' => ['satisfactionRate', 92.0],
            'remarks' => ['remarks', '表现优秀，深受学员喜爱'],
            'createTime' => ['createTime', new \DateTime('2024-01-01')],
        ];
    }

    public function testConstructorSetsDefaultValues(): void
    {
        $performance = new TeacherPerformance();
        $this->assertNotNull($performance->getCreateTime());
        $this->assertInstanceOf(\DateTimeInterface::class, $performance->getCreateTime());
    }

    public function testIdGetterAndSetter(): void
    {
        $id = 'perf_123';
        $this->performance->setId($id);

        $this->assertEquals($id, $this->performance->getId());
    }

    public function testTeacherGetterAndSetter(): void
    {
        $this->performance->setTeacher($this->teacher);

        $this->assertSame($this->teacher, $this->performance->getTeacher());
    }

    public function testPerformancePeriodGetterAndSetter(): void
    {
        $period = new \DateTime('2024-01-01');
        $this->performance->setPerformancePeriod($period);

        $this->assertEquals($period, $this->performance->getPerformancePeriod());
    }

    public function testAverageEvaluationGetterAndSetter(): void
    {
        $average = 4.5;
        $this->performance->setAverageEvaluation($average);

        $this->assertEquals($average, $this->performance->getAverageEvaluation());
    }

    public function testPerformanceMetricsGetterAndSetter(): void
    {
        $metrics = [
            'teachingHours' => 80.0,
            'studentSatisfaction' => 85.0,
            'courseCompletionRate' => 90.0,
            'attendanceRate' => 95.0,
            'innovationScore' => 75.0,
        ];
        $this->performance->setPerformanceMetrics($metrics);

        $this->assertEquals($metrics, $this->performance->getPerformanceMetrics());
    }

    public function testPerformanceMetricsDefaultEmptyArray(): void
    {
        $performance = new TeacherPerformance();

        $this->assertEquals([], $performance->getPerformanceMetrics());
    }

    public function testPerformanceScoreGetterAndSetter(): void
    {
        $score = 85.75;
        $this->performance->setPerformanceScore($score);

        $this->assertEquals($score, $this->performance->getPerformanceScore());
    }

    public function testPerformanceLevelGetterAndSetter(): void
    {
        $level = '优秀';
        $this->performance->setPerformanceLevel($level);

        $this->assertEquals($level, $this->performance->getPerformanceLevel());
    }

    public function testAchievementsGetterAndSetter(): void
    {
        $achievements = ['授课达人', '学员最爱', '全勤教师'];
        $this->performance->setAchievements($achievements);

        $this->assertEquals($achievements, $this->performance->getAchievements());
    }

    public function testAchievementsDefaultEmptyArray(): void
    {
        $performance = new TeacherPerformance();

        $this->assertEquals([], $performance->getAchievements());
    }

    public function testCreateTimeGetterAndSetter(): void
    {
        $createTime = new \DateTime('2024-01-01 10:00:00');
        $this->performance->setCreateTime($createTime);

        $this->assertEquals($createTime, $this->performance->getCreateTime());
    }

    public function testSettersWorkCorrectly(): void
    {
        $period = new \DateTime('2024-01-01');

        $this->performance->setId('perf_001');
        $this->performance->setTeacher($this->teacher);
        $this->performance->setPerformancePeriod($period);
        $this->performance->setAverageEvaluation(4.5);
        $this->performance->setPerformanceScore(85.75);

        $this->assertEquals('perf_001', $this->performance->getId());
        $this->assertSame($this->teacher, $this->performance->getTeacher());
        $this->assertEquals($period, $this->performance->getPerformancePeriod());
        $this->assertEquals(4.5, $this->performance->getAverageEvaluation());
        $this->assertEquals(85.75, $this->performance->getPerformanceScore());
    }

    public function testCompletePerformanceData(): void
    {
        $period = new \DateTime('2024-01-01');
        $metrics = [
            'teachingHours' => 80.0,
            'studentSatisfaction' => 85.0,
            'courseCompletionRate' => 90.0,
            'attendanceRate' => 95.0,
            'innovationScore' => 75.0,
        ];
        $achievements = ['授课达人', '学员最爱', '全勤教师'];

        $this->performance->setId('perf_001');
        $this->performance->setTeacher($this->teacher);
        $this->performance->setPerformancePeriod($period);
        $this->performance->setAverageEvaluation(4.5);
        $this->performance->setPerformanceMetrics($metrics);
        $this->performance->setPerformanceScore(85.75);
        $this->performance->setPerformanceLevel('优秀');
        $this->performance->setAchievements($achievements);

        $this->assertEquals('perf_001', $this->performance->getId());
        $this->assertSame($this->teacher, $this->performance->getTeacher());
        $this->assertEquals($period, $this->performance->getPerformancePeriod());
        $this->assertEquals(4.5, $this->performance->getAverageEvaluation());
        $this->assertEquals($metrics, $this->performance->getPerformanceMetrics());
        $this->assertEquals(85.75, $this->performance->getPerformanceScore());
        $this->assertEquals('优秀', $this->performance->getPerformanceLevel());
        $this->assertEquals($achievements, $this->performance->getAchievements());
    }

    public function testDifferentPerformanceLevels(): void
    {
        $levels = ['优秀', '良好', '一般', '合格', '较差'];

        foreach ($levels as $level) {
            $this->performance->setPerformanceLevel($level);
            $this->assertEquals($level, $this->performance->getPerformanceLevel());
        }
    }

    public function testScorePrecision(): void
    {
        $preciseScore = 85.123;
        $this->performance->setPerformanceScore($preciseScore);

        $this->assertEquals($preciseScore, $this->performance->getPerformanceScore());
    }

    public function testEvaluationPrecision(): void
    {
        $preciseEvaluation = 4.567;
        $this->performance->setAverageEvaluation($preciseEvaluation);

        $this->assertEquals($preciseEvaluation, $this->performance->getAverageEvaluation());
    }

    public function testEmptyArraysHandling(): void
    {
        $this->performance->setPerformanceMetrics([]);
        $this->performance->setAchievements([]);

        $this->assertEquals([], $this->performance->getPerformanceMetrics());
        $this->assertEquals([], $this->performance->getAchievements());
    }

    public function testComplexMetricsStructure(): void
    {
        $complexMetrics = [
            'teachingHours' => 80.0,
            'studentSatisfaction' => 85.0,
            'courseCompletionRate' => 90.0,
            'attendanceRate' => 95.0,
            'innovationScore' => 75.0,
            'additionalMetrics' => [
                'researchProjects' => 3,
                'publicationsCount' => 2,
                'trainingHours' => 40,
            ],
        ];

        $this->performance->setPerformanceMetrics($complexMetrics);

        $retrievedMetrics = $this->performance->getPerformanceMetrics();
        $this->assertEquals($complexMetrics, $retrievedMetrics);
        $this->assertIsArray($retrievedMetrics);
        $this->assertArrayHasKey('additionalMetrics', $retrievedMetrics);
        $this->assertIsArray($retrievedMetrics['additionalMetrics']);
        $this->assertEquals(3, $retrievedMetrics['additionalMetrics']['researchProjects']);
    }

    public function testMultipleAchievements(): void
    {
        $achievements = [
            '授课达人',
            '学员最爱',
            '全勤教师',
            '创新先锋',
            '完课之星',
            '优秀导师',
        ];

        $this->performance->setAchievements($achievements);

        $this->assertEquals($achievements, $this->performance->getAchievements());
        $this->assertCount(6, $this->performance->getAchievements());
    }

    public function testPeriodDifferentFormats(): void
    {
        // 测试不同的日期格式
        $periods = [
            new \DateTime('2024-01-01'),
            new \DateTime('2024-02-01'),
            new \DateTime('2024-12-31'),
        ];

        foreach ($periods as $period) {
            $this->performance->setPerformancePeriod($period);
            $this->assertEquals($period, $this->performance->getPerformancePeriod());
        }
    }

    public function testBoundaryValues(): void
    {
        // 测试边界值
        $this->performance->setAverageEvaluation(0.0);
        $this->assertEquals(0.0, $this->performance->getAverageEvaluation());

        $this->performance->setAverageEvaluation(5.0);
        $this->assertEquals(5.0, $this->performance->getAverageEvaluation());

        $this->performance->setPerformanceScore(0.0);
        $this->assertEquals(0.0, $this->performance->getPerformanceScore());

        $this->performance->setPerformanceScore(100.0);
        $this->assertEquals(100.0, $this->performance->getPerformanceScore());
    }

    public function testClassInstantiation(): void
    {
        $instance = new TeacherPerformance();
        $this->assertInstanceOf(TeacherPerformance::class, $instance);
    }

    public function testStringable(): void
    {
        $performance = new TeacherPerformance();
        $performance->setId('test-performance-id');
        $this->assertEquals('test-performance-id', (string) $performance);
    }
}
