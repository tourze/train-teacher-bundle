<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Helper;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TrainTeacherBundle\Entity\Teacher;
use Tourze\TrainTeacherBundle\Helper\TeacherDataPopulator;

/**
 * @internal
 */
#[CoversClass(TeacherDataPopulator::class)]
class TeacherDataPopulatorTest extends TestCase
{
    private TeacherDataPopulator $populator;

    protected function setUp(): void
    {
        $this->populator = new TeacherDataPopulator();
    }

    public function testPopulateBasicInfo(): void
    {
        $teacher = new Teacher();
        $data = [
            'teacherCode' => 'T001',
            'teacherName' => '张三',
            'teacherType' => 'full-time',
        ];

        $this->populator->populate($teacher, $data);

        self::assertSame('T001', $teacher->getTeacherCode());
        self::assertSame('张三', $teacher->getTeacherName());
        self::assertSame('full-time', $teacher->getTeacherType());
    }

    public function testPopulatePersonalInfo(): void
    {
        $teacher = new Teacher();
        $data = [
            'gender' => '男',
            'idCard' => '110101199001011234',
            'birthDate' => new \DateTimeImmutable('1990-01-01'),
        ];

        $this->populator->populate($teacher, $data);

        self::assertSame('男', $teacher->getGender());
        self::assertSame('110101199001011234', $teacher->getIdCard());
        self::assertEquals(new \DateTimeImmutable('1990-01-01'), $teacher->getBirthDate());
    }

    public function testPopulateContactInfo(): void
    {
        $teacher = new Teacher();
        $data = [
            'phone' => '13800138000',
            'email' => 'test@example.com',
            'address' => '北京市朝阳区',
        ];

        $this->populator->populate($teacher, $data);

        self::assertSame('13800138000', $teacher->getPhone());
        self::assertSame('test@example.com', $teacher->getEmail());
        self::assertSame('北京市朝阳区', $teacher->getAddress());
    }

    public function testPopulateEducationInfo(): void
    {
        $teacher = new Teacher();
        $data = [
            'education' => '硕士',
            'major' => '计算机科学',
            'graduateSchool' => '清华大学',
            'graduateDate' => new \DateTimeImmutable('2015-07-01'),
        ];

        $this->populator->populate($teacher, $data);

        self::assertSame('硕士', $teacher->getEducation());
        self::assertSame('计算机科学', $teacher->getMajor());
        self::assertSame('清华大学', $teacher->getGraduateSchool());
        self::assertEquals(new \DateTimeImmutable('2015-07-01'), $teacher->getGraduateDate());
    }

    public function testPopulateSpecialties(): void
    {
        $teacher = new Teacher();
        $data = [
            'specialties' => ['PHP开发', 'Symfony框架', '数据库设计'],
        ];

        $this->populator->populate($teacher, $data);

        self::assertCount(3, $teacher->getSpecialties());
        self::assertContains('PHP开发', $teacher->getSpecialties());
        self::assertContains('Symfony框架', $teacher->getSpecialties());
    }
}
