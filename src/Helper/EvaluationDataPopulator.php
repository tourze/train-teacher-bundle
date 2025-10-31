<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Helper;

use Tourze\TrainTeacherBundle\Entity\TeacherEvaluation;

/**
 * 评价数据填充器
 * 负责将原始数据填充到TeacherEvaluation实体
 */
class EvaluationDataPopulator
{
    /**
     * 填充评价数据
     * @param array<string, mixed> $data
     */
    public function populate(TeacherEvaluation $evaluation, array $data): void
    {
        $this->setBasicInfo($evaluation, $data);
        $this->setContent($evaluation, $data);
        $this->setDefaults($evaluation, $data);
    }

    /**
     * 设置基本信息
     * @param array<string, mixed> $data
     */
    private function setBasicInfo(TeacherEvaluation $evaluation, array $data): void
    {
        if (isset($data['evaluatorType']) && \is_string($data['evaluatorType'])) {
            $evaluation->setEvaluatorType($data['evaluatorType']);
        }
        if (isset($data['evaluationType']) && \is_string($data['evaluationType'])) {
            $evaluation->setEvaluationType($data['evaluationType']);
        }
        if (isset($data['evaluationDate']) && $data['evaluationDate'] instanceof \DateTimeInterface) {
            $evaluation->setEvaluationDate($data['evaluationDate']);
        }
    }

    /**
     * 设置评价内容
     * @param array<string, mixed> $data
     */
    private function setContent(TeacherEvaluation $evaluation, array $data): void
    {
        $this->setItems($evaluation, $data);
        $this->setScores($evaluation, $data);
        $this->setComments($evaluation, $data);
        $this->setSuggestions($evaluation, $data);
    }

    /**
     * 设置评价项目
     * @param array<string, mixed> $data
     */
    private function setItems(TeacherEvaluation $evaluation, array $data): void
    {
        if (!isset($data['evaluationItems']) || !\is_array($data['evaluationItems'])) {
            return;
        }

        /** @var array<string, string> $items */
        $items = [];
        foreach ($data['evaluationItems'] as $key => $value) {
            if (is_string($key) && is_string($value)) {
                $items[$key] = $value;
            }
        }
        $evaluation->setEvaluationItems($items);
    }

    /**
     * 设置评价分数
     * @param array<string, mixed> $data
     */
    private function setScores(TeacherEvaluation $evaluation, array $data): void
    {
        if (!isset($data['evaluationScores']) || !\is_array($data['evaluationScores'])) {
            return;
        }

        /** @var array<string, float> $scores */
        $scores = [];
        foreach ($data['evaluationScores'] as $key => $value) {
            if (is_string($key) && is_numeric($value)) {
                $scores[$key] = (float) $value;
            }
        }
        $evaluation->setEvaluationScores($scores);
    }

    /**
     * 设置评价评论
     * @param array<string, mixed> $data
     */
    private function setComments(TeacherEvaluation $evaluation, array $data): void
    {
        if (!array_key_exists('evaluationComments', $data)) {
            return;
        }

        $comments = $data['evaluationComments'];
        if (\is_string($comments) || null === $comments) {
            $evaluation->setEvaluationComments($comments);
        }
    }

    /**
     * 设置评价建议
     * @param array<string, mixed> $data
     */
    private function setSuggestions(TeacherEvaluation $evaluation, array $data): void
    {
        if (!isset($data['suggestions']) || !\is_array($data['suggestions'])) {
            return;
        }

        /** @var array<string> $suggestions */
        $suggestions = [];
        foreach ($data['suggestions'] as $key => $value) {
            if (is_string($value)) {
                $suggestions[$key] = $value;
            }
        }
        $evaluation->setSuggestions($suggestions);
    }

    /**
     * 设置默认值
     * @param array<string, mixed> $data
     */
    private function setDefaults(TeacherEvaluation $evaluation, array $data): void
    {
        if (!isset($data['evaluationDate'])) {
            $evaluation->setEvaluationDate(new \DateTimeImmutable());
        }

        if (isset($data['isAnonymous']) && \is_bool($data['isAnonymous'])) {
            $evaluation->setIsAnonymous($data['isAnonymous']);
        }

        if (isset($data['evaluationStatus']) && \is_string($data['evaluationStatus'])) {
            $evaluation->setEvaluationStatus($data['evaluationStatus']);
        } else {
            $evaluation->setEvaluationStatus('已提交');
        }
    }
}
