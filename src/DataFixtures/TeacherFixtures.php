<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\DataFixtures;

use Carbon\CarbonImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\TrainTeacherBundle\Entity\Teacher;

#[When(env: 'test')]
#[When(env: 'dev')]
class TeacherFixtures extends Fixture implements FixtureGroupInterface
{
    public const TEACHER_REFERENCE_PREFIX = 'teacher-';
    public const SENIOR_TEACHER_REFERENCE = 'senior-teacher';
    public const JUNIOR_TEACHER_REFERENCE = 'junior-teacher';

    public static function getGroups(): array
    {
        return ['teacher'];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('zh_CN');

        // 创建一名资深教师
        $seniorTeacher = new Teacher();
        $seniorTeacher->setId('teacher-senior-001');
        $seniorTeacher->setTeacherCode('T001');
        $seniorTeacher->setTeacherName('张教授');
        $seniorTeacher->setTeacherType('专职');
        $seniorTeacher->setGender('男');
        $seniorTeacher->setBirthDate(CarbonImmutable::createFromDate(1975, 5, 15));
        $seniorTeacher->setIdCard('110101197505150001');
        $seniorTeacher->setPhone('13800138001');
        $seniorTeacher->setEmail('zhang.professor@test.local');
        $seniorTeacher->setAddress('北京市朝阳区某某路123号');
        $seniorTeacher->setEducation('博士');
        $seniorTeacher->setMajor('计算机科学与技术');
        $seniorTeacher->setGraduateSchool('清华大学');
        $seniorTeacher->setGraduateDate(CarbonImmutable::createFromDate(2000, 6, 30));
        $seniorTeacher->setWorkExperience(20);
        $seniorTeacher->setSpecialties(['软件工程', '人工智能', '数据结构']);
        $seniorTeacher->setTeacherStatus('在职');
        $seniorTeacher->setJoinDate(CarbonImmutable::createFromDate(2005, 3, 1));
        $seniorTeacher->setCreateTime(CarbonImmutable::now()->modify('-30 days'));
        $seniorTeacher->setUpdateTime(CarbonImmutable::now()->modify('-5 days'));

        $manager->persist($seniorTeacher);
        $this->addReference(self::SENIOR_TEACHER_REFERENCE, $seniorTeacher);

        // 创建一名初级教师
        $juniorTeacher = new Teacher();
        $juniorTeacher->setId('teacher-junior-001');
        $juniorTeacher->setTeacherCode('T002');
        $juniorTeacher->setTeacherName('李老师');
        $juniorTeacher->setTeacherType('兼职');
        $juniorTeacher->setGender('女');
        $juniorTeacher->setBirthDate(CarbonImmutable::createFromDate(1990, 8, 20));
        $juniorTeacher->setIdCard('110101199008200002');
        $juniorTeacher->setPhone('13800138002');
        $juniorTeacher->setEmail('li.teacher@test.local');
        $juniorTeacher->setEducation('硕士');
        $juniorTeacher->setMajor('软件工程');
        $juniorTeacher->setGraduateSchool('北京大学');
        $juniorTeacher->setGraduateDate(CarbonImmutable::createFromDate(2015, 6, 30));
        $juniorTeacher->setWorkExperience(8);
        $juniorTeacher->setSpecialties(['前端开发', 'JavaScript']);
        $juniorTeacher->setTeacherStatus('在职');
        $juniorTeacher->setJoinDate(CarbonImmutable::createFromDate(2020, 9, 1));
        $juniorTeacher->setCreateTime(CarbonImmutable::now()->modify('-20 days'));
        $juniorTeacher->setUpdateTime(CarbonImmutable::now()->modify('-3 days'));

        $manager->persist($juniorTeacher);
        $this->addReference(self::JUNIOR_TEACHER_REFERENCE, $juniorTeacher);

        // 创建其他测试教师
        for ($i = 3; $i <= 10; ++$i) {
            $teacher = new Teacher();
            $teacher->setId('teacher-test-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT));
            $teacher->setTeacherCode('T' . str_pad((string) $i, 3, '0', STR_PAD_LEFT));
            $teacher->setTeacherName($faker->name());
            $teacherType = $faker->randomElement(['专职', '兼职']);
            $teacher->setTeacherType(is_string($teacherType) ? $teacherType : '专职');
            $gender = $faker->randomElement(['男', '女']);
            $teacher->setGender(is_string($gender) ? $gender : '男');
            $teacher->setBirthDate(CarbonImmutable::createFromDate($faker->numberBetween(1970, 1995), $faker->numberBetween(1, 12), $faker->numberBetween(1, 28)));
            $teacher->setIdCard($faker->numerify('################'));
            $teacher->setPhone($faker->phoneNumber());
            $teacher->setEmail($faker->email());
            $education = $faker->randomElement(['本科', '硕士', '博士']);
            $teacher->setEducation(is_string($education) ? $education : '本科');
            $major = $faker->randomElement(['计算机科学', '软件工程', '信息技术', '数学', '物理']);
            $teacher->setMajor(is_string($major) ? $major : '计算机科学');
            $graduateSchool = $faker->randomElement(['清华大学', '北京大学', '复旦大学', '上海交通大学']);
            $teacher->setGraduateSchool(is_string($graduateSchool) ? $graduateSchool : '清华大学');
            $teacher->setGraduateDate(CarbonImmutable::createFromDate($faker->numberBetween(2000, 2020), 6, 30));
            $teacher->setWorkExperience($faker->numberBetween(1, 20));
            $specialties = $faker->randomElements(['Java', 'Python', 'JavaScript', 'C++', '数据库', '网络安全'], $faker->numberBetween(1, 3));
            /** @var array<string> $filteredSpecialties */
            $filteredSpecialties = array_filter($specialties, static fn ($s): bool => is_string($s));
            $teacher->setSpecialties(array_values($filteredSpecialties));
            $teacherStatus = $faker->randomElement(['在职', '离职', '停职']);
            $teacher->setTeacherStatus(is_string($teacherStatus) ? $teacherStatus : '在职');
            $teacher->setJoinDate(CarbonImmutable::createFromDate($faker->numberBetween(2015, 2023), $faker->numberBetween(1, 12), $faker->numberBetween(1, 28)));

            $createTime = CarbonImmutable::now()->modify('-' . $faker->numberBetween(5, 60) . ' days');
            $teacher->setCreateTime($createTime);
            $teacher->setUpdateTime($createTime->modify('+' . $faker->numberBetween(0, 5) . ' days'));

            $manager->persist($teacher);
            $this->addReference(self::TEACHER_REFERENCE_PREFIX . $i, $teacher);
        }

        $manager->flush();
    }
}
