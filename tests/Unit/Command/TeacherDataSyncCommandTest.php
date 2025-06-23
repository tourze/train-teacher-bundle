<?php

namespace Tourze\TrainTeacherBundle\Tests\Unit\Command;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\TrainTeacherBundle\Command\TeacherDataSyncCommand;
use Tourze\TrainTeacherBundle\Entity\Teacher;
use Tourze\TrainTeacherBundle\Repository\TeacherRepository;

/**
 * TeacherDataSyncCommand单元测试
 */
class TeacherDataSyncCommandTest extends TestCase
{
    private TeacherDataSyncCommand $command;
    private CommandTester $commandTester;
    private TeacherRepository&MockObject $teacherRepository;
    private EntityManagerInterface&MockObject $entityManager;

    protected function setUp(): void
    {
        $this->teacherRepository = $this->createMock(TeacherRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->command = new TeacherDataSyncCommand(
            $this->teacherRepository,
            $this->entityManager
        );

        $application = new Application();
        $application->add($this->command);
        $this->commandTester = new CommandTester($this->command);
    }

    public function test_execute_dry_run_mode(): void
    {
        $teachers = [
            $this->createTeacher('teacher_1', 'T001', '张三', '13800138000'),
            $this->createTeacher('teacher_2', 'T002', '李四', '13800138001'),
        ];

        $this->teacherRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($teachers);

        $this->entityManager
            ->expects($this->never())
            ->method('flush');

        $exitCode = $this->commandTester->execute([
            '--dry-run' => true,
        ]);

        $this->assertEquals(0, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('运行在预览模式', $output);
        $this->assertStringContainsString('数据同步检查完成', $output);
    }

    public function test_execute_with_valid_teachers(): void
    {
        $teachers = [
            $this->createTeacher('teacher_1', 'T001', '张三', '13800138000'),
            $this->createTeacher('teacher_2', 'T002', '李四', '13800138001'),
        ];

        $this->teacherRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($teachers);

        $exitCode = $this->commandTester->execute([]);

        $this->assertEquals(0, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('教师数据同步', $output);
        $this->assertStringContainsString('数据同步检查完成', $output);
    }

    public function test_execute_with_invalid_teachers_fix_data(): void
    {
        $teacher1 = $this->createTeacher('teacher_1', '', '张三', '13800138000'); // 缺少教师编号
        $teacher2 = $this->createTeacher('teacher_2', 'T002', '', '13800138001'); // 缺少姓名
        $teacher3 = $this->createTeacher('teacher_3', 'T003', '王五', 'invalid_phone'); // 无效电话

        $teachers = [$teacher1, $teacher2, $teacher3];

        $this->teacherRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($teachers);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $exitCode = $this->commandTester->execute([
            '--fix-data' => true,
        ]);

        $this->assertEquals(0, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('教师数据同步', $output);
        $this->assertStringContainsString('已修复', $output);
    }

    public function test_execute_check_duplicates(): void
    {
        $teachers = [
            $this->createTeacher('teacher_1', 'T001', '张三', '13800138000'),
            $this->createTeacher('teacher_2', 'T002', '李四', '13800138001'),
        ];

        $this->teacherRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($teachers);

        $exitCode = $this->commandTester->execute([
            '--check-duplicates' => true,
        ]);

        $this->assertEquals(0, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('检查重复数据', $output);
    }

    public function test_execute_update_status(): void
    {
        $teachers = [
            $this->createTeacher('teacher_1', 'T001', '张三', '13800138000'),
            $this->createTeacher('teacher_2', 'T002', '李四', '13800138001'),
        ];

        $this->teacherRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($teachers);

        $exitCode = $this->commandTester->execute([
            '--update-status' => true,
        ]);

        $this->assertEquals(0, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('教师数据同步', $output);
    }

    public function test_execute_with_exception(): void
    {
        $this->teacherRepository
            ->expects($this->once())
            ->method('findAll')
            ->willThrowException(new \Exception('数据库连接失败'));

        $exitCode = $this->commandTester->execute([]);

        $this->assertEquals(1, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->// TODO: 检查断言的期望值是否正确
        assertStringContainsString('数据同步失败', $output);
        $this->// TODO: 检查断言的期望值是否正确
        assertStringContainsString('数据库连接失败', $output);
    }

    public function test_execute_with_empty_teacher_list(): void
    {
        $this->teacherRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        $exitCode = $this->commandTester->execute([]);

        $this->assertEquals(0, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('教师数据同步', $output);
        $this->assertStringContainsString('数据同步检查完成', $output);
    }

    public function test_execute_all_options(): void
    {
        $teachers = [
            $this->createTeacher('teacher_1', 'T001', '张三', '13800138000'),
        ];

        $this->teacherRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($teachers);

        $exitCode = $this->commandTester->execute([
            '--dry-run' => true,
            '--fix-data' => true,
            '--check-duplicates' => true,
            '--update-status' => true,
        ]);

        $this->assertEquals(0, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('运行在预览模式', $output);
        $this->assertStringContainsString('检查重复数据', $output);
        $this->assertStringContainsString('数据同步检查完成', $output);
    }

    public function test_command_configuration(): void
    {
        $definition = $this->command->getDefinition();

        $this->assertTrue($definition->hasOption('dry-run'));
        $this->assertTrue($definition->hasOption('fix-data'));
        $this->assertTrue($definition->hasOption('check-duplicates'));
        $this->assertTrue($definition->hasOption('update-status'));

        $this->assertEquals('teacher:data:sync', $this->command->getName());
        $this->assertEquals('同步教师数据，检查数据一致性和完整性', $this->command->getDescription());
    }

    /**
     * 创建测试教师对象
     */
    private function createTeacher(string $id, string $code, string $name, string $phone): Teacher
    {
        $teacher = new Teacher();
        $teacher->setId($id);
        $teacher->setTeacherCode($code);
        $teacher->setTeacherName($name);
        $teacher->setPhone($phone);
        $teacher->setTeacherType('专职');
        $teacher->setGender('男');
        $teacher->setBirthDate(new \DateTimeImmutable('1980-01-01'));
        $teacher->setIdCard('110101198001011234');
        $teacher->setEducation('本科');
        $teacher->setMajor('安全工程');
        $teacher->setGraduateSchool('北京理工大学');
        $teacher->setGraduateDate(new \DateTimeImmutable('2002-07-01'));
        $teacher->setWorkExperience(20);
        $teacher->setTeacherStatus('在职');
        $teacher->setJoinDate(new \DateTimeImmutable('2005-03-01'));

        return $teacher;
    }
}
