<?php

namespace Tourze\TrainTeacherBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Tourze\TrainTeacherBundle\Entity\Teacher;
use Tourze\TrainTeacherBundle\Entity\TeacherPerformance;
use Tourze\TrainTeacherBundle\Exception\PerformanceNotFoundException;
use Tourze\TrainTeacherBundle\Repository\TeacherPerformanceRepository;

/**
 * 教师绩效服务
 * 提供教师绩效计算、评估、排名等核心业务功能
 */
class PerformanceService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TeacherPerformanceRepository $performanceRepository,
        private readonly TeacherService $teacherService,
        private readonly EvaluationService $evaluationService
    ) {
    }

    /**
     * 计算教师绩效
     */
    public function calculatePerformance(string $teacherId, \DateTimeInterface $period): TeacherPerformance
    {
        $teacher = $this->teacherService->getTeacherById($teacherId);
        
        // 检查是否已存在该周期的绩效记录
        $existingPerformance = $this->performanceRepository->findByTeacherAndPeriod($teacher, $period);
        if ((bool) $existingPerformance) {
            // 更新现有记录
            $performance = $existingPerformance;
        } else {
            // 创建新记录
            $performance = new TeacherPerformance();
            $performance->setId($this->generatePerformanceId());
            $performance->setTeacher($teacher);
            $performance->setPerformancePeriod($period);
        }

        // 计算平均评价分数
        $averageEvaluation = $this->evaluationService->calculateAverageEvaluation($teacherId);
        $performance->setAverageEvaluation($averageEvaluation);

        // 计算绩效指标
        $metrics = $this->calculatePerformanceMetrics($teacher, $period);
        $performance->setPerformanceMetrics($metrics);

        // 计算绩效分数
        $performanceScore = $this->calculatePerformanceScore($metrics, $averageEvaluation);
        $performance->setPerformanceScore($performanceScore);

        // 确定绩效等级
        $performanceLevel = $this->determinePerformanceLevel($performanceScore);
        $performance->setPerformanceLevel($performanceLevel);

        // 计算成就
        $achievements = $this->calculateAchievements($teacher, $metrics);
        $performance->setAchievements($achievements);

        if ($existingPerformance === null) {
            $this->entityManager->persist($performance);
        }
        $this->entityManager->flush();

        return $performance;
    }

    /**
     * 更新绩效指标
     */
    public function updatePerformanceMetrics(string $performanceId, array $metrics): TeacherPerformance
    {
        $performance = $this->performanceRepository->find($performanceId);
        if (!$performance) {
            throw new PerformanceNotFoundException('绩效记录不存在: ' . $performanceId);
        }

        $performance->setPerformanceMetrics($metrics);
        
        // 重新计算绩效分数和等级
        $performanceScore = $this->calculatePerformanceScore($metrics, $performance->getAverageEvaluation());
        $performance->setPerformanceScore($performanceScore);
        
        $performanceLevel = $this->determinePerformanceLevel($performanceScore);
        $performance->setPerformanceLevel($performanceLevel);

        $this->entityManager->flush();

        return $performance;
    }

    /**
     * 获取教师绩效历史
     */
    public function getPerformanceHistory(string $teacherId): array
    {
        $teacher = $this->teacherService->getTeacherById($teacherId);
        return $this->performanceRepository->findByTeacher($teacher);
    }

    /**
     * 比较教师绩效
     */
    public function compareTeacherPerformance(array $teacherIds, \DateTimeInterface $period): array
    {
        return $this->performanceRepository->compareTeacherPerformance($teacherIds, $period);
    }

    /**
     * 生成绩效报告
     */
    public function generatePerformanceReport(string $teacherId): array
    {
        $teacher = $this->teacherService->getTeacherById($teacherId);
        $performances = $this->performanceRepository->findByTeacher($teacher);
        
        if ((bool) empty($performances)) {
            return [
                'teacher' => [
                    'id' => $teacher->getId(),
                    'name' => $teacher->getTeacherName(),
                    'code' => $teacher->getTeacherCode(),
                ],
                'message' => '暂无绩效数据',
                'reportGeneratedAt' => new \DateTime(),
            ];
        }

        // 获取最新绩效
        $latestPerformance = $performances[0];
        
        // 计算绩效趋势
        $trend = $this->performanceRepository->getPerformanceTrend($teacher, 12);
        
        // 分析绩效变化
        $analysis = $this->analyzePerformanceChange($performances);
        
        return [
            'teacher' => [
                'id' => $teacher->getId(),
                'name' => $teacher->getTeacherName(),
                'code' => $teacher->getTeacherCode(),
                'type' => $teacher->getTeacherType(),
            ],
            'latestPerformance' => [
                'period' => $latestPerformance->getPerformancePeriod()->format('Y-m'),
                'score' => $latestPerformance->getPerformanceScore(),
                'level' => $latestPerformance->getPerformanceLevel(),
                'averageEvaluation' => $latestPerformance->getAverageEvaluation(),
                'metrics' => $latestPerformance->getPerformanceMetrics(),
                'achievements' => $latestPerformance->getAchievements(),
            ],
            'trend' => $trend,
            'analysis' => $analysis,
            'performanceCount' => count($performances),
            'reportGeneratedAt' => new \DateTime(),
        ];
    }

    /**
     * 获取绩效排名
     */
    public function getPerformanceRanking(int $limit = 20): array
    {
        return $this->performanceRepository->getPerformanceRanking($limit);
    }

    /**
     * 获取指定周期的绩效排名
     */
    public function getPerformanceRankingByPeriod(\DateTimeInterface $period, int $limit = 20): array
    {
        return $this->performanceRepository->getPerformanceRankingByPeriod($period, $limit);
    }

    /**
     * 获取绩效统计信息
     */
    public function getPerformanceStatistics(): array
    {
        return $this->performanceRepository->getPerformanceStatistics();
    }

    /**
     * 计算绩效指标
     */
    private function calculatePerformanceMetrics(Teacher $teacher, \DateTimeInterface $period): array
    {
        // 这里可以根据具体业务需求计算各种绩效指标
        // 例如：授课时长、学员满意度、课程完成率等
        
        $metrics = [
            'teachingHours' => $this->calculateTeachingHours($teacher, $period),
            'studentSatisfaction' => $this->calculateStudentSatisfaction($teacher, $period),
            'courseCompletionRate' => $this->calculateCourseCompletionRate($teacher, $period),
            'attendanceRate' => $this->calculateAttendanceRate($teacher, $period),
            'innovationScore' => $this->calculateInnovationScore($teacher, $period),
        ];

        return $metrics;
    }

    /**
     * 计算绩效分数
     */
    private function calculatePerformanceScore(array $metrics, float $averageEvaluation): float
    {
        // 权重配置
        $weights = [
            'evaluation' => 0.3,      // 评价分数权重30%
            'teachingHours' => 0.2,   // 授课时长权重20%
            'satisfaction' => 0.2,    // 学员满意度权重20%
            'completion' => 0.15,     // 课程完成率权重15%
            'attendance' => 0.1,      // 出勤率权重10%
            'innovation' => 0.05,     // 创新分数权重5%
        ];

        $score = 0;
        
        // 评价分数（转换为百分制）
        $score += ($averageEvaluation * 20) * $weights['evaluation'];
        
        // 其他指标
        $score += ($metrics['teachingHours'] ?? 0) * $weights['teachingHours'];
        $score += ($metrics['studentSatisfaction'] ?? 0) * $weights['satisfaction'];
        $score += ($metrics['courseCompletionRate'] ?? 0) * $weights['completion'];
        $score += ($metrics['attendanceRate'] ?? 0) * $weights['attendance'];
        $score += ($metrics['innovationScore'] ?? 0) * $weights['innovation'];

        return round($score, 2);
    }

    /**
     * 确定绩效等级
     */
    private function determinePerformanceLevel(float $score): string
    {
        if ($score >= 90) {
            return '优秀';
        } elseif ($score >= 80) {
            return '良好';
        } elseif ($score >= 70) {
            return '一般';
        } elseif ($score >= 60) {
            return '合格';
        } else {
            return '较差';
        }
    }

    /**
     * 计算成就
     */
    private function calculateAchievements(Teacher $teacher, array $metrics): array
    {
        $achievements = [];

        // 根据指标判断成就
        if ((bool) ($metrics['teachingHours'] ?? 0) >= 80) {
            $achievements[] = '授课达人';
        }
        if ((bool) ($metrics['studentSatisfaction'] ?? 0) >= 95) {
            $achievements[] = '学员最爱';
        }
        if ((bool) ($metrics['courseCompletionRate'] ?? 0) >= 95) {
            $achievements[] = '完课之星';
        }
        if ((bool) ($metrics['attendanceRate'] ?? 0) >= 98) {
            $achievements[] = '全勤教师';
        }
        if ((bool) ($metrics['innovationScore'] ?? 0) >= 90) {
            $achievements[] = '创新先锋';
        }

        return $achievements;
    }

    /**
     * 分析绩效变化
     */
    private function analyzePerformanceChange(array $performances): array
    {
        if ((bool) count($performances) < 2) {
            return ['message' => '数据不足，无法分析趋势'];
        }

        $latest = $performances[0];
        $previous = $performances[1];

        if ($latest === null || $previous === null) {
            return ['message' => '绩效数据异常'];
        }

        $scoreChange = $latest->getPerformanceScore() - $previous->getPerformanceScore();
        $evaluationChange = $latest->getAverageEvaluation() - $previous->getAverageEvaluation();

        $analysis = [
            'scoreChange' => round($scoreChange, 2),
            'evaluationChange' => round($evaluationChange, 1),
            'trend' => $scoreChange > 0 ? '上升' : ($scoreChange < 0 ? '下降' : '持平'),
        ];

        if ($scoreChange > 5) {
            $analysis['message'] = '绩效显著提升';
        } elseif ($scoreChange > 0) {
            $analysis['message'] = '绩效稳步提升';
        } elseif ($scoreChange < -5) {
            $analysis['message'] = '绩效明显下降';
        } elseif ($scoreChange < 0) {
            $analysis['message'] = '绩效略有下降';
        } else {
            $analysis['message'] = '绩效保持稳定';
        }

        return $analysis;
    }

    /**
     * 计算授课时长（示例实现）
     */
    private function calculateTeachingHours(Teacher $teacher, \DateTimeInterface $period): float
    {
        // 这里应该从课程系统获取实际数据
        // 暂时返回模拟数据
        return 80.0;
    }

    /**
     * 计算学员满意度（示例实现）
     */
    private function calculateStudentSatisfaction(Teacher $teacher, \DateTimeInterface $period): float
    {
        // 这里应该从评价系统获取实际数据
        return 85.0;
    }

    /**
     * 计算课程完成率（示例实现）
     */
    private function calculateCourseCompletionRate(Teacher $teacher, \DateTimeInterface $period): float
    {
        // 这里应该从课程系统获取实际数据
        return 90.0;
    }

    /**
     * 计算出勤率（示例实现）
     */
    private function calculateAttendanceRate(Teacher $teacher, \DateTimeInterface $period): float
    {
        // 这里应该从考勤系统获取实际数据
        return 95.0;
    }

    /**
     * 计算创新分数（示例实现）
     */
    private function calculateInnovationScore(Teacher $teacher, \DateTimeInterface $period): float
    {
        // 这里应该根据创新活动、论文发表等计算
        return 75.0;
    }

    /**
     * 生成绩效ID
     */
    private function generatePerformanceId(): string
    {
        return uniqid('perf_', true);
    }
} 