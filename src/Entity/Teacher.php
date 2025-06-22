<?php

namespace Tourze\TrainTeacherBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;

/**
 * 教师实体
 * 管理教师的基本信息、联系方式、身份信息等
 */
#[ORM\Entity(repositoryClass: \Tourze\TrainTeacherBundle\Repository\TeacherRepository::class)]
#[ORM\Table(name: 'train_teacher', options: ['comment' => '教师信息表'])]
#[ORM\Index(columns: ['teacher_code'], name: 'idx_teacher_code')]
#[ORM\Index(columns: ['teacher_type'], name: 'idx_teacher_type')]
#[ORM\Index(columns: ['teacher_status'], name: 'idx_teacher_status')]
class Teacher implements Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 36, options: ['comment' => '教师ID'])]
    private string $id;

    #[ORM\Column(name: 'teacher_code', type: Types::STRING, length: 32, unique: true, options: ['comment' => '教师编号'])]
    private string $teacherCode;

    #[ORM\Column(name: 'teacher_name', type: Types::STRING, length: 50, options: ['comment' => '教师姓名'])]
    private string $teacherName;

    #[ORM\Column(name: 'teacher_type', type: Types::STRING, length: 20, options: ['comment' => '教师类型（专职、兼职）'])]
    private string $teacherType;

    #[ORM\Column(type: Types::STRING, length: 10, options: ['comment' => '性别'])]
    private string $gender;

    #[ORM\Column(name: 'birth_date', type: Types::DATE_MUTABLE, options: ['comment' => '出生日期'])]
    private \DateTimeInterface $birthDate;

    #[ORM\Column(name: 'id_card', type: Types::STRING, length: 18, options: ['comment' => '身份证号'])]
    private string $idCard;

    #[ORM\Column(type: Types::STRING, length: 20, options: ['comment' => '联系电话'])]
    private string $phone;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '邮箱'])]
    private ?string $email = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '地址'])]
    private ?string $address = null;

    #[ORM\Column(type: Types::STRING, length: 20, options: ['comment' => '学历'])]
    private string $education;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '专业'])]
    private string $major;

    #[ORM\Column(name: 'graduate_school', type: Types::STRING, length: 100, options: ['comment' => '毕业院校'])]
    private string $graduateSchool;

    #[ORM\Column(name: 'graduate_date', type: Types::DATE_MUTABLE, options: ['comment' => '毕业日期'])]
    private \DateTimeInterface $graduateDate;

    #[ORM\Column(name: 'work_experience', type: Types::INTEGER, options: ['comment' => '工作经验（年）'])]
    private int $workExperience;

    #[ORM\Column(type: Types::JSON, options: ['comment' => '专业特长'])]
    private array $specialties = [];

    #[ORM\Column(name: 'teacher_status', type: Types::STRING, length: 20, options: ['comment' => '教师状态'])]
    private string $teacherStatus;

    #[ORM\Column(name: 'profile_photo', type: Types::STRING, length: 255, nullable: true, options: ['comment' => '头像'])]
    private ?string $profilePhoto = null;

    #[ORM\Column(name: 'join_date', type: Types::DATE_MUTABLE, options: ['comment' => '入职日期'])]
    private \DateTimeInterface $joinDate;

    #[ORM\Column(name: 'create_time', type: Types::DATETIME_IMMUTABLE, options: ['comment' => '创建时间'])]
    private \DateTimeInterface $createTime;

    #[ORM\Column(name: 'update_time', type: Types::DATETIME_IMMUTABLE, options: ['comment' => '更新时间'])]
    private \DateTimeInterface $updateTime;

    #[ORM\Column(name: 'last_active_time', type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '最后活跃时间'])]
    private ?\DateTimeInterface $lastActiveTime = null;

    public function __construct()
    {
        $this->createTime = new \DateTimeImmutable();
        $this->updateTime = new \DateTimeImmutable();
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

    public function getTeacherCode(): string
    {
        return $this->teacherCode;
    }

    public function setTeacherCode(string $teacherCode): self
    {
        $this->teacherCode = $teacherCode;
        return $this;
    }

    public function getTeacherName(): string
    {
        return $this->teacherName;
    }

    public function setTeacherName(string $teacherName): self
    {
        $this->teacherName = $teacherName;
        return $this;
    }

    public function getTeacherType(): string
    {
        return $this->teacherType;
    }

    public function setTeacherType(string $teacherType): self
    {
        $this->teacherType = $teacherType;
        return $this;
    }

    public function getGender(): string
    {
        return $this->gender;
    }

    public function setGender(string $gender): self
    {
        $this->gender = $gender;
        return $this;
    }

    public function getBirthDate(): \DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTimeInterface $birthDate): self
    {
        $this->birthDate = $birthDate;
        return $this;
    }

    public function getIdCard(): string
    {
        return $this->idCard;
    }

    public function setIdCard(string $idCard): self
    {
        $this->idCard = $idCard;
        return $this;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;
        return $this;
    }

    public function getEducation(): string
    {
        return $this->education;
    }

    public function setEducation(string $education): self
    {
        $this->education = $education;
        return $this;
    }

    public function getMajor(): string
    {
        return $this->major;
    }

    public function setMajor(string $major): self
    {
        $this->major = $major;
        return $this;
    }

    public function getGraduateSchool(): string
    {
        return $this->graduateSchool;
    }

    public function setGraduateSchool(string $graduateSchool): self
    {
        $this->graduateSchool = $graduateSchool;
        return $this;
    }

    public function getGraduateDate(): \DateTimeInterface
    {
        return $this->graduateDate;
    }

    public function setGraduateDate(\DateTimeInterface $graduateDate): self
    {
        $this->graduateDate = $graduateDate;
        return $this;
    }

    public function getWorkExperience(): int
    {
        return $this->workExperience;
    }

    public function setWorkExperience(int $workExperience): self
    {
        $this->workExperience = $workExperience;
        return $this;
    }

    public function getSpecialties(): array
    {
        return $this->specialties;
    }

    public function setSpecialties(array $specialties): self
    {
        $this->specialties = $specialties;
        return $this;
    }

    public function getTeacherStatus(): string
    {
        return $this->teacherStatus;
    }

    public function setTeacherStatus(string $teacherStatus): self
    {
        $this->teacherStatus = $teacherStatus;
        return $this;
    }

    public function getProfilePhoto(): ?string
    {
        return $this->profilePhoto;
    }

    public function setProfilePhoto(?string $profilePhoto): self
    {
        $this->profilePhoto = $profilePhoto;
        return $this;
    }

    public function getJoinDate(): \DateTimeInterface
    {
        return $this->joinDate;
    }

    public function setJoinDate(\DateTimeInterface $joinDate): self
    {
        $this->joinDate = $joinDate;
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

    public function getUpdateTime(): \DateTimeInterface
    {
        return $this->updateTime;
    }

    public function setUpdateTime(\DateTimeInterface $updateTime): self
    {
        $this->updateTime = $updateTime;
        return $this;
    }

    public function getLastActiveTime(): ?\DateTimeInterface
    {
        return $this->lastActiveTime;
    }

    public function setLastActiveTime(?\DateTimeInterface $lastActiveTime): self
    {
        $this->lastActiveTime = $lastActiveTime;
        return $this;
    }

    /**
     * 更新时间戳
     */
    #[ORM\PreUpdate]
    public function updateTimestamp(): void
    {
        $this->updateTime = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }
} 