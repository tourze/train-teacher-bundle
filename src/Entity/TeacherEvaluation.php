<?php

namespace Tourze\TrainTeacherBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;

/**
 * 教师评价实体
 * 管理学员、同行、管理层对教师的评价信息
 */
#[ORM\Entity(repositoryClass: \Tourze\TrainTeacherBundle\Repository\TeacherEvaluationRepository::class)]
#[ORM\Table(name: 'train_teacher_evaluation', options: ['comment' => '教师评价表'])]
#[ORM\Index(columns: ['teacher_id'], name: 'idx_evaluation_teacher_id')]
#[ORM\Index(columns: ['evaluator_type'], name: 'idx_evaluator_type')]
#[ORM\Index(columns: ['evaluation_date'], name: 'idx_evaluation_date')]
#[ORM\Index(columns: ['evaluation_status'], name: 'idx_evaluation_status')]
class TeacherEvaluation implements Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 36, options: ['comment' => '评价ID'])]
    private string $id;

    /**
     * 教师
     */
    #[ORM\ManyToOne(targetEntity: Teacher::class)]
    #[ORM\JoinColumn(name: 'teacher_id', referencedColumnName: 'id', nullable: false)]
    private Teacher $teacher;

    #[ORM\Column(name: 'evaluator_type', type: Types::STRING, length: 20, options: ['comment' => '评价者类型（学员、同行、管理层、自我）'])]
    private string $evaluatorType;

    #[ORM\Column(name: 'evaluator_id', type: Types::STRING, length: 36, options: ['comment' => '评价者ID'])]
    private string $evaluatorId;

    #[ORM\Column(name: 'evaluation_type', type: Types::STRING, length: 50, options: ['comment' => '评价类型'])]
    private string $evaluationType;

    #[ORM\Column(name: 'evaluation_date', type: Types::DATE_MUTABLE, options: ['comment' => '评价日期'])]
    private \DateTimeInterface $evaluationDate;

    #[ORM\Column(name: 'evaluation_items', type: Types::JSON, options: ['comment' => '评价项目'])]
    private array $evaluationItems = [];

    #[ORM\Column(name: 'evaluation_scores', type: Types::JSON, options: ['comment' => '评价分数'])]
    private array $evaluationScores = [];

    #[ORM\Column(name: 'overall_score', type: Types::DECIMAL, precision: 3, scale: 1, options: ['comment' => '总体评分'])]
    private float $overallScore;

    #[ORM\Column(name: 'evaluation_comments', type: Types::TEXT, nullable: true, options: ['comment' => '评价意见'])]
    private ?string $evaluationComments = null;

    #[ORM\Column(type: Types::JSON, options: ['comment' => '建议'])]
    private array $suggestions = [];

    #[ORM\Column(name: 'is_anonymous', type: Types::BOOLEAN, options: ['comment' => '是否匿名'])]
    private bool $isAnonymous = false;

    #[ORM\Column(name: 'evaluation_status', type: Types::STRING, length: 20, options: ['comment' => '评价状态'])]
    private string $evaluationStatus;

    #[ORM\Column(name: 'create_time', type: Types::DATETIME_IMMUTABLE, options: ['comment' => '创建时间'])]
    private \DateTimeInterface $createTime;

    public function __construct()
    {
        $this->createTime = new \DateTimeImmutable();
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

    public function getEvaluatorType(): string
    {
        return $this->evaluatorType;
    }

    public function setEvaluatorType(string $evaluatorType): self
    {
        $this->evaluatorType = $evaluatorType;
        return $this;
    }

    public function getEvaluatorId(): string
    {
        return $this->evaluatorId;
    }

    public function setEvaluatorId(string $evaluatorId): self
    {
        $this->evaluatorId = $evaluatorId;
        return $this;
    }

    public function getEvaluationType(): string
    {
        return $this->evaluationType;
    }

    public function setEvaluationType(string $evaluationType): self
    {
        $this->evaluationType = $evaluationType;
        return $this;
    }

    public function getEvaluationDate(): \DateTimeInterface
    {
        return $this->evaluationDate;
    }

    public function setEvaluationDate(\DateTimeInterface $evaluationDate): self
    {
        $this->evaluationDate = $evaluationDate;
        return $this;
    }

    public function getEvaluationItems(): array
    {
        return $this->evaluationItems;
    }

    public function setEvaluationItems(array $evaluationItems): self
    {
        $this->evaluationItems = $evaluationItems;
        return $this;
    }

    public function getEvaluationScores(): array
    {
        return $this->evaluationScores;
    }

    public function setEvaluationScores(array $evaluationScores): self
    {
        $this->evaluationScores = $evaluationScores;
        return $this;
    }

    public function getOverallScore(): float
    {
        return $this->overallScore;
    }

    public function setOverallScore(float $overallScore): self
    {
        $this->overallScore = $overallScore;
        return $this;
    }

    public function getEvaluationComments(): ?string
    {
        return $this->evaluationComments;
    }

    public function setEvaluationComments(?string $evaluationComments): self
    {
        $this->evaluationComments = $evaluationComments;
        return $this;
    }

    public function getSuggestions(): array
    {
        return $this->suggestions;
    }

    public function setSuggestions(array $suggestions): self
    {
        $this->suggestions = $suggestions;
        return $this;
    }

    public function isAnonymous(): bool
    {
        return $this->isAnonymous;
    }

    public function setIsAnonymous(bool $isAnonymous): self
    {
        $this->isAnonymous = $isAnonymous;
        return $this;
    }

    public function getEvaluationStatus(): string
    {
        return $this->evaluationStatus;
    }

    public function setEvaluationStatus(string $evaluationStatus): self
    {
        $this->evaluationStatus = $evaluationStatus;
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

    public function __toString(): string
    {
        return (string) $this->id;
    }
} 