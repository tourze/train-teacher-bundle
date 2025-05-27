<?php

namespace Tourze\TrainTeacherBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * 教师绩效实体
 * 管理教师的绩效评估、指标统计和等级评定
 */
#[ORM\Entity(repositoryClass: 'Tourze\TrainTeacherBundle\Repository\TeacherPerformanceRepository')]
#[ORM\Table(name: 'train_teacher_performance')]
#[ORM\Index(columns: ['teacher_id'], name: 'idx_performance_teacher_id')]
#[ORM\Index(columns: ['performance_period'], name: 'idx_performance_period')]
#[ORM\Index(columns: ['performance_level'], name: 'idx_performance_level')]
class TeacherPerformance
{
    /**
     * 绩效ID
     */
    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 36)]
    private string $id;

    /**
     * 教师
     */
    #[ORM\ManyToOne(targetEntity: Teacher::class)]
    #[ORM\JoinColumn(name: 'teacher_id', referencedColumnName: 'id', nullable: false)]
    private Teacher $teacher;

    /**
     * 绩效周期
     */
    #[ORM\Column(name: 'performance_period', type: Types::DATE_MUTABLE)]
    private \DateTimeInterface $performancePeriod;

    /**
     * 平均评价分数
     */
    #[ORM\Column(name: 'average_evaluation', type: Types::DECIMAL, precision: 3, scale: 1)]
    private float $averageEvaluation;

    /**
     * 绩效指标
     */
    #[ORM\Column(name: 'performance_metrics', type: Types::JSON)]
    private array $performanceMetrics = [];

    /**
     * 绩效分数
     */
    #[ORM\Column(name: 'performance_score', type: Types::DECIMAL, precision: 5, scale: 2)]
    private float $performanceScore;

    /**
     * 绩效等级
     */
    #[ORM\Column(name: 'performance_level', type: Types::STRING, length: 20)]
    private string $performanceLevel;

    /**
     * 成就
     */
    #[ORM\Column(type: Types::JSON)]
    private array $achievements = [];

    /**
     * 创建时间
     */
    #[ORM\Column(name: 'create_time', type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $createTime;

    public function __construct()
    {
        $this->createTime = new \DateTime();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getTeacher(): Teacher
    {
        return $this->teacher;
    }

    public function setTeacher(Teacher $teacher): self
    {
        $this->teacher = $teacher;
        return $this;
    }

    public function getPerformancePeriod(): \DateTimeInterface
    {
        return $this->performancePeriod;
    }

    public function setPerformancePeriod(\DateTimeInterface $performancePeriod): self
    {
        $this->performancePeriod = $performancePeriod;
        return $this;
    }

    public function getAverageEvaluation(): float
    {
        return $this->averageEvaluation;
    }

    public function setAverageEvaluation(float $averageEvaluation): self
    {
        $this->averageEvaluation = $averageEvaluation;
        return $this;
    }

    public function getPerformanceMetrics(): array
    {
        return $this->performanceMetrics;
    }

    public function setPerformanceMetrics(array $performanceMetrics): self
    {
        $this->performanceMetrics = $performanceMetrics;
        return $this;
    }

    public function getPerformanceScore(): float
    {
        return $this->performanceScore;
    }

    public function setPerformanceScore(float $performanceScore): self
    {
        $this->performanceScore = $performanceScore;
        return $this;
    }

    public function getPerformanceLevel(): string
    {
        return $this->performanceLevel;
    }

    public function setPerformanceLevel(string $performanceLevel): self
    {
        $this->performanceLevel = $performanceLevel;
        return $this;
    }

    public function getAchievements(): array
    {
        return $this->achievements;
    }

    public function setAchievements(array $achievements): self
    {
        $this->achievements = $achievements;
        return $this;
    }

    public function getCreateTime(): \DateTimeInterface
    {
        return $this->createTime;
    }

    public function setCreateTime(\DateTimeInterface $createTime): self
    {
        $this->createTime = $createTime;
        return $this;
    }
} 