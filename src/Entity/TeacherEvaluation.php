<?php

namespace Tourze\TrainTeacherBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\TrainTeacherBundle\Repository\TeacherEvaluationRepository;

/**
 * 教师评价实体
 * 管理学员、同行、管理层对教师的评价信息
 */
#[ORM\Entity(repositoryClass: TeacherEvaluationRepository::class)]
#[ORM\Table(name: 'train_teacher_evaluation', options: ['comment' => '教师评价表'])]
#[ORM\Index(columns: ['teacher_id', 'evaluation_date'], name: 'train_teacher_evaluation_IDX_teacher_evaluation_date')]
class TeacherEvaluation implements \Stringable
{
    #[ORM\Id]
    #[ORM\CustomIdGenerator]
    #[ORM\Column(type: Types::STRING, length: 36, options: ['comment' => '评价ID'])]
    #[Assert\Length(max: 36)]
    private string $id;

    /**
     * 教师
     */
    #[ORM\ManyToOne(targetEntity: Teacher::class)]
    #[ORM\JoinColumn(name: 'teacher_id', referencedColumnName: 'id', nullable: false)]
    #[Assert\NotNull]
    private Teacher $teacher;

    #[ORM\Column(name: 'evaluator_type', type: Types::STRING, length: 20, options: ['comment' => '评价者类型（学员、同行、管理层、自我）'])]
    #[IndexColumn]
    #[Assert\NotBlank]
    #[Assert\Length(max: 20)]
    private string $evaluatorType;

    #[ORM\Column(name: 'evaluator_id', type: Types::STRING, length: 36, options: ['comment' => '评价者ID'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 36)]
    private string $evaluatorId;

    #[ORM\Column(name: 'evaluation_type', type: Types::STRING, length: 50, options: ['comment' => '评价类型'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    private string $evaluationType;

    #[ORM\Column(name: 'evaluation_date', type: Types::DATE_IMMUTABLE, options: ['comment' => '评价日期'])]
    #[IndexColumn]
    #[Assert\NotNull]
    private \DateTimeInterface $evaluationDate;

    /**
     * @var array<string, string>
     */
    #[ORM\Column(name: 'evaluation_items', type: Types::JSON, options: ['comment' => '评价项目'])]
    #[Assert\Type(type: 'array')]
    private array $evaluationItems = [];

    /**
     * @var array<string, float>
     */
    #[ORM\Column(name: 'evaluation_scores', type: Types::JSON, options: ['comment' => '评价分数'])]
    #[Assert\Type(type: 'array')]
    private array $evaluationScores = [];

    #[ORM\Column(name: 'overall_score', type: Types::DECIMAL, precision: 3, scale: 1, options: ['comment' => '总体评分'])]
    #[Assert\NotNull]
    #[Assert\Range(min: 0, max: 10)]
    private float $overallScore;

    #[ORM\Column(name: 'evaluation_comments', type: Types::TEXT, nullable: true, options: ['comment' => '评价意见'])]
    #[Assert\Length(max: 2000)]
    private ?string $evaluationComments = null;

    /**
     * @var array<string>
     */
    #[ORM\Column(type: Types::JSON, options: ['comment' => '建议'])]
    #[Assert\Type(type: 'array')]
    private array $suggestions = [];

    #[ORM\Column(name: 'is_anonymous', type: Types::BOOLEAN, options: ['comment' => '是否匿名'])]
    #[Assert\Type(type: 'bool')]
    private bool $isAnonymous = false;

    #[ORM\Column(name: 'evaluation_status', type: Types::STRING, length: 20, options: ['comment' => '评价状态'])]
    #[IndexColumn]
    #[Assert\NotBlank]
    #[Assert\Length(max: 20)]
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

    public function getEvaluatorType(): string
    {
        return $this->evaluatorType;
    }

    public function setEvaluatorType(string $evaluatorType): void
    {
        $this->evaluatorType = $evaluatorType;
    }

    public function getEvaluatorId(): string
    {
        return $this->evaluatorId;
    }

    public function setEvaluatorId(string $evaluatorId): void
    {
        $this->evaluatorId = $evaluatorId;
    }

    public function getEvaluationType(): string
    {
        return $this->evaluationType;
    }

    public function setEvaluationType(string $evaluationType): void
    {
        $this->evaluationType = $evaluationType;
    }

    public function getEvaluationDate(): \DateTimeInterface
    {
        return $this->evaluationDate;
    }

    public function setEvaluationDate(\DateTimeInterface $evaluationDate): void
    {
        $this->evaluationDate = $evaluationDate;
    }

    /**
     * @return array<string, string>
     */
    public function getEvaluationItems(): array
    {
        return $this->evaluationItems;
    }

    /**
     * @param array<string, string> $evaluationItems
     */
    public function setEvaluationItems(array $evaluationItems): void
    {
        $this->evaluationItems = $evaluationItems;
    }

    /**
     * @return array<string, float>
     */
    public function getEvaluationScores(): array
    {
        return $this->evaluationScores;
    }

    /**
     * @param array<string, float> $evaluationScores
     */
    public function setEvaluationScores(array $evaluationScores): void
    {
        $this->evaluationScores = $evaluationScores;
    }

    public function getOverallScore(): float
    {
        return $this->overallScore;
    }

    public function setOverallScore(float $overallScore): void
    {
        $this->overallScore = $overallScore;
    }

    public function getEvaluationComments(): ?string
    {
        return $this->evaluationComments;
    }

    public function setEvaluationComments(?string $evaluationComments): void
    {
        $this->evaluationComments = $evaluationComments;
    }

    /**
     * @return array<string>
     */
    public function getSuggestions(): array
    {
        return $this->suggestions;
    }

    /**
     * @param array<string> $suggestions
     */
    public function setSuggestions(array $suggestions): void
    {
        $this->suggestions = $suggestions;
    }

    public function isAnonymous(): bool
    {
        return $this->isAnonymous;
    }

    public function getIsAnonymous(): bool
    {
        return $this->isAnonymous;
    }

    public function setIsAnonymous(bool $isAnonymous): void
    {
        $this->isAnonymous = $isAnonymous;
    }

    public function getEvaluationStatus(): string
    {
        return $this->evaluationStatus;
    }

    public function setEvaluationStatus(string $evaluationStatus): void
    {
        $this->evaluationStatus = $evaluationStatus;
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
