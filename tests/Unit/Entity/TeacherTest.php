<?php

namespace Tourze\TrainTeacherBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\TrainTeacherBundle\Entity\Teacher;

/**
 * Teacher实体单元测试
 */
class TeacherTest extends TestCase
{
    private Teacher $teacher;

    protected function setUp(): void
    {
        $this->teacher = new Teacher();
    }

    public function test_constructor_sets_default_values(): void
    {
        $teacher = new Teacher();
        
        $this->assertInstanceOf(\DateTimeInterface::class, $teacher->getCreateTime());
        $this->assertInstanceOf(\DateTimeInterface::class, $teacher->getUpdateTime());
    }

    public function test_id_getter_and_setter(): void
    {
        $id = 'teacher_123';
        $this->teacher->setId($id);
        
        $this->assertEquals($id, $this->teacher->getId());
    }

    public function test_teacher_code_getter_and_setter(): void
    {
        $code = 'T20240101001';
        $this->teacher->setTeacherCode($code);
        
        $this->assertEquals($code, $this->teacher->getTeacherCode());
    }

    public function test_teacher_name_getter_and_setter(): void
    {
        $name = '张三';
        $this->teacher->setTeacherName($name);
        
        $this->assertEquals($name, $this->teacher->getTeacherName());
    }

    public function test_teacher_type_getter_and_setter(): void
    {
        $type = '专职';
        $this->teacher->setTeacherType($type);
        
        $this->assertEquals($type, $this->teacher->getTeacherType());
    }

    public function test_gender_getter_and_setter(): void
    {
        $gender = '男';
        $this->teacher->setGender($gender);
        
        $this->assertEquals($gender, $this->teacher->getGender());
    }

    public function test_birth_date_getter_and_setter(): void
    {
        $birthDate = new \DateTime('1980-01-01');
        $this->teacher->setBirthDate($birthDate);
        
        $this->assertEquals($birthDate, $this->teacher->getBirthDate());
    }

    public function test_id_card_getter_and_setter(): void
    {
        $idCard = '110101198001011234';
        $this->teacher->setIdCard($idCard);
        
        $this->assertEquals($idCard, $this->teacher->getIdCard());
    }

    public function test_phone_getter_and_setter(): void
    {
        $phone = '13800138000';
        $this->teacher->setPhone($phone);
        
        $this->assertEquals($phone, $this->teacher->getPhone());
    }

    public function test_email_getter_and_setter(): void
    {
        $email = 'test@example.com';
        $this->teacher->setEmail($email);
        
        $this->assertEquals($email, $this->teacher->getEmail());
    }

    public function test_email_can_be_null(): void
    {
        $this->teacher->setEmail(null);
        
        $this->assertNull($this->teacher->getEmail());
    }

    public function test_address_getter_and_setter(): void
    {
        $address = '北京市朝阳区';
        $this->teacher->setAddress($address);
        
        $this->assertEquals($address, $this->teacher->getAddress());
    }

    public function test_address_can_be_null(): void
    {
        $this->teacher->setAddress(null);
        
        $this->assertNull($this->teacher->getAddress());
    }

    public function test_education_getter_and_setter(): void
    {
        $education = '本科';
        $this->teacher->setEducation($education);
        
        $this->assertEquals($education, $this->teacher->getEducation());
    }

    public function test_major_getter_and_setter(): void
    {
        $major = '安全工程';
        $this->teacher->setMajor($major);
        
        $this->assertEquals($major, $this->teacher->getMajor());
    }

    public function test_graduate_school_getter_and_setter(): void
    {
        $school = '北京理工大学';
        $this->teacher->setGraduateSchool($school);
        
        $this->assertEquals($school, $this->teacher->getGraduateSchool());
    }

    public function test_graduate_date_getter_and_setter(): void
    {
        $date = new \DateTime('2002-07-01');
        $this->teacher->setGraduateDate($date);
        
        $this->assertEquals($date, $this->teacher->getGraduateDate());
    }

    public function test_work_experience_getter_and_setter(): void
    {
        $experience = 20;
        $this->teacher->setWorkExperience($experience);
        
        $this->assertEquals($experience, $this->teacher->getWorkExperience());
    }

    public function test_specialties_getter_and_setter(): void
    {
        $specialties = ['安全管理', '风险评估'];
        $this->teacher->setSpecialties($specialties);
        
        $this->assertEquals($specialties, $this->teacher->getSpecialties());
    }

    public function test_specialties_default_empty_array(): void
    {
        $teacher = new Teacher();
        
        $this->assertEquals([], $teacher->getSpecialties());
    }

    public function test_teacher_status_getter_and_setter(): void
    {
        $status = '在职';
        $this->teacher->setTeacherStatus($status);
        
        $this->assertEquals($status, $this->teacher->getTeacherStatus());
    }

