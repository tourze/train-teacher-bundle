<?php

namespace Tourze\TrainTeacherBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Tourze\TrainTeacherBundle\Entity\Teacher;
use Tourze\TrainTeacherBundle\Exception\DuplicateTeacherException;
use Tourze\TrainTeacherBundle\Exception\TeacherNotFoundException;
use Tourze\TrainTeacherBundle\Repository\TeacherRepository;

/**
 * 教师管理服务
 * 提供教师的创建、更新、查询等核心业务功能
 */
class TeacherService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TeacherRepository $teacherRepository
    ) {
    }

    /**
     * 创建教师
     */
    public function createTeacher(array $teacherData): Teacher
    {
        // 检查教师编号是否已存在
        if ((bool) isset($teacherData['teacherCode']) && 
            $this->teacherRepository->findByTeacherCode($teacherData['teacherCode']) !== null) {
            throw new DuplicateTeacherException('教师编号已存在: ' . $teacherData['teacherCode']);
        }

        // 检查身份证号是否已存在
        if ((bool) isset($teacherData['idCard']) && 
            $this->teacherRepository->findByIdCard($teacherData['idCard']) !== null) {
            throw new DuplicateTeacherException('身份证号已存在: ' . $teacherData['idCard']);
        }

        // 检查手机号是否已存在
        if ((bool) isset($teacherData['phone']) && 
            $this->teacherRepository->findByPhone($teacherData['phone']) !== null) {
            throw new DuplicateTeacherException('手机号已存在: ' . $teacherData['phone']);
        }

        $teacher = new Teacher();
        $this->populateTeacherData($teacher, $teacherData);
        
        // 生成教师ID
        $teacher->setId($this->generateTeacherId());
        
        // 如果没有提供教师编号，自动生成
        if (!isset($teacherData['teacherCode'])) {
            $teacher->setTeacherCode($this->generateTeacherCode());
        }

        $this->entityManager->persist($teacher);
        $this->entityManager->flush();

        return $teacher;
    }

    /**
     * 更新教师信息
     */
    public function updateTeacher(string $teacherId, array $teacherData): Teacher
    {
        $teacher = $this->getTeacherById($teacherId);
        
        // 检查更新的数据是否与其他教师冲突
        if ((bool) isset($teacherData['teacherCode']) && 
            $teacherData['teacherCode'] !== $teacher->getTeacherCode()) {
            $existingTeacher = $this->teacherRepository->findByTeacherCode($teacherData['teacherCode']);
            if ($existingTeacher !== null && $existingTeacher->getId() !== $teacherId) {
                throw new DuplicateTeacherException('教师编号已存在: ' . $teacherData['teacherCode']);
            }
        }

        if ((bool) isset($teacherData['idCard']) && 
            $teacherData['idCard'] !== $teacher->getIdCard()) {
            $existingTeacher = $this->teacherRepository->findByIdCard($teacherData['idCard']);
            if ($existingTeacher !== null && $existingTeacher->getId() !== $teacherId) {
                throw new DuplicateTeacherException('身份证号已存在: ' . $teacherData['idCard']);
            }
        }

        if ((bool) isset($teacherData['phone']) && 
            $teacherData['phone'] !== $teacher->getPhone()) {
            $existingTeacher = $this->teacherRepository->findByPhone($teacherData['phone']);
            if ($existingTeacher !== null && $existingTeacher->getId() !== $teacherId) {
                throw new DuplicateTeacherException('手机号已存在: ' . $teacherData['phone']);
            }
        }

        $this->populateTeacherData($teacher, $teacherData);

        $this->entityManager->flush();

        return $teacher;
    }

    /**
     * 根据ID获取教师
     */
    public function getTeacherById(string $teacherId): Teacher
    {
        $teacher = $this->teacherRepository->find($teacherId);
        if ($teacher === null) {
            throw new TeacherNotFoundException('教师不存在: ' . $teacherId);
        }
        return $teacher;
    }

    /**
     * 根据教师编号获取教师
     */
    public function getTeacherByCode(string $teacherCode): Teacher
    {
        $teacher = $this->teacherRepository->findByTeacherCode($teacherCode);
        if ($teacher === null) {
            throw new TeacherNotFoundException('教师不存在: ' . $teacherCode);
        }
        return $teacher;
    }

    /**
     * 根据教师类型获取教师列表
     */
    public function getTeachersByType(string $type): array
    {
        return $this->teacherRepository->findByTeacherType($type);
    }

    /**
     * 根据教师状态获取教师列表
     */
    public function getTeachersByStatus(string $status): array
    {
        return $this->teacherRepository->findByTeacherStatus($status);
    }

    /**
     * 更改教师状态
     */
    public function changeTeacherStatus(string $teacherId, string $status, string $reason = ''): Teacher
    {
        $teacher = $this->getTeacherById($teacherId);
        $teacher->setTeacherStatus($status);

        $this->entityManager->flush();

        return $teacher;
    }

    /**
     * 搜索教师
     */
    public function searchTeachers(string $keyword, int $limit = 20): array
    {
        return $this->teacherRepository->searchTeachers($keyword, $limit);
    }

    /**
     * 获取教师统计信息
     */
    public function getTeacherStatistics(): array
    {
        return $this->teacherRepository->getTeacherStatistics();
    }

    /**
     * 获取最近加入的教师
     */
    public function getRecentTeachers(int $limit = 10): array
    {
        return $this->teacherRepository->getRecentTeachers($limit);
    }

    /**
     * 删除教师
     */
    public function deleteTeacher(string $teacherId): void
    {
        $teacher = $this->getTeacherById($teacherId);
        $this->entityManager->remove($teacher);
        $this->entityManager->flush();
    }

    /**
     * 填充教师数据
     */
    private function populateTeacherData(Teacher $teacher, array $data): void
    {
        if ((bool) isset($data['teacherCode'])) {
            $teacher->setTeacherCode($data['teacherCode']);
        }
        if ((bool) isset($data['teacherName'])) {
            $teacher->setTeacherName($data['teacherName']);
        }
        if ((bool) isset($data['teacherType'])) {
            $teacher->setTeacherType($data['teacherType']);
        }
        if ((bool) isset($data['gender'])) {
            $teacher->setGender($data['gender']);
        }
        if ((bool) isset($data['birthDate'])) {
            $teacher->setBirthDate($data['birthDate']);
        }
        if ((bool) isset($data['idCard'])) {
            $teacher->setIdCard($data['idCard']);
        }
        if ((bool) isset($data['phone'])) {
            $teacher->setPhone($data['phone']);
        }
        if ((bool) isset($data['email'])) {
            $teacher->setEmail($data['email']);
        }
        if ((bool) isset($data['address'])) {
            $teacher->setAddress($data['address']);
        }
        if ((bool) isset($data['education'])) {
            $teacher->setEducation($data['education']);
        }
        if ((bool) isset($data['major'])) {
            $teacher->setMajor($data['major']);
        }
        if ((bool) isset($data['graduateSchool'])) {
            $teacher->setGraduateSchool($data['graduateSchool']);
        }
        if ((bool) isset($data['graduateDate'])) {
            $teacher->setGraduateDate($data['graduateDate']);
        }
        if ((bool) isset($data['workExperience'])) {
            $teacher->setWorkExperience($data['workExperience']);
        }
        if ((bool) isset($data['specialties'])) {
            $teacher->setSpecialties($data['specialties']);
        }
        if ((bool) isset($data['teacherStatus'])) {
            $teacher->setTeacherStatus($data['teacherStatus']);
        }
        if ((bool) isset($data['profilePhoto'])) {
            $teacher->setProfilePhoto($data['profilePhoto']);
        }
        if ((bool) isset($data['joinDate'])) {
            $teacher->setJoinDate($data['joinDate']);
        }
    }

    /**
     * 生成教师ID
     */
    private function generateTeacherId(): string
    {
        return uniqid('teacher_', true);
    }

    /**
     * 生成教师编号
     */
    private function generateTeacherCode(): string
    {
        $prefix = 'T';
        $timestamp = date('Ymd');
        $random = str_pad((string) mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        return $prefix . $timestamp . $random;
    }
} 