<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Helper;

/**
 * CSV报告格式化助手
 * 负责将教师报告数据格式化为CSV格式
 */
class ReportCsvFormatter
{
    /**
     * 格式化绩效报告为CSV
     * @param array<string, mixed> $data
     */
    public function formatPerformance(array $data): string
    {
        if (!isset($data['ranking'])) {
            return '';
        }

        $header = "排名,教师姓名,教师编号,教师类型,绩效分数,绩效等级,平均评价\n";

        return $this->formatData($data['ranking'], fn (array $item) => $this->formatPerformanceRow($item), $header);
    }

    /**
     * 格式化评价报告为CSV
     * @param array<string, mixed> $data
     */
    public function formatEvaluation(array $data): string
    {
        if (!isset($data['teachers'])) {
            return '';
        }

        $header = "教师姓名,教师编号,教师类型,平均分数,评价次数\n";

        return $this->formatData($data['teachers'], fn (array $item) => $this->formatEvaluationRow($item), $header);
    }

    /**
     * 格式化摘要报告为CSV
     * @param array<string, mixed> $data
     */
    public function formatSummary(array $data): string
    {
        if (!isset($data['top_performers'])) {
            return '';
        }

        $header = "教师姓名,教师编号,平均评分\n";

        return $this->formatData($data['top_performers'], fn (array $performer) => $this->formatSummaryRow($performer), $header);
    }

    /**
     * 格式化统计报告为CSV
     * @param array<string, mixed> $data
     */
    public function formatStatistics(array $data): string
    {
        if (!isset($data['teacher_statistics'])) {
            return '';
        }

        return $this->buildStatisticsCsv($data['teacher_statistics']);
    }

    /**
     * 格式化绩效行
     * @param array<string, mixed> $item
     */
    private function formatPerformanceRow(array $item): string
    {
        return sprintf(
            "%d,%s,%s,%s,%.2f,%s,%.2f\n",
            $this->extractInt($item, 'rank', 0),
            $this->castToString($item['teacher_name'] ?? ''),
            $this->castToString($item['teacher_code'] ?? ''),
            $this->castToString($item['teacher_type'] ?? ''),
            $this->extractFloat($item, 'performance_score', 0.0),
            $this->castToString($item['performance_level'] ?? ''),
            $this->extractFloat($item, 'average_evaluation', 0.0)
        );
    }

    /**
     * 格式化评价行
     * @param array<string, mixed> $item
     */
    private function formatEvaluationRow(array $item): string
    {
        return sprintf(
            "%s,%s,%s,%.2f,%d\n",
            $this->castToString($item['teacher_name'] ?? ''),
            $this->castToString($item['teacher_code'] ?? ''),
            $this->castToString($item['teacher_type'] ?? ''),
            $this->extractFloat($item, 'average_score', 0.0),
            $this->extractInt($item, 'evaluation_count', 0)
        );
    }

    /**
     * 格式化摘要行
     * @param array<string, mixed> $performer
     */
    private function formatSummaryRow(array $performer): string
    {
        return sprintf(
            "%s,%s,%.2f\n",
            $this->castToString($performer['teacher_name'] ?? ''),
            $this->castToString($performer['teacher_code'] ?? ''),
            $this->extractFloat($performer, 'average_rating', 0.0)
        );
    }

    /**
     * 构建统计CSV
     * @param mixed $teacherStatistics
     */
    private function buildStatisticsCsv($teacherStatistics): string
    {
        $output = "指标,数值\n";

        if (!is_array($teacherStatistics)) {
            return $output;
        }

        foreach ($teacherStatistics as $key => $value) {
            $output .= sprintf(
                "%s,%s\n",
                $this->castToString($key),
                $this->castToString($value)
            );
        }

        return $output;
    }

    /**
     * 通用数据格式化
     * @param mixed $dataCollection
     * @param callable(array<string, mixed>): string $formatter
     */
    private function formatData($dataCollection, callable $formatter, string $header): string
    {
        if (!is_array($dataCollection)) {
            return $header;
        }

        $output = $header;
        foreach ($dataCollection as $item) {
            if (!is_array($item)) {
                continue;
            }
            /** @var array<string, mixed> $typedItem */
            $typedItem = $item;
            $output .= $formatter($typedItem);
        }

        return $output;
    }

    /**
     * 提取整数值
     * @param array<string, mixed> $data
     */
    private function extractInt(array $data, string $key, int $default): int
    {
        $value = $data[$key] ?? null;

        return is_numeric($value) ? (int) $value : $default;
    }

    /**
     * 提取浮点值
     * @param array<string, mixed> $data
     */
    private function extractFloat(array $data, string $key, float $default): float
    {
        $value = $data[$key] ?? null;

        return is_numeric($value) ? (float) $value : $default;
    }

    /**
     * 安全转换为字符串
     * @param mixed $value
     */
    private function castToString($value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }
}
