<?php

namespace Tourze\TrainTeacherBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\TrainTeacherBundle\Repository\TeacherPerformanceRepository;

/**
 * 教师绩效实体
 * 管理教师的绩效评估、指标统计和等级评定
 */
#[ORM\Entity(repositoryClass: TeacherPerformanceRepository::class)]
#[ORM\Table(name: 'train_teacher_performance', options: ['comment' => '教师绩效表'])]
#[ORM\Index(columns: ['teacher_id', 'performance_period'], name: 'train_teacher_performance_IDX_teacher_performance_period')]
class TeacherPerformance implements \Stringable
{
    #[ORM\Id]
    #[ORM\CustomIdGenerator]
    #[ORM\Column(type: Types::STRING, length: 36, options: ['comment' => '绩效ID'])]
    #[Assert\Length(max: 36)]
    private string $id;

    /**
     * 教师
     */
    #[ORM\ManyToOne(targetEntity: Teacher::class)]
    #[ORM\JoinColumn(name: 'teacher_id', referencedColumnName: 'id', nullable: false)]
    #[Assert\NotNull]
    private Teacher $teacher;

    #[ORM\Column(name: 'performance_period', type: Types::DATE_IMMUTABLE, options: ['comment' => '绩效周期'])]
    #[IndexColumn]
    #[Assert\NotNull]
    private \DateTimeInterface $performancePeriod;

    #[ORM\Column(name: 'average_evaluation', type: Types::DECIMAL, precision: 3, scale: 1, options: ['comment' => '平均评价分数'])]
    #[Assert\NotNull]
    #[Assert\Range(min: 0, max: 10)]
    private float $averageEvaluation;

    /**
     * @var array<string, mixed>
     */
    #[ORM\Column(name: 'performance_metrics', type: Types::JSON, options: ['comment' => '绩效指标'])]
    #[Assert\Type(type: 'array')]
    private array $performanceMetrics = [];

    #[ORM\Column(name: 'performance_score', type: Types::DECIMAL, precision: 5, scale: 2, options: ['comment' => '绩效分数'])]
    #[Assert\NotNull]
    #[Assert\Range(min: 0, max: 100)]
    private float $performanceScore;

    #[ORM\Column(name: 'performance_level', type: Types::STRING, length: 20, options: ['comment' => '绩效等级'])]
    #[IndexColumn]
    #[Assert\NotBlank]
    #[Assert\Length(max: 20)]
    private string $performanceLevel;

    #[ORM\Column(name: 'total_courses', type: Types::INTEGER, options: ['comment' => '总课程数', 'default' => 0])]
    #[Assert\Type(type: 'int')]
    #[Assert\PositiveOrZero]
    private int $totalCourses = 0;

    #[ORM\Column(name: 'total_hours', type: Types::INTEGER, options: ['comment' => '总课时数', 'default' => 0])]
    #[Assert\Type(type: 'int')]
    #[Assert\PositiveOrZero]
    private int $totalHours = 0;

    #[ORM\Column(name: 'student_count', type: Types::INTEGER, options: ['comment' => '学生总数', 'default' => 0])]
    #[Assert\Type(type: 'int')]
    #[Assert\PositiveOrZero]
    private int $studentCount = 0;

    #[ORM\Column(name: 'average_score', type: Types::DECIMAL, precision: 5, scale: 2, options: ['comment' => '平均分数', 'default' => 0])]
    #[Assert\Type(type: 'float')]
    #[Assert\Range(min: 0, max: 100)]
    private float $averageScore = 0.0;

    #[ORM\Column(name: 'completion_rate', type: Types::DECIMAL, precision: 5, scale: 2, options: ['comment' => '完成率', 'default' => 0])]
    #[Assert\Type(type: 'float')]
    #[Assert\Range(min: 0, max: 100)]
    private float $completionRate = 0.0;

