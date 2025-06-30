<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Unit\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\TrainTeacherBundle\Entity\Teacher;
use Tourze\TrainTeacherBundle\Exception\DuplicateTeacherException;
use Tourze\TrainTeacherBundle\Exception\TeacherNotFoundException;
use Tourze\TrainTeacherBundle\Repository\TeacherRepository;
use Tourze\TrainTeacherBundle\Service\TeacherService;

/**
 * TeacherService单元测试
 */
class TeacherServiceTest extends TestCase
{
    private TeacherService $teacherService;
    private EntityManagerInterface&MockObject $entityManager;
    private TeacherRepository&MockObject $teacherRepository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->teacherRepository = $this->createMock(TeacherRepository::class);
        
        $this->teacherService = new TeacherService(
            $this->entityManager,
            $this->teacherRepository
        );
    }

    public function test_create_teacher_success(): void
    {
        $teacherData = [
            'teacherName' => '张三',
            'teacherType' => '专职',
            'gender' => '男',
            'birthDate' => new \DateTimeImmutable('1980-01-01'),
            'idCard' => '110101198001011234',
            'phone' => '13800138000',
            'email' => 'zhangsan@example.com',
            'education' => '本科',
            'major' => '安全工程',
            'graduateSchool' => '北京理工大学',
            'graduateDate' => new \DateTimeImmutable('2002-07-01'),
            'workExperience' => 20,
            'specialties' => ['安全管理', '风险评估'],
            'teacherStatus' => '在职',
            'joinDate' => new \DateTimeImmutable('2005-03-01'),
        ];

        // Mock repository methods to return null (no duplicates)
        $this->teacherRepository
            ->expects($this->once())
            ->method('findByIdCard')
            ->with('110101198001011234')
            ->willReturn(null);

        $this->teacherRepository
            ->expects($this->once())
            ->method('findByPhone')
            ->with('13800138000')
            ->willReturn(null);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Teacher::class));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $teacher = $this->teacherService->createTeacher($teacherData);

        $this->assertInstanceOf(Teacher::class, $teacher);
        $this->assertEquals('张三', $teacher->getTeacherName());
        $this->assertEquals('专职', $teacher->getTeacherType());
        $this->assertEquals('男', $teacher->getGender());
        $this->assertEquals('110101198001011234', $teacher->getIdCard());
        $this->assertEquals('13800138000', $teacher->getPhone());
        $this->assertEquals('zhangsan@example.com', $teacher->getEmail());
    }

    public function test_create_teacher_with_teacher_code(): void
    {
        $teacherData = [
            'teacherCode' => 'T20240101001',
            'teacherName' => '张三',
            'teacherType' => '专职',
            'gender' => '男',
            'birthDate' => new \DateTimeImmutable('1980-01-01'),
            'idCard' => '110101198001011234',
            'phone' => '13800138000',
            'education' => '本科',
            'major' => '安全工程',
            'graduateSchool' => '北京理工大学',
            'graduateDate' => new \DateTimeImmutable('2002-07-01'),
            'workExperience' => 20,
            'teacherStatus' => '在职',
            'joinDate' => new \DateTimeImmutable('2005-03-01'),
        ];

        $this->teacherRepository
            ->expects($this->once())
            ->method('findByTeacherCode')
            ->with('T20240101001')
            ->willReturn(null);

        $this->teacherRepository
            ->expects($this->once())
            ->method('findByIdCard')
            ->willReturn(null);

        $this->teacherRepository
            ->expects($this->once())
            ->method('findByPhone')
            ->willReturn(null);

        $this->entityManager
            ->expects($this->once())
            ->method('persist');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $teacher = $this->teacherService->createTeacher($teacherData);

        $this->assertEquals('T20240101001', $teacher->getTeacherCode());
    }

    public function test_create_teacher_throws_exception_for_duplicate_teacher_code(): void
    {
        $teacherData = [
            'teacherCode' => 'T20240101001',
            'teacherName' => '张三',
        ];

        $existingTeacher = new Teacher();
        $this->teacherRepository
            ->expects($this->once())
            ->method('findByTeacherCode')
            ->with('T20240101001')
            ->willReturn($existingTeacher);

        $this->expectException(DuplicateTeacherException::class);
        $this->expectExceptionMessage('教师编号已存在: T20240101001');

        $this->teacherService->createTeacher($teacherData);
    }

    public function test_create_teacher_throws_exception_for_duplicate_id_card(): void
    {
        $teacherData = [
            'teacherName' => '张三',
            'idCard' => '110101198001011234',
        ];

        $existingTeacher = new Teacher();
        $this->teacherRepository
            ->expects($this->once())
            ->method('findByIdCard')
            ->with('110101198001011234')
            ->willReturn($existingTeacher);

        $this->expectException(DuplicateTeacherException::class);
        $this->expectExceptionMessage('身份证号已存在: 110101198001011234');

        $this->teacherService->createTeacher($teacherData);
    }

    public function test_create_teacher_throws_exception_for_duplicate_phone(): void
    {
        $teacherData = [
            'teacherName' => '张三',
            'idCard' => '110101198001011234',
            'phone' => '13800138000',
        ];

        $this->teacherRepository
            ->expects($this->once())
            ->method('findByIdCard')
            ->willReturn(null);

        $existingTeacher = new Teacher();
        $this->teacherRepository
            ->expects($this->once())
            ->method('findByPhone')
            ->with('13800138000')
            ->willReturn($existingTeacher);

        $this->expectException(DuplicateTeacherException::class);
        $this->expectExceptionMessage('手机号已存在: 13800138000');

        $this->teacherService->createTeacher($teacherData);
    }

    public function test_update_teacher_success(): void
    {
        $teacherId = 'teacher_123';
        $teacher = new Teacher();
        $teacher->setId($teacherId);
        $teacher->setTeacherName('张三');
        $teacher->setTeacherCode('T001');
        $teacher->setIdCard('110101198001011234');
        $teacher->setPhone('13800138000');

        $updateData = [
            'teacherName' => '李四',
            'email' => 'lisi@example.com',
        ];

        $this->teacherRepository
            ->expects($this->once())
            ->method('find')
            ->with($teacherId)
            ->willReturn($teacher);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $updatedTeacher = $this->teacherService->updateTeacher($teacherId, $updateData);

        $this->assertEquals('李四', $updatedTeacher->getTeacherName());
        $this->assertEquals('lisi@example.com', $updatedTeacher->getEmail());
    }

    public function test_update_teacher_throws_exception_for_nonexistent_teacher(): void
    {
        $teacherId = 'nonexistent_teacher';
        $updateData = ['teacherName' => '李四'];

        $this->teacherRepository
            ->expects($this->once())
            ->method('find')
            ->with($teacherId)
            ->willReturn(null);

        $this->expectException(TeacherNotFoundException::class);
        $this->expectExceptionMessage('教师不存在: nonexistent_teacher');

        $this->teacherService->updateTeacher($teacherId, $updateData);
    }

    public function test_update_teacher_throws_exception_for_duplicate_teacher_code(): void
    {
        $teacherId = 'teacher_123';
        $teacher = new Teacher();
        $teacher->setId($teacherId);
        $teacher->setTeacherCode('T001');

        $updateData = ['teacherCode' => 'T002'];

        $existingTeacher = new Teacher();
        $existingTeacher->setId('other_teacher');

        $this->teacherRepository
            ->expects($this->once())
            ->method('find')
            ->with($teacherId)
            ->willReturn($teacher);

        $this->teacherRepository
            ->expects($this->once())
            ->method('findByTeacherCode')
            ->with('T002')
            ->willReturn($existingTeacher);

        $this->expectException(DuplicateTeacherException::class);
        $this->expectExceptionMessage('教师编号已存在: T002');

        $this->teacherService->updateTeacher($teacherId, $updateData);
    }

    public function test_get_teacher_by_id_success(): void
    {
        $teacherId = 'teacher_123';
        $teacher = new Teacher();
        $teacher->setId($teacherId);

        $this->teacherRepository
            ->expects($this->once())
            ->method('find')
            ->with($teacherId)
            ->willReturn($teacher);

        $result = $this->teacherService->getTeacherById($teacherId);

        $this->assertSame($teacher, $result);
    }

    public function test_get_teacher_by_id_throws_exception_for_nonexistent_teacher(): void
    {
        $teacherId = 'nonexistent_teacher';

        $this->teacherRepository
            ->expects($this->once())
            ->method('find')
            ->with($teacherId)
            ->willReturn(null);

        $this->expectException(TeacherNotFoundException::class);
        $this->expectExceptionMessage('教师不存在: nonexistent_teacher');

        $this->teacherService->getTeacherById($teacherId);
    }

    public function test_get_teacher_by_code_success(): void
    {
        $teacherCode = 'T001';
        $teacher = new Teacher();
        $teacher->setTeacherCode($teacherCode);

        $this->teacherRepository
            ->expects($this->once())
            ->method('findByTeacherCode')
            ->with($teacherCode)
            ->willReturn($teacher);

        $result = $this->teacherService->getTeacherByCode($teacherCode);

        $this->assertSame($teacher, $result);
    }

    public function test_get_teacher_by_code_throws_exception_for_nonexistent_teacher(): void
    {
        $teacherCode = 'T999';

        $this->teacherRepository
            ->expects($this->once())
            ->method('findByTeacherCode')
            ->with($teacherCode)
            ->willReturn(null);

        $this->expectException(TeacherNotFoundException::class);
        $this->expectExceptionMessage('教师不存在: T999');

        $this->teacherService->getTeacherByCode($teacherCode);
    }

    public function test_get_teachers_by_type(): void
    {
        $type = '专职';
        $teachers = [new Teacher(), new Teacher()];

        $this->teacherRepository
            ->expects($this->once())
            ->method('findByTeacherType')
            ->with($type)
            ->willReturn($teachers);

        $result = $this->teacherService->getTeachersByType($type);

        $this->assertSame($teachers, $result);
    }

    public function test_get_teachers_by_status(): void
    {
        $status = '在职';
        $teachers = [new Teacher(), new Teacher()];

        $this->teacherRepository
            ->expects($this->once())
            ->method('findByTeacherStatus')
            ->with($status)
            ->willReturn($teachers);

        $result = $this->teacherService->getTeachersByStatus($status);

        $this->assertSame($teachers, $result);
    }

    public function test_change_teacher_status(): void
    {
        $teacherId = 'teacher_123';
        $teacher = new Teacher();
        $teacher->setId($teacherId);
        $teacher->setTeacherStatus('在职');

        $newStatus = '离职';
        $reason = '个人原因';

        $this->teacherRepository
            ->expects($this->once())
            ->method('find')
            ->with($teacherId)
            ->willReturn($teacher);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $result = $this->teacherService->changeTeacherStatus($teacherId, $newStatus, $reason);

        $this->assertEquals($newStatus, $result->getTeacherStatus());
    }

    public function test_search_teachers(): void
    {
        $keyword = '张三';
        $limit = 20;
        $teachers = [new Teacher(), new Teacher()];

        $this->teacherRepository
            ->expects($this->once())
            ->method('searchTeachers')
            ->with($keyword, $limit)
            ->willReturn($teachers);

        $result = $this->teacherService->searchTeachers($keyword, $limit);

        $this->assertSame($teachers, $result);
    }

    public function test_get_teacher_statistics(): void
    {
        $statistics = [
            'total' => 100,
            'fullTime' => 60,
            'partTime' => 40,
            'active' => 90,
        ];

        $this->teacherRepository
            ->expects($this->once())
            ->method('getTeacherStatistics')
            ->willReturn($statistics);

        $result = $this->teacherService->getTeacherStatistics();

        $this->assertSame($statistics, $result);
    }

    public function test_get_recent_teachers(): void
    {
        $limit = 10;
        $teachers = [new Teacher(), new Teacher()];

        $this->teacherRepository
            ->expects($this->once())
            ->method('getRecentTeachers')
            ->with($limit)
            ->willReturn($teachers);

        $result = $this->teacherService->getRecentTeachers($limit);

        $this->assertSame($teachers, $result);
    }

    public function test_delete_teacher(): void
    {
        $teacherId = 'teacher_123';
        $teacher = new Teacher();
        $teacher->setId($teacherId);

        $this->teacherRepository
            ->expects($this->once())
            ->method('find')
            ->with($teacherId)
            ->willReturn($teacher);

        $this->entityManager
            ->expects($this->once())
            ->method('remove')
            ->with($teacher);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->teacherService->deleteTeacher($teacherId);
    }

    public function test_delete_teacher_throws_exception_for_nonexistent_teacher(): void
    {
        $teacherId = 'nonexistent_teacher';

        $this->teacherRepository
            ->expects($this->once())
            ->method('find')
            ->with($teacherId)
            ->willReturn(null);

        $this->expectException(TeacherNotFoundException::class);
        $this->expectExceptionMessage('教师不存在: nonexistent_teacher');

        $this->teacherService->deleteTeacher($teacherId);
    }

    public function testClassExists(): void
    {
        $this->assertTrue(class_exists(TeacherService::class));
    }
} 