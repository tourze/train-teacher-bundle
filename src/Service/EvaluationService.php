<?php

namespace Tourze\TrainTeacherBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Tourze\TrainTeacherBundle\Entity\TeacherEvaluation;
use Tourze\TrainTeacherBundle\Exception\DuplicateEvaluationException;
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
        private readonly TeacherService $teacherService
    ) {
    }

    /**
     * 提交教师评价
     */
    public function submitEvaluation(string $teacherId, string $evaluatorId, array $evaluationData): TeacherEvaluation
    {
        $teacher = $this->teacherService->getTeacherById($teacherId);
        
        // 检查是否已经评价过
        if ((bool) isset($evaluationData['evaluationType']) && 
            $this->evaluationRepository->hasEvaluated($teacher, $evaluatorId, $evaluationData['evaluationType'])) {
            throw new DuplicateEvaluationException('您已经对该教师进行过此类型的评价');
        }

        $evaluation = new TeacherEvaluation();
        $evaluation->setId($this->generateEvaluationId());
        $evaluation->setTeacher($teacher);
        $evaluation->setEvaluatorId($evaluatorId);
        
        $this->populateEvaluationData($evaluation, $evaluationData);
        
        // 计算总体评分
        $overallScore = $this->calculateOverallScore($evaluationData['evaluationScores'] ?? []);
        $evaluation->setOverallScore($overallScore);

        $this->entityManager->persist($evaluation);
        $this->entityManager->flush();

        return $evaluation;
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
     */
    public function generateEvaluationReport(string $teacherId): array
    {
        $teacher = $this->teacherService->getTeacherById($teacherId);
        $evaluations = $this->evaluationRepository->findByTeacher($teacher);
        $statistics = $this->getEvaluationStatistics($teacherId);
        
        // 分析评价趋势
        $trend = $this->analyzeEvaluationTrend($evaluations);
        
        // 分析强项和弱项
        $strengths = $this->analyzeStrengths($evaluations);
        $weaknesses = $this->analyzeWeaknesses($evaluations);
        
        // 收集建议
        $suggestions = $this->collectSuggestions($evaluations);
        
        return [
            'teacher' => [
                'id' => $teacher->getId(),
                'name' => $teacher->getTeacherName(),
                'code' => $teacher->getTeacherCode(),
                'type' => $teacher->getTeacherType(),
            ],
            'statistics' => $statistics,
            'trend' => $trend,
            'strengths' => $strengths,
            'weaknesses' => $weaknesses,
            'suggestions' => $suggestions,
            'evaluationCount' => count($evaluations),
            'reportGeneratedAt' => new \DateTime(),
        ];
    }

    /**
     * 获取最高评分的教师列表
     */
    public function getTopRatedTeachers(int $limit = 10): array
    {
        return $this->evaluationRepository->getTopRatedTeachers($limit);
    }

    /**
     * 获取教师评价列表
     */
    public function getTeacherEvaluations(string $teacherId): array
    {
        $teacher = $this->teacherService->getTeacherById($teacherId);
        return $this->evaluationRepository->findByTeacher($teacher);
    }

    /**
     * 根据评价者类型获取评价列表
     */
    public function getEvaluationsByType(string $evaluatorType): array
    {
        return $this->evaluationRepository->findByEvaluatorType($evaluatorType);
    }

    /**
     * 获取指定时间范围内的评价
     */
    public function getEvaluationsByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->evaluationRepository->findByDateRange($startDate, $endDate);
    }

    /**
     * 填充评价数据
     */
    private function populateEvaluationData(TeacherEvaluation $evaluation, array $data): void
    {
        if ((bool) isset($data['evaluatorType'])) {
            $evaluation->setEvaluatorType($data['evaluatorType']);
        }
        if ((bool) isset($data['evaluationType'])) {
            $evaluation->setEvaluationType($data['evaluationType']);
        }
        if ((bool) isset($data['evaluationDate'])) {
            $evaluation->setEvaluationDate($data['evaluationDate']);
        } else {
            $evaluation->setEvaluationDate(new \DateTimeImmutable());
        }
        if ((bool) isset($data['evaluationItems'])) {
            $evaluation->setEvaluationItems($data['evaluationItems']);
        }
        if ((bool) isset($data['evaluationScores'])) {
            $evaluation->setEvaluationScores($data['evaluationScores']);
        }
        if ((bool) isset($data['evaluationComments'])) {
            $evaluation->setEvaluationComments($data['evaluationComments']);
        }
        if ((bool) isset($data['suggestions'])) {
            $evaluation->setSuggestions($data['suggestions']);
        }
        if ((bool) isset($data['isAnonymous'])) {
            $evaluation->setIsAnonymous($data['isAnonymous']);
        }
        if ((bool) isset($data['evaluationStatus'])) {
            $evaluation->setEvaluationStatus($data['evaluationStatus']);
        } else {
            $evaluation->setEvaluationStatus('已提交');
        }
    }

    /**
     * 计算总体评分
     */
    private function calculateOverallScore(array $scores): float
    {
        if ((bool) empty($scores)) {
            return 0.0;
        }

        $total = 0;
        $count = 0;
        
        foreach ($scores as $score) {
            if ((bool) is_numeric($score)) {
                $total += (float) $score;
                $count++;
            }
        }
        
        return $count > 0 ? round($total / $count, 1) : 0.0;
    }

    /**
     * 分析评价趋势
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
     */
    private function analyzeStrengths(array $evaluations): array
    {
        $itemScores = [];
        
        foreach ($evaluations as $evaluation) {
            $scores = $evaluation->getEvaluationScores();
            foreach ($scores as $item => $score) {
                if (!isset($itemScores[$item])) {
                    $itemScores[$item] = [];
                }
                $itemScores[$item][] = (float) $score;
            }
        }
        
        $strengths = [];
        foreach ($itemScores as $item => $scores) {
            $average = array_sum($scores) / count($scores);
            if ($average >= 4.0) { // 假设5分制，4分以上为强项
                $strengths[] = [
                    'item' => $item,
                    'averageScore' => round($average, 1),
                    'evaluationCount' => count($scores),
                ];
            }
        }
        
        // 按平均分排序
        usort($strengths, fn($a, $b) => $b['averageScore'] <=> $a['averageScore']);
        
        return array_slice($strengths, 0, 5); // 返回前5个强项
    }

    /**
     * 分析弱项
     */
    private function analyzeWeaknesses(array $evaluations): array
    {
        $itemScores = [];
        
        foreach ($evaluations as $evaluation) {
            $scores = $evaluation->getEvaluationScores();
            foreach ($scores as $item => $score) {
                if (!isset($itemScores[$item])) {
                    $itemScores[$item] = [];
                }
                $itemScores[$item][] = (float) $score;
            }
        }
        
        $weaknesses = [];
        foreach ($itemScores as $item => $scores) {
            $average = array_sum($scores) / count($scores);
            if ($average < 3.0) { // 假设5分制，3分以下为弱项
                $weaknesses[] = [
                    'item' => $item,
                    'averageScore' => round($average, 1),
                    'evaluationCount' => count($scores),
                ];
            }
        }
        
        // 按平均分排序（从低到高）
        usort($weaknesses, fn($a, $b) => $a['averageScore'] <=> $b['averageScore']);
        
        return array_slice($weaknesses, 0, 5); // 返回前5个弱项
    }

    /**
     * 收集建议
     */
    private function collectSuggestions(array $evaluations): array
    {
        $allSuggestions = [];
        
        foreach ($evaluations as $evaluation) {
            $suggestions = $evaluation->getSuggestions();
            foreach ($suggestions as $suggestion) {
                if (!empty($suggestion)) {
                    $allSuggestions[] = $suggestion;
                }
            }
        }
        
        // 去重并返回
        return array_unique($allSuggestions);
    }

    /**
     * 生成评价ID
     */
    private function generateEvaluationId(): string
    {
        return uniqid('eval_', true);
    }
} 