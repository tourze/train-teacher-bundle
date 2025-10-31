<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Tourze\TrainTeacherBundle\Entity\Teacher;
use Tourze\TrainTeacherBundle\Exception\DuplicateTeacherException;
use Tourze\TrainTeacherBundle\Exception\TeacherNotFoundException;
use Tourze\TrainTeacherBundle\Helper\TeacherDataPopulator;
use Tourze\TrainTeacherBundle\Repository\TeacherRepository;

/**
 * 教师管理服务
 * 提供教师的创建、更新、查询等核心业务功能
 */
class TeacherService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TeacherRepository $teacherRepository,
        private readonly TeacherDataPopulator $dataPopulator,
    ) {
    }

    /**
     * 创建教师
     * @param array<string, mixed> $teacherData
     */
    public function createTeacher(array $teacherData): Teacher
    {
        $this->validateCreateUniqueFields($teacherData);

        $teacher = new Teacher();
        $this->dataPopulator->populate($teacher, $teacherData);
        $teacher->setId($this->generateTeacherId());
        if (!isset($teacherData['teacherCode'])) {
            $teacher->setTeacherCode($this->generateTeacherCode());
        }

        $this->entityManager->persist($teacher);
        $this->entityManager->flush();

        return $teacher;
    }

    /**
     * 更新教师信息
     * @param array<string, mixed> $teacherData
     */
    public function updateTeacher(string $teacherId, array $teacherData): Teacher
    {
        $teacher = $this->getTeacherById($teacherId);
        $this->validateUniqueFieldsForUpdate($teacher, $teacherData, $teacherId);
        $this->dataPopulator->populate($teacher, $teacherData);
        $this->entityManager->flush();

        return $teacher;
    }

    /**
     * 根据ID获取教师
     */
    public function getTeacherById(string $teacherId): Teacher
    {
        $teacher = $this->teacherRepository->find($teacherId);
        if (!$teacher instanceof Teacher) {
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
        if (null === $teacher) {
            throw new TeacherNotFoundException('教师不存在: ' . $teacherCode);
        }

        return $teacher;
    }

    /**
     * 根据教师类型获取教师列表
     * @return array<int, Teacher>
     */
    public function getTeachersByType(string $type): array
    {
        return $this->teacherRepository->findByTeacherType($type);
    }

    /**
     * 根据教师状态获取教师列表
     * @return array<int, Teacher>
     */
    public function getTeachersByStatus(string $status): array
    {
        return $this->teacherRepository->findByTeacherStatus($status);
    }

    /**
     * 更改教师状态
     */
    public function changeTeacherStatus(string $teacherId, string $status): Teacher
    {
        $teacher = $this->getTeacherById($teacherId);
        $teacher->setTeacherStatus($status);

        $this->entityManager->flush();

        return $teacher;
    }

    /**
     * 搜索教师
     * @return array<int, Teacher>
     */
    public function searchTeachers(string $keyword, int $limit = 20): array
    {
        return $this->teacherRepository->searchTeachers($keyword, $limit);
    }

    /**
     * 获取教师统计信息
     * @return array<string, mixed>
     */
    public function getTeacherStatistics(): array
    {
        return $this->teacherRepository->getTeacherStatistics();
    }

    /**
     * 获取最近加入的教师
     * @return array<int, Teacher>
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
     * 验证更新时的唯一字段冲突
     * @param array<string, mixed> $teacherData
     */
    private function validateUniqueFieldsForUpdate(Teacher $teacher, array $teacherData, string $teacherId): void
    {
        $this->checkUpdateTeacherCode($teacher, $teacherData, $teacherId);
        $this->checkUpdateIdCard($teacher, $teacherData, $teacherId);
        $this->checkUpdatePhone($teacher, $teacherData, $teacherId);
    }

    /**
     * @param array<string, mixed> $teacherData
     */
    private function checkUpdateTeacherCode(Teacher $teacher, array $teacherData, string $teacherId): void
    {
        if (!isset($teacherData['teacherCode']) || !is_string($teacherData['teacherCode']) || $teacherData['teacherCode'] === $teacher->getTeacherCode()) {
            return;
        }

        $existing = $this->teacherRepository->findByTeacherCode($teacherData['teacherCode']);
        if (null !== $existing && $existing->getId() !== $teacherId) {
            throw new DuplicateTeacherException('教师编号已存在: ' . $teacherData['teacherCode']);
        }
    }

    /**
     * @param array<string, mixed> $teacherData
     */
    private function checkUpdateIdCard(Teacher $teacher, array $teacherData, string $teacherId): void
    {
        if (!isset($teacherData['idCard']) || !is_string($teacherData['idCard']) || $teacherData['idCard'] === $teacher->getIdCard()) {
            return;
        }

        $existing = $this->teacherRepository->findByIdCard($teacherData['idCard']);
        if (null !== $existing && $existing->getId() !== $teacherId) {
            throw new DuplicateTeacherException('身份证号已存在: ' . $teacherData['idCard']);
        }
    }

    /**
     * @param array<string, mixed> $teacherData
     */
    private function checkUpdatePhone(Teacher $teacher, array $teacherData, string $teacherId): void
    {
        if (!isset($teacherData['phone']) || !is_string($teacherData['phone']) || $teacherData['phone'] === $teacher->getPhone()) {
            return;
        }

        $existing = $this->teacherRepository->findByPhone($teacherData['phone']);
        if (null !== $existing && $existing->getId() !== $teacherId) {
            throw new DuplicateTeacherException('手机号已存在: ' . $teacherData['phone']);
        }
    }

    /**
     * 验证创建时的唯一字段
     * @param array<string, mixed> $teacherData
     */
    private function validateCreateUniqueFields(array $teacherData): void
    {
        $this->checkCreateTeacherCode($teacherData);
        $this->checkCreateIdCard($teacherData);
        $this->checkCreatePhone($teacherData);
    }

    /**
     * @param array<string, mixed> $teacherData
     */
    private function checkCreateTeacherCode(array $teacherData): void
    {
        if (!isset($teacherData['teacherCode']) || !is_string($teacherData['teacherCode'])) {
            return;
        }

        $existing = $this->teacherRepository->findByTeacherCode($teacherData['teacherCode']);
        if (null !== $existing) {
            throw new DuplicateTeacherException('教师编号已存在: ' . $teacherData['teacherCode']);
        }
    }

    /**
     * @param array<string, mixed> $teacherData
     */
    private function checkCreateIdCard(array $teacherData): void
    {
        if (!isset($teacherData['idCard']) || !is_string($teacherData['idCard'])) {
            return;
        }

        $existing = $this->teacherRepository->findByIdCard($teacherData['idCard']);
        if (null !== $existing) {
            throw new DuplicateTeacherException('身份证号已存在: ' . $teacherData['idCard']);
        }
    }

    /**
     * @param array<string, mixed> $teacherData
     */
    private function checkCreatePhone(array $teacherData): void
    {
        if (!isset($teacherData['phone']) || !is_string($teacherData['phone'])) {
            return;
        }

        $existing = $this->teacherRepository->findByPhone($teacherData['phone']);
        if (null !== $existing) {
            throw new DuplicateTeacherException('手机号已存在: ' . $teacherData['phone']);
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
