<?php

namespace Tourze\TrainTeacherBundle\Tests\Unit\Command;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\TrainTeacherBundle\Command\PerformanceCalculateCommand;
use Tourze\TrainTeacherBundle\Entity\Teacher;
use Tourze\TrainTeacherBundle\Entity\TeacherPerformance;
use Tourze\TrainTeacherBundle\Repository\TeacherRepository;
use Tourze\TrainTeacherBundle\Service\PerformanceService;
use Tourze\TrainTeacherBundle\Service\TeacherService;

/**
 * PerformanceCalculateCommand单元测试
 */
class PerformanceCalculateCommandTest extends TestCase
{
    private PerformanceCalculateCommand $command;
    private CommandTester $commandTester;
    private PerformanceService&MockObject $performanceService;
    private TeacherService&MockObject $teacherService;
    private TeacherRepository&MockObject $teacherRepository;

    protected function setUp(): void
    {
        $this->performanceService = $this->createMock(PerformanceService::class);
        $this->teacherService = $this->createMock(TeacherService::class);
        $this->teacherRepository = $this->createMock(TeacherRepository::class);

        $this->command = new PerformanceCalculateCommand(
            $this->performanceService,
            $this->teacherService,
            $this->teacherRepository
        );

        $application = new Application();
        $application->add($this->command);
        $this->commandTester = new CommandTester($this->command);
    }

