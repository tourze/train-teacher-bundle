<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Helper;

use Tourze\TrainTeacherBundle\Entity\Teacher;

/**
 * 教师数据填充器
 * 负责将原始数据填充到Teacher实体
 */
class TeacherDataPopulator
{
    /**
     * 填充教师数据
     * @param array<string, mixed> $data
     */
    public function populate(Teacher $teacher, array $data): void
    {
        $this->setBasicInfo($teacher, $data);
        $this->setPersonalInfo($teacher, $data);
        $this->setContactInfo($teacher, $data);
        $this->setEducationInfo($teacher, $data);
        $this->setWorkInfo($teacher, $data);
    }

    /**
     * 设置基本信息
     * @param array<string, mixed> $data
     */
    private function setBasicInfo(Teacher $teacher, array $data): void
    {
        if (isset($data['teacherCode']) && is_string($data['teacherCode'])) {
            $teacher->setTeacherCode($data['teacherCode']);
        }
        if (isset($data['teacherName']) && is_string($data['teacherName'])) {
            $teacher->setTeacherName($data['teacherName']);
        }
        if (isset($data['teacherType']) && is_string($data['teacherType'])) {
            $teacher->setTeacherType($data['teacherType']);
        }
    }

    /**
     * 设置个人信息
     * @param array<string, mixed> $data
     */
    private function setPersonalInfo(Teacher $teacher, array $data): void
    {
        if (isset($data['gender']) && is_string($data['gender'])) {
            $teacher->setGender($data['gender']);
        }
        if (isset($data['idCard']) && is_string($data['idCard'])) {
            $teacher->setIdCard($data['idCard']);
        }
        if (isset($data['birthDate']) && $data['birthDate'] instanceof \DateTimeInterface) {
            $teacher->setBirthDate($data['birthDate']);
        }
    }

    /**
     * 设置联系信息
     * @param array<string, mixed> $data
     */
    private function setContactInfo(Teacher $teacher, array $data): void
    {
        if (isset($data['phone']) && is_string($data['phone'])) {
            $teacher->setPhone($data['phone']);
        }
        if (array_key_exists('email', $data) && (null === $data['email'] || is_string($data['email']))) {
            $teacher->setEmail($data['email']);
        }
        if (array_key_exists('address', $data) && (null === $data['address'] || is_string($data['address']))) {
            $teacher->setAddress($data['address']);
        }
    }

    /**
     * 设置教育信息
     * @param array<string, mixed> $data
     */
    private function setEducationInfo(Teacher $teacher, array $data): void
    {
        if (isset($data['education']) && is_string($data['education'])) {
            $teacher->setEducation($data['education']);
        }
        if (isset($data['major']) && is_string($data['major'])) {
            $teacher->setMajor($data['major']);
        }
        if (isset($data['graduateSchool']) && is_string($data['graduateSchool'])) {
            $teacher->setGraduateSchool($data['graduateSchool']);
        }
        if (isset($data['graduateDate']) && $data['graduateDate'] instanceof \DateTimeInterface) {
            $teacher->setGraduateDate($data['graduateDate']);
        }
    }

    /**
     * 设置工作信息
     * @param array<string, mixed> $data
     */
    private function setWorkInfo(Teacher $teacher, array $data): void
    {
        if (isset($data['workExperience']) && is_int($data['workExperience'])) {
            $teacher->setWorkExperience($data['workExperience']);
        }
        if (isset($data['teacherStatus']) && is_string($data['teacherStatus'])) {
            $teacher->setTeacherStatus($data['teacherStatus']);
        }
        if (array_key_exists('profilePhoto', $data) && (null === $data['profilePhoto'] || is_string($data['profilePhoto']))) {
            $teacher->setProfilePhoto($data['profilePhoto']);
        }
        if (isset($data['specialties']) && is_array($data['specialties'])) {
            $this->setSpecialties($teacher, $data['specialties']);
        }
        if (isset($data['joinDate']) && $data['joinDate'] instanceof \DateTimeInterface) {
            $teacher->setJoinDate($data['joinDate']);
        }
    }

    /**
     * 设置专长
     * @param array<mixed> $specialties
     */
    private function setSpecialties(Teacher $teacher, array $specialties): void
    {
        /** @var array<int, string> $validSpecialties */
        $validSpecialties = [];
        foreach (array_values($specialties) as $specialty) {
            if (is_string($specialty)) {
                $validSpecialties[] = $specialty;
            }
        }
        $teacher->setSpecialties($validSpecialties);
    }
}