    public function test_profile_photo_getter_and_setter(): void
    {
        $photo = '/uploads/photos/teacher_123.jpg';
        $this->teacher->setProfilePhoto($photo);
        
        $this->assertEquals($photo, $this->teacher->getProfilePhoto());
    }

    public function test_profile_photo_can_be_null(): void
    {
        $this->teacher->setProfilePhoto(null);
        
        $this->assertNull($this->teacher->getProfilePhoto());
    }

    public function test_join_date_getter_and_setter(): void
    {
        $joinDate = new \DateTime('2005-03-01');
        $this->teacher->setJoinDate($joinDate);
        
        $this->assertEquals($joinDate, $this->teacher->getJoinDate());
    }

    public function test_create_time_getter_and_setter(): void
    {
        $createTime = new \DateTime('2024-01-01 10:00:00');
        $this->teacher->setCreateTime($createTime);
        
        $this->assertEquals($createTime, $this->teacher->getCreateTime());
    }

    public function test_update_time_getter_and_setter(): void
    {
        $updateTime = new \DateTime('2024-01-02 10:00:00');
        $this->teacher->setUpdateTime($updateTime);
        
        $this->assertEquals($updateTime, $this->teacher->getUpdateTime());
    }

    public function test_update_timestamp_method(): void
    {
        $originalUpdateTime = $this->teacher->getUpdateTime();
        
        // 等待一毫秒确保时间不同
        usleep(1000);
        
        $this->teacher->updateTimestamp();
        
        $this->assertGreaterThan($originalUpdateTime, $this->teacher->getUpdateTime());
    }

    public function test_fluent_interface(): void
    {
        $result = $this->teacher
            ->setId('test_id')
            ->setTeacherCode('T001')
            ->setTeacherName('测试教师')
            ->setTeacherType('专职')
            ->setGender('男');
        
        $this->assertSame($this->teacher, $result);
        $this->assertEquals('test_id', $this->teacher->getId());
        $this->assertEquals('T001', $this->teacher->getTeacherCode());
        $this->assertEquals('测试教师', $this->teacher->getTeacherName());
        $this->assertEquals('专职', $this->teacher->getTeacherType());
        $this->assertEquals('男', $this->teacher->getGender());
    }

    public function test_complete_teacher_data(): void
    {
        $birthDate = new \DateTime('1980-01-01');
        $graduateDate = new \DateTime('2002-07-01');
        $joinDate = new \DateTime('2005-03-01');
        $specialties = ['安全管理', '风险评估', '应急预案'];

        $this->teacher
            ->setId('teacher_001')
            ->setTeacherCode('T20240101001')
            ->setTeacherName('张三')
            ->setTeacherType('专职')
            ->setGender('男')
            ->setBirthDate($birthDate)
            ->setIdCard('110101198001011234')
            ->setPhone('13800138000')
            ->setEmail('zhangsan@example.com')
            ->setAddress('北京市朝阳区')
            ->setEducation('本科')
            ->setMajor('安全工程')
            ->setGraduateSchool('北京理工大学')
            ->setGraduateDate($graduateDate)
            ->setWorkExperience(20)
            ->setSpecialties($specialties)
            ->setTeacherStatus('在职')
            ->setProfilePhoto('/uploads/photos/teacher_001.jpg')
            ->setJoinDate($joinDate);

        $this->assertEquals('teacher_001', $this->teacher->getId());
        $this->assertEquals('T20240101001', $this->teacher->getTeacherCode());
        $this->assertEquals('张三', $this->teacher->getTeacherName());
        $this->assertEquals('专职', $this->teacher->getTeacherType());
        $this->assertEquals('男', $this->teacher->getGender());
        $this->assertEquals($birthDate, $this->teacher->getBirthDate());
        $this->assertEquals('110101198001011234', $this->teacher->getIdCard());
        $this->assertEquals('13800138000', $this->teacher->getPhone());
        $this->assertEquals('zhangsan@example.com', $this->teacher->getEmail());
        $this->assertEquals('北京市朝阳区', $this->teacher->getAddress());
        $this->assertEquals('本科', $this->teacher->getEducation());
        $this->assertEquals('安全工程', $this->teacher->getMajor());
        $this->assertEquals('北京理工大学', $this->teacher->getGraduateSchool());
        $this->assertEquals($graduateDate, $this->teacher->getGraduateDate());
        $this->assertEquals(20, $this->teacher->getWorkExperience());
        $this->assertEquals($specialties, $this->teacher->getSpecialties());
        $this->assertEquals('在职', $this->teacher->getTeacherStatus());
        $this->assertEquals('/uploads/photos/teacher_001.jpg', $this->teacher->getProfilePhoto());
        $this->assertEquals($joinDate, $this->teacher->getJoinDate());
    }
} 