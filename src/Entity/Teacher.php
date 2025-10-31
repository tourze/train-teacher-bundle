<?php

namespace Tourze\TrainTeacherBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\TrainTeacherBundle\Repository\TeacherRepository;

/**
 * 教师实体
 * 管理教师的基本信息、联系方式、身份信息等
 */
#[ORM\Entity(repositoryClass: TeacherRepository::class)]
#[ORM\Table(name: 'train_teacher', options: ['comment' => '教师信息表'])]
class Teacher implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\CustomIdGenerator]
    #[ORM\Column(type: Types::STRING, length: 36, options: ['comment' => '教师ID'])]
    #[Assert\Length(max: 36)]
    private string $id;

    #[ORM\Column(name: 'teacher_code', type: Types::STRING, length: 32, unique: true, options: ['comment' => '教师编号'])]
    #[IndexColumn]
    #[Assert\NotBlank]
    #[Assert\Length(max: 32)]
    private string $teacherCode;

    #[ORM\Column(name: 'teacher_name', type: Types::STRING, length: 50, options: ['comment' => '教师姓名'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    private string $teacherName;

    #[ORM\Column(name: 'teacher_type', type: Types::STRING, length: 20, options: ['comment' => '教师类型（专职、兼职）'])]
    #[IndexColumn]
    #[Assert\NotBlank]
    #[Assert\Length(max: 20)]
    private string $teacherType;

    #[ORM\Column(type: Types::STRING, length: 10, options: ['comment' => '性别'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 10)]
    private string $gender;

    #[ORM\Column(name: 'birth_date', type: Types::DATE_IMMUTABLE, options: ['comment' => '出生日期'])]
    #[Assert\NotNull]
    private \DateTimeInterface $birthDate;

    #[ORM\Column(name: 'id_card', type: Types::STRING, length: 18, options: ['comment' => '身份证号'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 18)]
    private string $idCard;

    #[ORM\Column(type: Types::STRING, length: 20, options: ['comment' => '联系电话'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 20)]
    #[Assert\Regex(
        pattern: '/^(?:\+86\s?)?1[3-9]\d{9}$/',
        message: '请输入正确的手机号码格式，如：13812345678 或 +86 13812345678'
    )]
    private string $phone;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '邮箱'])]
    #[Assert\Email]
    #[Assert\Length(max: 100)]
    private ?string $email = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '地址'])]
    #[Assert\Length(max: 500)]
    private ?string $address = null;

    #[ORM\Column(type: Types::STRING, length: 20, options: ['comment' => '学历'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 20)]
    private string $education;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '专业'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private string $major;

    #[ORM\Column(name: 'graduate_school', type: Types::STRING, length: 100, options: ['comment' => '毕业院校'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private string $graduateSchool;

    #[ORM\Column(name: 'graduate_date', type: Types::DATE_IMMUTABLE, options: ['comment' => '毕业日期'])]
    #[Assert\NotNull]
    private \DateTimeInterface $graduateDate;

    #[ORM\Column(name: 'work_experience', type: Types::INTEGER, options: ['comment' => '工作经验（年）'])]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(value: 0)]
    private int $workExperience;

    /**
     * @var array<string>
     */
    #[ORM\Column(type: Types::JSON, options: ['comment' => '专业特长'])]
    #[Assert\Type(type: 'array')]
    private array $specialties = [];

    #[ORM\Column(name: 'teacher_status', type: Types::STRING, length: 20, options: ['comment' => '教师状态'])]
    #[IndexColumn]
    #[Assert\NotBlank]
    #[Assert\Length(max: 20)]
    private string $teacherStatus;

    #[ORM\Column(name: 'profile_photo', type: Types::STRING, length: 255, nullable: true, options: ['comment' => '头像'])]
    #[Assert\Length(max: 255)]
    #[Assert\Url]
    private ?string $profilePhoto = null;

    #[ORM\Column(name: 'join_date', type: Types::DATE_IMMUTABLE, options: ['comment' => '入职日期'])]
    #[Assert\NotNull]
    private \DateTimeInterface $joinDate;

    #[ORM\Column(name: 'last_active_time', type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '最后活跃时间'])]
    #[Assert\Type(type: '\DateTimeInterface')]
    private ?\DateTimeInterface $lastActiveTime = null;

    #[ORM\Column(name: 'is_anonymous', type: Types::BOOLEAN, options: ['comment' => '是否匿名', 'default' => false])]
    #[Assert\Type(type: 'bool')]
    private bool $isAnonymous = false;

    public function __construct()
    {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getTeacherCode(): string
    {
        return $this->teacherCode;
    }

    public function setTeacherCode(string $teacherCode): void
    {
        $this->teacherCode = $teacherCode;
    }

    public function getTeacherName(): string
    {
        return $this->teacherName;
    }

    public function setTeacherName(string $teacherName): void
    {
        $this->teacherName = $teacherName;
    }

    public function getTeacherType(): string
    {
        return $this->teacherType;
    }

    public function setTeacherType(string $teacherType): void
    {
        $this->teacherType = $teacherType;
    }

    public function getGender(): string
    {
        return $this->gender;
    }

    public function setGender(string $gender): void
    {
        $this->gender = $gender;
    }

    public function getBirthDate(): \DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTimeInterface $birthDate): void
    {
        $this->birthDate = $birthDate;
    }

    public function getIdCard(): string
    {
        return $this->idCard;
    }

    public function setIdCard(string $idCard): void
    {
        $this->idCard = $idCard;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    public function getEducation(): string
    {
        return $this->education;
    }

    public function setEducation(string $education): void
    {
        $this->education = $education;
    }

    public function getMajor(): string
    {
        return $this->major;
    }

    public function setMajor(string $major): void
    {
        $this->major = $major;
    }

    public function getGraduateSchool(): string
    {
        return $this->graduateSchool;
    }

    public function setGraduateSchool(string $graduateSchool): void
    {
        $this->graduateSchool = $graduateSchool;
    }

    public function getGraduateDate(): \DateTimeInterface
    {
        return $this->graduateDate;
    }

    public function setGraduateDate(\DateTimeInterface $graduateDate): void
    {
        $this->graduateDate = $graduateDate;
    }

    public function getWorkExperience(): int
    {
        return $this->workExperience;
    }

    public function setWorkExperience(int $workExperience): void
    {
        $this->workExperience = $workExperience;
    }

    /**
     * @return array<string>
     */
    public function getSpecialties(): array
    {
        return $this->specialties;
    }

    /**
     * @param array<string> $specialties
     */
    public function setSpecialties(array $specialties): void
    {
        $this->specialties = $specialties;
    }

    public function getTeacherStatus(): string
    {
        return $this->teacherStatus;
    }

    public function setTeacherStatus(string $teacherStatus): void
    {
        $this->teacherStatus = $teacherStatus;
    }

    public function getProfilePhoto(): ?string
    {
        return $this->profilePhoto;
    }

    public function setProfilePhoto(?string $profilePhoto): void
    {
        $this->profilePhoto = $profilePhoto;
    }

    public function getJoinDate(): \DateTimeInterface
    {
        return $this->joinDate;
    }

    public function setJoinDate(\DateTimeInterface $joinDate): void
    {
        $this->joinDate = $joinDate;
    }

    public function getLastActiveTime(): ?\DateTimeInterface
    {
        return $this->lastActiveTime;
    }

    public function setLastActiveTime(?\DateTimeInterface $lastActiveTime): void
    {
        $this->lastActiveTime = $lastActiveTime;
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

    public function __toString(): string
    {
        return $this->id;
    }
}
