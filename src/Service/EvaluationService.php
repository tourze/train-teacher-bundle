<?php

namespace Tourze\TrainTeacherBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Tourze\TrainTeacherBundle\Entity\Teacher;
use Tourze\TrainTeacherBundle\Entity\TeacherEvaluation;
use Tourze\TrainTeacherBundle\Exception\DuplicateEvaluationException;
use Tourze\TrainTeacherBundle\Helper\EvaluationDataPopulator;
use Tourze\TrainTeacherBundle\Repository\TeacherEvaluationRepository;

/**
 * 教师评价服务
 * 提供教师评价的提交、统计、分析等核心业务功能
 */
class EvaluationService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TeacherEvaluationRepository $evaluationRepository,
        private readonly TeacherService $teacherService,
        private readonly EvaluationDataPopulator $dataPopulator,
    ) {
    }

    /**
     * 提交教师评价
     * @param array<string, mixed> $evaluationData
     */
    public function submitEvaluation(string $teacherId, string $evaluatorId, array $evaluationData): TeacherEvaluation
    {
        $teacher = $this->teacherService->getTeacherById($teacherId);
        $this->validateDuplicateEvaluation($teacher, $evaluatorId, $evaluationData);

        $evaluation = $this->createEvaluationEntity($teacher, $evaluatorId, $evaluationData);
        $this->persistEvaluation($evaluation);

        return $evaluation;
    }

    /**
     * 验证重复评价
     * @param array<string, mixed> $evaluationData
     */
    private function validateDuplicateEvaluation(Teacher $teacher, string $evaluatorId, array $evaluationData): void
    {
        if (!isset($evaluationData['evaluationType']) || !\is_string($evaluationData['evaluationType'])) {
            return;
        }

        if ($this->evaluationRepository->hasEvaluated($teacher, $evaluatorId, $evaluationData['evaluationType'])) {
            throw new DuplicateEvaluationException('您已经对该教师进行过此类型的评价');
        }
    }

    /**
     * 创建评价实体
     * @param array<string, mixed> $evaluationData
     */
    private function createEvaluationEntity(Teacher $teacher, string $evaluatorId, array $evaluationData): TeacherEvaluation
    {
        $evaluation = new TeacherEvaluation();
        $evaluation->setId($this->generateEvaluationId());
        $evaluation->setTeacher($teacher);
        $evaluation->setEvaluatorId($evaluatorId);

        $this->dataPopulator->populate($evaluation, $evaluationData);

        /** @var array<string, mixed> $evaluationScores */
        $evaluationScores = $evaluationData['evaluationScores'] ?? [];
        $overallScore = $this->calculateOverallScore($evaluationScores);
        $evaluation->setOverallScore($overallScore);

        return $evaluation;
    }

    /**
     * 持久化评价
     */
    private function persistEvaluation(TeacherEvaluation $evaluation): void
    {
        $this->entityManager->persist($evaluation);
        $this->entityManager->flush();
    }

    /**
     * 计算教师平均评价分数
     */
    public function calculateAverageEvaluation(string $teacherId): float
    {
        $teacher = $this->teacherService->getTeacherById($teacherId);

        return $this->evaluationRepository->getAverageScore($teacher);
    }

    /**
     * 获取教师评价统计信息
     * @return array<string, mixed>
     */
    public function getEvaluationStatistics(string $teacherId): array
    {
        $teacher = $this->teacherService->getTeacherById($teacherId);
        $statistics = $this->evaluationRepository->getEvaluationStatistics($teacher);

        // 添加各类型评价的平均分
        $statistics['studentAverage'] = $this->evaluationRepository->getAverageScoreByEvaluatorType($teacher, '学员');
        $statistics['peerAverage'] = $this->evaluationRepository->getAverageScoreByEvaluatorType($teacher, '同行');
        $statistics['managerAverage'] = $this->evaluationRepository->getAverageScoreByEvaluatorType($teacher, '管理层');
        $statistics['selfAverage'] = $this->evaluationRepository->getAverageScoreByEvaluatorType($teacher, '自我');

        return $statistics;
    }

    /**
     * 生成教师评价报告
     * @return array<string, mixed>
     */
    public function generateEvaluationReport(string $teacherId): array
    {
        $teacher = $this->teacherService->getTeacherById($teacherId);
        $evaluations = $this->evaluationRepository->findByTeacher($teacher);
        $statistics = $this->getEvaluationStatistics($teacherId);

        $analysisData = $this->generateEvaluationAnalysis($evaluations);
        $teacherInfo = $this->buildTeacherInfo($teacher);

        return array_merge([
            'teacher' => $teacherInfo,
            'statistics' => $statistics,
            'evaluationCount' => count($evaluations),
            'reportGeneratedAt' => new \DateTime(),
        ], $analysisData);
    }

    /**
     * 生成评价分析数据
     * @param array<int, TeacherEvaluation> $evaluations
     * @return array<string, mixed>
     */
    private function generateEvaluationAnalysis(array $evaluations): array
    {
        return [
            'trend' => $this->analyzeEvaluationTrend($evaluations),
            'strengths' => $this->analyzeStrengths($evaluations),
            'weaknesses' => $this->analyzeWeaknesses($evaluations),
            'suggestions' => $this->collectSuggestions($evaluations),
        ];
    }

    /**
     * 构建教师信息
     * @return array<string, mixed>
     */
    private function buildTeacherInfo(Teacher $teacher): array
    {
        return [
            'id' => $teacher->getId(),
            'name' => $teacher->getTeacherName(),
            'code' => $teacher->getTeacherCode(),
            'type' => $teacher->getTeacherType(),
        ];
    }

    /**
     * 获取最高评分的教师列表
     * @return array<int, mixed>
     */
    public function getTopRatedTeachers(int $limit = 10): array
    {
        return $this->evaluationRepository->getTopRatedTeachers($limit);
    }

    /**
     * 获取教师评价列表
     * @return array<int, TeacherEvaluation>
     */
    public function getTeacherEvaluations(string $teacherId): array
    {
        $teacher = $this->teacherService->getTeacherById($teacherId);

        return $this->evaluationRepository->findByTeacher($teacher);
    }

    /**
     * 根据评价者类型获取评价列表
     * @return array<int, TeacherEvaluation>
     */
    public function getEvaluationsByType(string $evaluatorType): array
    {
        return $this->evaluationRepository->findByEvaluatorType($evaluatorType);
    }

    /**
     * 获取指定时间范围内的评价
     * @return array<int, TeacherEvaluation>
     */
    public function getEvaluationsByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->evaluationRepository->findByDateRange($startDate, $endDate);
    }

    /**
     * 计算总体评分
     * @param array<string, mixed> $scores
     */
    private function calculateOverallScore(array $scores): float
    {
        if ([] === $scores) {
            return 0.0;
        }

        $total = 0;
        $count = 0;

        foreach ($scores as $score) {
            if (is_numeric($score)) {
                $total += (float) $score;
                ++$count;
            }
        }

        return 0 < $count ? round($total / $count, 1) : 0.0;
    }

    /**
     * 分析评价趋势
     * @param array<int, TeacherEvaluation> $evaluations
     * @return array<string, array<string, mixed>>
     */
    private function analyzeEvaluationTrend(array $evaluations): array
    {
        $monthlyScores = [];

        foreach ($evaluations as $evaluation) {
            $month = $evaluation->getEvaluationDate()->format('Y-m');
            if (!isset($monthlyScores[$month])) {
                $monthlyScores[$month] = [];
            }
            $monthlyScores[$month][] = $evaluation->getOverallScore();
        }

        $trend = [];
        foreach ($monthlyScores as $month => $scores) {
            $trend[$month] = [
                'averageScore' => round(array_sum($scores) / count($scores), 1),
                'evaluationCount' => count($scores),
            ];
        }

        ksort($trend);

        return $trend;
    }

    /**
     * 分析强项
     * @param array<int, TeacherEvaluation> $evaluations
     * @return array<int, array<string, mixed>>
     */
    private function analyzeStrengths(array $evaluations): array
    {
        $itemScores = $this->collectItemScores($evaluations);
        $strengths = [];

        foreach ($itemScores as $item => $scores) {
            $average = array_sum($scores) / count($scores);
            if ($average >= 4.0) {
                $strengths[] = [
                    'item' => $item,
                    'averageScore' => round($average, 1),
                    'evaluationCount' => count($scores),
                ];
            }
        }

        usort($strengths, static fn (array $a, array $b): int => $b['averageScore'] <=> $a['averageScore']);

        return array_slice($strengths, 0, 5);
    }

    /**
     * 分析弱项
     * @param array<int, TeacherEvaluation> $evaluations
     * @return array<int, array<string, mixed>>
     */
    private function analyzeWeaknesses(array $evaluations): array
    {
        $itemScores = $this->collectItemScores($evaluations);
        $weaknesses = [];

        foreach ($itemScores as $item => $scores) {
            $average = array_sum($scores) / count($scores);
            if ($average < 3.0) {
                $weaknesses[] = [
                    'item' => $item,
                    'averageScore' => round($average, 1),
                    'evaluationCount' => count($scores),
                ];
            }
        }

        usort($weaknesses, static fn (array $a, array $b): int => $a['averageScore'] <=> $b['averageScore']);

        return array_slice($weaknesses, 0, 5);
    }

    /**
     * 收集建议
     * @param array<int, TeacherEvaluation> $evaluations
     * @return array<int, string>
     */
    private function collectSuggestions(array $evaluations): array
    {
        $allSuggestions = [];

        foreach ($evaluations as $evaluation) {
            $suggestions = $evaluation->getSuggestions();
            foreach ($suggestions as $suggestion) {
                if ('' !== $suggestion) {
                    $allSuggestions[] = $suggestion;
                }
            }
        }

        // 去重并返回
        return array_unique($allSuggestions);
    }

    /**
     * 收集评价项目得分
     * @param array<int, TeacherEvaluation> $evaluations
     * @return array<string, array<int, float>>
     */
    private function collectItemScores(array $evaluations): array
    {
        $itemScores = [];

        foreach ($evaluations as $evaluation) {
            $scores = $evaluation->getEvaluationScores();
            foreach ($scores as $item => $score) {
                $itemKey = (string) $item;
                if (!isset($itemScores[$itemKey])) {
                    $itemScores[$itemKey] = [];
                }
                $itemScores[$itemKey][] = (float) $score;
            }
        }

        return $itemScores;
    }

    /**
     * 生成评价ID
     */
    private function generateEvaluationId(): string
    {
        return uniqid('eval_', true);
    }
}