    public function test_execute_with_default_period(): void
    {
        $teachers = [
            $this->createTeacher('teacher_1', 'T001', '张三'),
            $this->createTeacher('teacher_2', 'T002', '李四'),
        ];

        $this->teacherRepository
            ->expects($this->once())
            ->method('findBy')
            ->willReturn($teachers);

        $this->performanceService
            ->expects($this->exactly(2))
            ->method('calculatePerformance')
            ->willReturn($this->createMockPerformance());

        $exitCode = $this->commandTester->execute([]);

        $this->assertEquals(0, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('教师绩效计算', $output);
        $this->assertStringContainsString('教师绩效计算完成', $output);
    }

    public function test_execute_with_specific_period(): void
    {
        $teachers = [
            $this->createTeacher('teacher_1', 'T001', '张三'),
        ];

        $this->teacherRepository
            ->expects($this->once())
            ->method('findBy')
            ->willReturn($teachers);

        $this->performanceService
            ->expects($this->once())
            ->method('calculatePerformance')
            ->willReturn($this->createMockPerformance());

        $exitCode = $this->commandTester->execute([
            'period' => '2024-01',
        ]);

        $this->assertEquals(0, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('2024年01月', $output);
        $this->assertStringContainsString('教师绩效计算完成', $output);
    }

    public function test_execute_with_specific_teacher(): void
    {
        $teacher = $this->createTeacher('teacher_1', 'T001', '张三');

        $this->teacherService
            ->expects($this->once())
            ->method('getTeacherById')
            ->with('teacher_1')
            ->willReturn($teacher);

        $this->performanceService
            ->expects($this->once())
            ->method('calculatePerformance')
            ->willReturn($this->createMockPerformance());

        $exitCode = $this->commandTester->execute([
            '--teacher-id' => 'teacher_1',
        ]);

        $this->assertEquals(0, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('找到 1 个教师需要计算绩效', $output);
        $this->assertStringContainsString('教师绩效计算完成', $output);
    }

    public function test_execute_with_teacher_type_filter(): void
    {
        $teachers = [
            $this->createTeacher('teacher_1', 'T001', '张三'),
        ];

        $this->teacherRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(['teacherType' => 'full-time', 'teacherStatus' => 'active'])
            ->willReturn($teachers);

        $this->performanceService
            ->expects($this->once())
            ->method('calculatePerformance')
            ->willReturn($this->createMockPerformance());

        $exitCode = $this->commandTester->execute([
            '--teacher-type' => 'full-time',
        ]);

        $this->assertEquals(0, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('教师绩效计算完成', $output);
    }

    public function test_execute_dry_run_mode(): void
    {
        $teachers = [
            $this->createTeacher('teacher_1', 'T001', '张三'),
        ];

        $this->teacherRepository
            ->expects($this->once())
            ->method('findBy')
            ->willReturn($teachers);

        // 在dry-run模式下，不应该调用实际的计算方法
        $this->performanceService
            ->expects($this->never())
            ->method('calculatePerformance');

        $exitCode = $this->commandTester->execute([
            '--dry-run' => true,
        ]);

        $this->assertEquals(0, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('运行在预览模式', $output);
        $this->assertStringContainsString('教师绩效计算完成', $output);
    }

    public function test_execute_with_force_option(): void
    {
        $teachers = [
            $this->createTeacher('teacher_1', 'T001', '张三'),
        ];

        $this->teacherRepository
            ->expects($this->once())
            ->method('findBy')
            ->willReturn($teachers);

        $this->performanceService
            ->expects($this->once())
            ->method('calculatePerformance')
            ->willReturn($this->createMockPerformance());

        $exitCode = $this->commandTester->execute([
            '--force' => true,
        ]);

        $this->assertEquals(0, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('教师绩效计算完成', $output);
    }

    public function test_execute_with_batch_size(): void
    {
        $teachers = [
            $this->createTeacher('teacher_1', 'T001', '张三'),
            $this->createTeacher('teacher_2', 'T002', '李四'),
            $this->createTeacher('teacher_3', 'T003', '王五'),
        ];

        $this->teacherRepository
            ->expects($this->once())
            ->method('findBy')
            ->willReturn($teachers);

        $this->performanceService
            ->expects($this->exactly(3))
            ->method('calculatePerformance')
            ->willReturn($this->createMockPerformance());

        $exitCode = $this->commandTester->execute([
            '--batch-size' => 2,
        ]);

        $this->assertEquals(0, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('处理批次', $output);
        $this->assertStringContainsString('教师绩效计算完成', $output);
    }

    public function test_execute_with_invalid_period(): void
    {
        $exitCode = $this->commandTester->execute([
            'period' => 'invalid-period',
        ]);

        $this->assertEquals(1, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('无效的绩效周期格式', $output);
    }

    public function test_execute_with_nonexistent_teacher(): void
    {
        $this->teacherService
            ->expects($this->once())
            ->method('getTeacherById')
            ->with('nonexistent_teacher')
            ->willThrowException(new \Exception('教师不存在'));

        $exitCode = $this->commandTester->execute([
            '--teacher-id' => 'nonexistent_teacher',
        ]);

        $this->assertEquals(0, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('没有找到符合条件的教师', $output);
    }

    public function test_execute_with_empty_teacher_list(): void
    {
        $this->teacherRepository
            ->expects($this->once())
            ->method('findBy')
            ->willReturn([]);

        $exitCode = $this->commandTester->execute([]);

        $this->assertEquals(0, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('没有找到符合条件的教师', $output);
    }

    public function test_execute_with_calculation_exception(): void
    {
        $teachers = [
            $this->createTeacher('teacher_1', 'T001', '张三'),
        ];

        $this->teacherRepository
            ->expects($this->once())
            ->method('findBy')
            ->willReturn($teachers);

        $this->performanceService
            ->expects($this->once())
            ->method('calculatePerformance')
            ->willThrowException(new \Exception('计算失败'));

        $exitCode = $this->commandTester->execute([]);

        $this->assertEquals(1, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->// TODO: 检查断言的期望值是否正确
        assertStringContainsString('部分教师绩效计算失败', $output);
    }

    public function test_command_configuration(): void
    {
        $definition = $this->command->getDefinition();
        
        $this->assertTrue($definition->hasArgument('period'));
        $this->assertTrue($definition->hasOption('teacher-id'));
        $this->assertTrue($definition->hasOption('teacher-type'));
        $this->assertTrue($definition->hasOption('teacher-status'));
        $this->assertTrue($definition->hasOption('force'));
        $this->assertTrue($definition->hasOption('dry-run'));
        $this->assertTrue($definition->hasOption('batch-size'));
        
        $this->assertEquals('teacher:performance:calculate', $this->command->getName());
        $this->assertEquals('计算教师绩效，支持批量计算和单个教师计算', $this->command->getDescription());
    }

    /**
     * 创建测试教师对象
     */
    private function createTeacher(string $id, string $code, string $name): Teacher
    {
        $teacher = new Teacher();
        $teacher->setId($id);
        $teacher->setTeacherCode($code);
        $teacher->setTeacherName($name);
        $teacher->setTeacherType('专职');
        $teacher->setGender('男');
        $teacher->setBirthDate(new \DateTime('1980-01-01'));
        $teacher->setIdCard('110101198001011234');
        $teacher->setPhone('13800138000');
        $teacher->setEducation('本科');
        $teacher->setMajor('安全工程');
        $teacher->setGraduateSchool('北京理工大学');
        $teacher->setGraduateDate(new \DateTime('2002-07-01'));
        $teacher->setWorkExperience(20);
        $teacher->setTeacherStatus('在职');
        $teacher->setJoinDate(new \DateTime('2005-03-01'));
        
        return $teacher;
    }

    /**
     * 创建模拟绩效对象
     */
    private function createMockPerformance(): TeacherPerformance&MockObject
    {
        $performance = $this->createMock(TeacherPerformance::class);
        $performance->method('getPerformanceScore')->willReturn(4.5);
        $performance->method('getPerformanceLevel')->willReturn('优秀');
        return $performance;
    }
} 