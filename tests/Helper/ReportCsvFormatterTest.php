<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Helper;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TrainTeacherBundle\Helper\ReportCsvFormatter;

/**
 * @internal
 */
#[CoversClass(ReportCsvFormatter::class)]
class ReportCsvFormatterTest extends TestCase
{
    private ReportCsvFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new ReportCsvFormatter();
    }

    public function testFormatPerformanceWithValidData(): void
    {
        $data = [
            'ranking' => [
                [
                    'rank' => 1,
                    'teacher_name' => '张三',
                    'teacher_code' => 'T001',
                    'teacher_type' => 'full-time',
                    'performance_score' => 95.5,
                    'performance_level' => 'A',
                    'average_evaluation' => 4.8,
                ],
            ],
        ];

        $result = $this->formatter->formatPerformance($data);

        self::assertStringContainsString('排名,教师姓名,教师编号', $result);
        self::assertStringContainsString('1,张三,T001', $result);
        self::assertStringContainsString('95.50', $result);
    }

    public function testFormatPerformanceWithEmptyData(): void
    {
        $data = [];
        $result = $this->formatter->formatPerformance($data);

        self::assertSame('', $result);
    }

    public function testFormatEvaluationWithValidData(): void
    {
        $data = [
            'teachers' => [
                [
                    'teacher_name' => '李四',
                    'teacher_code' => 'T002',
                    'teacher_type' => 'part-time',
                    'average_score' => 4.5,
                    'evaluation_count' => 10,
                ],
            ],
        ];

        $result = $this->formatter->formatEvaluation($data);

        self::assertStringContainsString('教师姓名,教师编号,教师类型', $result);
        self::assertStringContainsString('李四,T002,part-time', $result);
        self::assertStringContainsString('4.50,10', $result);
    }

    public function testFormatSummaryWithValidData(): void
    {
        $data = [
            'top_performers' => [
                [
                    'teacher_name' => '王五',
                    'teacher_code' => 'T003',
                    'average_rating' => 4.9,
                ],
            ],
        ];

        $result = $this->formatter->formatSummary($data);

        self::assertStringContainsString('教师姓名,教师编号,平均评分', $result);
        self::assertStringContainsString('王五,T003,4.90', $result);
    }

    public function testFormatStatisticsWithValidData(): void
    {
        $data = [
            'teacher_statistics' => [
                'total_teachers' => 100,
                'active_teachers' => 85,
            ],
        ];

        $result = $this->formatter->formatStatistics($data);

        self::assertStringContainsString('指标,数值', $result);
        self::assertStringContainsString('total_teachers,100', $result);
        self::assertStringContainsString('active_teachers,85', $result);
    }
}
