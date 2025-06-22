<?php

namespace Tourze\TrainTeacherBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\TrainTeacherBundle\Entity\Teacher;
use Tourze\TrainTeacherBundle\Entity\TeacherPerformance;

/**
 * TeacherPerformance实体单元测试
 */
class TeacherPerformanceTest extends TestCase
{
    private TeacherPerformance $performance;
    private Teacher $teacher;

    protected function setUp(): void
    {
        $this->performance = new TeacherPerformance();
        $this->teacher = new Teacher();
    }

    public function test_constructor_sets_default_values(): void
    {
        $performance = new TeacherPerformance();
        
        $this->assertInstanceOf(\DateTimeInterface::class, $performance->getCreateTime());
    }

    public function test_id_getter_and_setter(): void
    {
        $id = 'perf_123';
        $this->performance->setId($id);
        
        $this->assertEquals($id, $this->performance->getId());
    }

    public function test_teacher_getter_and_setter(): void
    {
        $this->performance->setTeacher($this->teacher);
        
        $this->assertSame($this->teacher, $this->performance->getTeacher());
    }

    public function test_performance_period_getter_and_setter(): void
    {
        $period = new \DateTime('2024-01-01');
        $this->performance->setPerformancePeriod($period);
        
        $this->assertEquals($period, $this->performance->getPerformancePeriod());
    }

    public function test_average_evaluation_getter_and_setter(): void
    {
        $average = 4.5;
        $this->performance->setAverageEvaluation($average);
        
        $this->assertEquals($average, $this->performance->getAverageEvaluation());
    }

    public function test_performance_metrics_getter_and_setter(): void
    {
        $metrics = [
            'teachingHours' => 80.0,
            'studentSatisfaction' => 85.0,
            'courseCompletionRate' => 90.0,
            'attendanceRate' => 95.0,
            'innovationScore' => 75.0
        ];
        $this->performance->setPerformanceMetrics($metrics);
        
        $this->assertEquals($metrics, $this->performance->getPerformanceMetrics());
    }

    public function test_performance_metrics_default_empty_array(): void
    {
        $performance = new TeacherPerformance();
        
        $this->assertEquals([], $performance->getPerformanceMetrics());
    }

    public function test_performance_score_getter_and_setter(): void
    {
        $score = 85.75;
        $this->performance->setPerformanceScore($score);
        
        $this->assertEquals($score, $this->performance->getPerformanceScore());
    }

    public function test_performance_level_getter_and_setter(): void
    {
        $level = '优秀';
        $this->performance->setPerformanceLevel($level);
        
        $this->assertEquals($level, $this->performance->getPerformanceLevel());
    }

    public function test_achievements_getter_and_setter(): void
    {
        $achievements = ['授课达人', '学员最爱', '全勤教师'];
        $this->performance->setAchievements($achievements);
        
        $this->assertEquals($achievements, $this->performance->getAchievements());
    }

    public function test_achievements_default_empty_array(): void
    {
        $performance = new TeacherPerformance();
        
        $this->assertEquals([], $performance->getAchievements());
    }

    public function test_create_time_getter_and_setter(): void
    {
        $createTime = new \DateTime('2024-01-01 10:00:00');
        $this->performance->setCreateTime($createTime);
        
        $this->assertEquals($createTime, $this->performance->getCreateTime());
    }

    public function test_fluent_interface(): void
    {
        $period = new \DateTime('2024-01-01');
        
        $result = $this->performance
            ->setId('perf_001')
            ->setTeacher($this->teacher)
            ->setPerformancePeriod($period)
            ->setAverageEvaluation(4.5)
            ->setPerformanceScore(85.75);
        
        $this->assertSame($this->performance, $result);
        $this->assertEquals('perf_001', $this->performance->getId());
        $this->assertSame($this->teacher, $this->performance->getTeacher());
        $this->assertEquals($period, $this->performance->getPerformancePeriod());
        $this->assertEquals(4.5, $this->performance->getAverageEvaluation());
        $this->assertEquals(85.75, $this->performance->getPerformanceScore());
    }

    public function test_complete_performance_data(): void
    {
        $period = new \DateTime('2024-01-01');
        $metrics = [
            'teachingHours' => 80.0,
            'studentSatisfaction' => 85.0,
            'courseCompletionRate' => 90.0,
            'attendanceRate' => 95.0,
            'innovationScore' => 75.0
        ];
        $achievements = ['授课达人', '学员最爱', '全勤教师'];

        $this->performance
            ->setId('perf_001')
            ->setTeacher($this->teacher)
            ->setPerformancePeriod($period)
            ->setAverageEvaluation(4.5)
            ->setPerformanceMetrics($metrics)
            ->setPerformanceScore(85.75)
            ->setPerformanceLevel('优秀')
            ->setAchievements($achievements);

        $this->assertEquals('perf_001', $this->performance->getId());
        $this->assertSame($this->teacher, $this->performance->getTeacher());
        $this->assertEquals($period, $this->performance->getPerformancePeriod());
        $this->assertEquals(4.5, $this->performance->getAverageEvaluation());
        $this->assertEquals($metrics, $this->performance->getPerformanceMetrics());
        $this->assertEquals(85.75, $this->performance->getPerformanceScore());
        $this->assertEquals('优秀', $this->performance->getPerformanceLevel());
        $this->assertEquals($achievements, $this->performance->getAchievements());
    }

    public function test_different_performance_levels(): void
    {
        $levels = ['优秀', '良好', '一般', '合格', '较差'];
        
        foreach ($levels as $level) {
            $this->performance->setPerformanceLevel($level);
            $this->assertEquals($level, $this->performance->getPerformanceLevel());
        }
    }

    public function test_score_precision(): void
    {
        $preciseScore = 85.123;
        $this->performance->setPerformanceScore($preciseScore);
        
        $this->assertEquals($preciseScore, $this->performance->getPerformanceScore());
    }

    public function test_evaluation_precision(): void
    {
        $preciseEvaluation = 4.567;
        $this->performance->setAverageEvaluation($preciseEvaluation);
        
        $this->assertEquals($preciseEvaluation, $this->performance->getAverageEvaluation());
    }

    public function test_empty_arrays_handling(): void
    {
        $this->performance
            ->setPerformanceMetrics([])
            ->setAchievements([]);

        $this->assertEquals([], $this->performance->getPerformanceMetrics());
        $this->assertEquals([], $this->performance->getAchievements());
    }

    public function test_complex_metrics_structure(): void
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
                'trainingHours' => 40
            ]
        ];
        
        $this->performance->setPerformanceMetrics($complexMetrics);
        
        $this->assertEquals($complexMetrics, $this->performance->getPerformanceMetrics());
        $this->assertEquals(3, $this->performance->getPerformanceMetrics()['additionalMetrics']['researchProjects']);
    }

    public function test_multiple_achievements(): void
    {
        $achievements = [
            '授课达人',
            '学员最爱',
            '全勤教师',
            '创新先锋',
            '完课之星',
            '优秀导师'
        ];
        
        $this->performance->setAchievements($achievements);
        
        $this->assertEquals($achievements, $this->performance->getAchievements());
        $this->assertCount(6, $this->performance->getAchievements());
    }

    public function test_period_different_formats(): void
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

    public function test_boundary_values(): void
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
} 