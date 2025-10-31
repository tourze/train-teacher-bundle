<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TrainTeacherBundle\Entity\Teacher;

/**
 * Teacher实体测试
 *
 * @internal
 */
#[CoversClass(Teacher::class)]
final class TeacherTest extends AbstractEntityTestCase
{
    private Teacher $teacher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->teacher = new Teacher();
    }

    protected function createEntity(): object
    {
        return new Teacher();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'id' => ['id', 'teacher_123'],
            'teacherCode' => ['teacherCode', 'TCH001'],
            'teacherName' => ['teacherName', '张老师'],
            'teacherType' => ['teacherType', '专职'],
            'gender' => ['gender', '男'],
            'birthDate' => ['birthDate', new \DateTime('1980-01-01')],
            'idCard' => ['idCard', '110101198001011234'],
            'phone' => ['phone', '13800138000'],
            'email' => ['email', 'teacher@example.com'],
            'address' => ['address', '北京市朝阳区'],
            'education' => ['education', '本科'],
            'major' => ['major', '安全工程'],
            'graduateSchool' => ['graduateSchool', '北京理工大学'],
            'graduateDate' => ['graduateDate', new \DateTime('2002-07-01')],
            'workExperience' => ['workExperience', 20],
            'specialties' => ['specialties', ['安全管理', '风险评估']],
            'teacherStatus' => ['teacherStatus', '在职'],
            'profilePhoto' => ['profilePhoto', 'photo.jpg'],
            'joinDate' => ['joinDate', new \DateTime('2005-03-01')],
            'createTime' => ['createTime', new \DateTimeImmutable('2024-01-01')],
            'updateTime' => ['updateTime', new \DateTimeImmutable('2024-01-01')],
            'isAnonymous' => ['isAnonymous', false],
        ];
    }

    public function testConstructorSetsDefaultValues(): void
    {
        $teacher = new Teacher();
        // TimestampableAware trait 的时间戳字段是可空的，由监听器自动设置
        $this->assertNull($teacher->getCreateTime());
        $this->assertNull($teacher->getUpdateTime());
    }

    public function testIdGetterAndSetter(): void
    {
        $id = 'teacher_123';
        $this->teacher->setId($id);

        $this->assertEquals($id, $this->teacher->getId());
    }

    public function testTeacherCodeGetterAndSetter(): void
    {
        $code = 'T20240101001';
        $this->teacher->setTeacherCode($code);

        $this->assertEquals($code, $this->teacher->getTeacherCode());
    }

    public function testTeacherNameGetterAndSetter(): void
    {
        $name = '张三';
        $this->teacher->setTeacherName($name);

        $this->assertEquals($name, $this->teacher->getTeacherName());
    }

    public function testTeacherTypeGetterAndSetter(): void
    {
        $type = '专职';
        $this->teacher->setTeacherType($type);

        $this->assertEquals($type, $this->teacher->getTeacherType());
    }

    public function testGenderGetterAndSetter(): void
    {
        $gender = '男';
        $this->teacher->setGender($gender);

        $this->assertEquals($gender, $this->teacher->getGender());
    }

    public function testBirthDateGetterAndSetter(): void
    {
        $birthDate = new \DateTimeImmutable('1980-01-01');
        $this->teacher->setBirthDate($birthDate);

        $this->assertEquals($birthDate, $this->teacher->getBirthDate());
    }

    public function testIdCardGetterAndSetter(): void
    {
        $idCard = '110101198001011234';
        $this->teacher->setIdCard($idCard);

        $this->assertEquals($idCard, $this->teacher->getIdCard());
    }

    public function testPhoneGetterAndSetter(): void
    {
        $phone = '13800138000';
        $this->teacher->setPhone($phone);

        $this->assertEquals($phone, $this->teacher->getPhone());
    }

    public function testEmailGetterAndSetter(): void
    {
        $email = 'test@example.com';
        $this->teacher->setEmail($email);

        $this->assertEquals($email, $this->teacher->getEmail());
    }

    public function testEmailCanBeNull(): void
    {
        $this->teacher->setEmail(null);

        $this->assertNull($this->teacher->getEmail());
    }

    public function testAddressGetterAndSetter(): void
    {
        $address = '北京市朝阳区';
        $this->teacher->setAddress($address);

        $this->assertEquals($address, $this->teacher->getAddress());
    }

    public function testAddressCanBeNull(): void
    {
        $this->teacher->setAddress(null);

        $this->assertNull($this->teacher->getAddress());
    }

    public function testEducationGetterAndSetter(): void
    {
        $education = '本科';
        $this->teacher->setEducation($education);

        $this->assertEquals($education, $this->teacher->getEducation());
    }

    public function testMajorGetterAndSetter(): void
    {
        $major = '安全工程';
        $this->teacher->setMajor($major);

        $this->assertEquals($major, $this->teacher->getMajor());
    }

    public function testGraduateSchoolGetterAndSetter(): void
    {
        $school = '北京理工大学';
        $this->teacher->setGraduateSchool($school);

        $this->assertEquals($school, $this->teacher->getGraduateSchool());
    }

    public function testGraduateDateGetterAndSetter(): void
    {
        $date = new \DateTimeImmutable('2002-07-01');
        $this->teacher->setGraduateDate($date);

        $this->assertEquals($date, $this->teacher->getGraduateDate());
    }

    public function testWorkExperienceGetterAndSetter(): void
    {
        $experience = 20;
        $this->teacher->setWorkExperience($experience);

        $this->assertEquals($experience, $this->teacher->getWorkExperience());
    }

    public function testSpecialtiesGetterAndSetter(): void
    {
        $specialties = ['安全管理', '风险评估'];
        $this->teacher->setSpecialties($specialties);

        $this->assertEquals($specialties, $this->teacher->getSpecialties());
    }

    public function testSpecialtiesDefaultEmptyArray(): void
    {
        $teacher = new Teacher();

        $this->assertEquals([], $teacher->getSpecialties());
    }

    public function testTeacherStatusGetterAndSetter(): void
    {
        $status = '在职';
        $this->teacher->setTeacherStatus($status);

        $this->assertEquals($status, $this->teacher->getTeacherStatus());
    }

    public function testProfilePhotoGetterAndSetter(): void
    {
        $photo = '/uploads/photos/teacher_123.jpg';
        $this->teacher->setProfilePhoto($photo);

        $this->assertEquals($photo, $this->teacher->getProfilePhoto());
    }

    public function testProfilePhotoCanBeNull(): void
    {
        $this->teacher->setProfilePhoto(null);

        $this->assertNull($this->teacher->getProfilePhoto());
    }

    public function testJoinDateGetterAndSetter(): void
    {
        $joinDate = new \DateTimeImmutable('2005-03-01');
        $this->teacher->setJoinDate($joinDate);

        $this->assertEquals($joinDate, $this->teacher->getJoinDate());
    }

    public function testCreateTimeGetterAndSetter(): void
    {
        $createTime = new \DateTimeImmutable('2024-01-01 10:00:00');
        $this->teacher->setCreateTime($createTime);

        $this->assertEquals($createTime, $this->teacher->getCreateTime());
    }

    public function testUpdateTimeGetterAndSetter(): void
    {
        $updateTime = new \DateTimeImmutable('2024-01-02 10:00:00');
        $this->teacher->setUpdateTime($updateTime);

        $this->assertEquals($updateTime, $this->teacher->getUpdateTime());
    }

    public function testSettersWorkCorrectly(): void
    {
        $this->teacher->setId('test_id');
        $this->teacher->setTeacherCode('T001');
        $this->teacher->setTeacherName('测试教师');
        $this->teacher->setTeacherType('专职');
        $this->teacher->setGender('男');

        $this->assertEquals('test_id', $this->teacher->getId());
        $this->assertEquals('T001', $this->teacher->getTeacherCode());
        $this->assertEquals('测试教师', $this->teacher->getTeacherName());
        $this->assertEquals('专职', $this->teacher->getTeacherType());
        $this->assertEquals('男', $this->teacher->getGender());
    }

    public function testCompleteTeacherData(): void
    {
        $birthDate = new \DateTimeImmutable('1980-01-01');
        $graduateDate = new \DateTimeImmutable('2002-07-01');
        $joinDate = new \DateTimeImmutable('2005-03-01');
        $specialties = ['安全管理', '风险评估', '应急预案'];

        $this->teacher->setId('teacher_001');
        $this->teacher->setTeacherCode('T20240101001');
        $this->teacher->setTeacherName('张三');
        $this->teacher->setTeacherType('专职');
        $this->teacher->setGender('男');
        $this->teacher->setBirthDate($birthDate);
        $this->teacher->setIdCard('110101198001011234');
        $this->teacher->setPhone('13800138000');
        $this->teacher->setEmail('zhangsan@example.com');
        $this->teacher->setAddress('北京市朝阳区');
        $this->teacher->setEducation('本科');
        $this->teacher->setMajor('安全工程');
        $this->teacher->setGraduateSchool('北京理工大学');
        $this->teacher->setGraduateDate($graduateDate);
        $this->teacher->setWorkExperience(20);
        $this->teacher->setSpecialties($specialties);
        $this->teacher->setTeacherStatus('在职');
        $this->teacher->setProfilePhoto('/uploads/photos/teacher_001.jpg');
        $this->teacher->setJoinDate($joinDate);

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

    public function testClassInstantiation(): void
    {
        $instance = new Teacher();
        $this->assertInstanceOf(Teacher::class, $instance);
    }

    public function testStringable(): void
    {
        $teacher = new Teacher();
        $teacher->setId('test-id');
        $this->assertEquals('test-id', (string) $teacher);
    }
}