    #[ORM\Column(name: 'satisfaction_rate', type: Types::DECIMAL, precision: 5, scale: 2, options: ['comment' => '满意度', 'default' => 0])]
    #[Assert\Type(type: 'float')]
    #[Assert\Range(min: 0, max: 100)]
    private float $satisfactionRate = 0.0;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注'])]
    #[Assert\Length(max: 1000)]
    private ?string $remarks = null;

    /**
     * @var array<string>
     */
    #[ORM\Column(type: Types::JSON, options: ['comment' => '成就'])]
    #[Assert\Type(type: 'array')]
    private array $achievements = [];

    #[ORM\Column(name: 'create_time', type: Types::DATETIME_IMMUTABLE, options: ['comment' => '创建时间'])]
    #[Assert\NotNull]
    private \DateTimeInterface $createTime;

    public function __construct()
    {
        $this->createTime = new \DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getTeacher(): Teacher
    {
        return $this->teacher;
    }

    public function setTeacher(Teacher $teacher): void
    {
        $this->teacher = $teacher;
    }

    public function getPerformancePeriod(): \DateTimeInterface
    {
        return $this->performancePeriod;
    }

    public function setPerformancePeriod(\DateTimeInterface $performancePeriod): void
    {
        $this->performancePeriod = $performancePeriod;
    }

    public function getAverageEvaluation(): float
    {
        return $this->averageEvaluation;
    }

    public function setAverageEvaluation(float $averageEvaluation): void
    {
        $this->averageEvaluation = $averageEvaluation;
    }

    /**
     * @return array<string, mixed>
     */
    public function getPerformanceMetrics(): array
    {
        return $this->performanceMetrics;
    }

    /**
     * @param array<string, mixed> $performanceMetrics
     */
    public function setPerformanceMetrics(array $performanceMetrics): void
    {
        $this->performanceMetrics = $performanceMetrics;
    }

    public function getPerformanceScore(): float
    {
        return $this->performanceScore;
    }

    public function setPerformanceScore(float $performanceScore): void
    {
        $this->performanceScore = $performanceScore;
    }

    public function getPerformanceLevel(): string
    {
        return $this->performanceLevel;
    }

    public function setPerformanceLevel(string $performanceLevel): void
    {
        $this->performanceLevel = $performanceLevel;
    }

    public function getTotalCourses(): int
    {
        return $this->totalCourses;
    }

    public function setTotalCourses(int $totalCourses): void
    {
        $this->totalCourses = $totalCourses;
    }

    public function getTotalHours(): int
    {
        return $this->totalHours;
    }

    public function setTotalHours(int $totalHours): void
    {
        $this->totalHours = $totalHours;
    }

    public function getStudentCount(): int
    {
        return $this->studentCount;
    }

    public function setStudentCount(int $studentCount): void
    {
        $this->studentCount = $studentCount;
    }

    public function getAverageScore(): float
    {
        return $this->averageScore;
    }

    public function setAverageScore(float $averageScore): void
    {
        $this->averageScore = $averageScore;
    }

    public function getCompletionRate(): float
    {
        return $this->completionRate;
    }

    public function setCompletionRate(float $completionRate): void
    {
        $this->completionRate = $completionRate;
    }

    public function getSatisfactionRate(): float
    {
        return $this->satisfactionRate;
    }

    public function setSatisfactionRate(float $satisfactionRate): void
    {
        $this->satisfactionRate = $satisfactionRate;
    }

    public function getRemarks(): ?string
    {
        return $this->remarks;
    }

    public function setRemarks(?string $remarks): void
    {
        $this->remarks = $remarks;
    }

    /**
     * @return array<string>
     */
    public function getAchievements(): array
    {
        return $this->achievements;
    }

    /**
     * @param array<string> $achievements
     */
    public function setAchievements(array $achievements): void
    {
        $this->achievements = $achievements;
    }

    public function getCreateTime(): \DateTimeInterface
    {
        return $this->createTime;
    }

    public function setCreateTime(\DateTimeInterface $createTime): void
    {
        $this->createTime = $createTime;
    }

    public function __toString(): string
    {
        return $this->id;
    }
}
