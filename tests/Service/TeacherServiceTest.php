<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainTeacherBundle\Entity\Teacher;
use Tourze\TrainTeacherBundle\Service\TeacherService;

/**
 * @internal
 */
#[CoversClass(TeacherService::class)]
#[RunTestsInSeparateProcesses]
final class TeacherServiceTest extends AbstractIntegrationTestCase
{
    private TeacherService $service;

    protected function onSetUp(): void
    {
        $service = self::getContainer()->get(TeacherService::class);
        self::assertInstanceOf(TeacherService::class, $service);
        $this->service = $service;
    }

    public function testGetTeacherStatisticsReturnsArray(): void
    {
        $result = $this->service->getTeacherStatistics();
        $this->assertArrayHasKey('total', $result);
    }

    public function testGetTeacherByIdThrowsExceptionForNonExistentTeacher(): void
    {
        $this->expectException(\Exception::class);
        $this->service->getTeacherById('non-existent-id');
    }

    public function testGetTeachersByStatusReturnsArray(): void
    {
        $result = $this->service->getTeachersByStatus('active');
        $this->assertGreaterThanOrEqual(0, count($result));
    }

    public function testGetRecentTeachersReturnsArray(): void
    {
        $result = $this->service->getRecentTeachers(5);
        $this->assertLessThanOrEqual(5, count($result));
    }

    public function testSearchTeachersReturnsArray(): void
    {
        $result = $this->service->searchTeachers('test');
        $this->assertGreaterThanOrEqual(0, count($result));
    }

    public function testChangeTeacherStatus(): void
    {
        $this->expectException(\Exception::class);
        $this->service->changeTeacherStatus('non-existent-id', 'active');
    }

    public function testCreateTeacher(): void
    {
        $serviceTimestamp = time() + 3000; // +3000 to avoid collision
        $teacherData = [
            'teacherCode' => "T{$serviceTimestamp}",
            'teacherName' => 'Test Teacher',
            'teacherType' => 'full-time',
            'gender' => 'male',
            'birthDate' => new \DateTimeImmutable('1990-01-01'),
            'idCard' => "110101199001011{$serviceTimestamp}",
            'phone' => "135{$serviceTimestamp}",
            'email' => "test{$serviceTimestamp}@example.com",
            'education' => '本科',
            'major' => '计算机科学与技术',
            'graduateSchool' => '北京大学',
            'graduateDate' => new \DateTimeImmutable('2012-06-30'),
            'workExperience' => 5,
            'teacherStatus' => 'active',
            'joinDate' => new \DateTimeImmutable('2020-01-01'),
        ];

        $result = $this->service->createTeacher($teacherData);
        $this->assertInstanceOf(Teacher::class, $result);
    }

    public function testDeleteTeacher(): void
    {
        $this->expectException(\Exception::class);
        $this->service->deleteTeacher('non-existent-id');
    }

    public function testUpdateTeacher(): void
    {
        $this->expectException(\Exception::class);
        $this->service->updateTeacher('non-existent-id', ['teacherName' => 'Updated Name']);
    }

    public function testGetTeacherByCode(): void
    {
        // 测试不存在的教师编号
        $this->expectException(\Exception::class);
        $this->service->getTeacherByCode('non-existent-code');
    }

    public function testGetTeachersByType(): void
    {
        $result = $this->service->getTeachersByType('full-time');
        $this->assertGreaterThanOrEqual(0, count($result));
    }

    public function testCreateAndRetrieveTeacher(): void
    {
        $serviceTimestamp = time() + 4000; // +4000 to avoid collision
        $teacherData = [
            'teacherCode' => "T{$serviceTimestamp}",
            'teacherName' => 'Test Teacher Complete',
            'teacherType' => 'full-time',
            'gender' => 'male',
            'birthDate' => new \DateTimeImmutable('1990-01-01'),
            'idCard' => "110101199001011{$serviceTimestamp}",
            'phone' => "136{$serviceTimestamp}",
            'email' => "complete{$serviceTimestamp}@example.com",
            'education' => '本科',
            'major' => '计算机科学与技术',
            'graduateSchool' => '北京大学',
            'graduateDate' => new \DateTimeImmutable('2012-06-30'),
            'workExperience' => 5,
            'teacherStatus' => 'active',
            'joinDate' => new \DateTimeImmutable('2020-01-01'),
            'specialties' => ['编程', '数据库'],
            'address' => '北京市朝阳区',
            'profilePhoto' => 'https://example.com/photo.jpg',
        ];

        // 创建教师
        $createdTeacher = $this->service->createTeacher($teacherData);
        $this->assertInstanceOf(Teacher::class, $createdTeacher);

        // 通过ID获取教师
        $foundTeacher = $this->service->getTeacherById($createdTeacher->getId());
        $this->assertSame($createdTeacher->getId(), $foundTeacher->getId());

        // 通过编号获取教师
        $foundByCode = $this->service->getTeacherByCode($createdTeacher->getTeacherCode());
        $this->assertSame($createdTeacher->getId(), $foundByCode->getId());

        // 更新教师信息
        $updateData = ['teacherName' => 'Updated Teacher Name'];
        $updatedTeacher = $this->service->updateTeacher($createdTeacher->getId(), $updateData);
        $this->assertSame('Updated Teacher Name', $updatedTeacher->getTeacherName());

        // 修改状态
        $statusUpdatedTeacher = $this->service->changeTeacherStatus($createdTeacher->getId(), 'inactive');
        $this->assertSame('inactive', $statusUpdatedTeacher->getTeacherStatus());

        // 删除教师
        $this->service->deleteTeacher($createdTeacher->getId());

        // 确认已删除
        $this->expectException(\Exception::class);
        $this->service->getTeacherById($createdTeacher->getId());
    }

    public function testCreateTeacherWithDuplicateCode(): void
    {
        $serviceTimestamp = time() + 5000;
        $teacherData = [
            'teacherCode' => "DUPLICATE{$serviceTimestamp}",
            'teacherName' => 'Test Teacher 1',
            'teacherType' => 'full-time',
            'gender' => 'male',
            'birthDate' => new \DateTimeImmutable('1990-01-01'),
            'idCard' => "110101199001011{$serviceTimestamp}",
            'phone' => "137{$serviceTimestamp}",
            'education' => '本科',
            'major' => '计算机科学与技术',
            'graduateSchool' => '北京大学',
            'graduateDate' => new \DateTimeImmutable('2012-06-30'),
            'workExperience' => 5,
            'teacherStatus' => 'active',
            'joinDate' => new \DateTimeImmutable('2020-01-01'),
        ];

        // 创建第一个教师
        $this->service->createTeacher($teacherData);

        // 尝试创建具有相同编号的第二个教师
        $teacherData['idCard'] = "110101199001012{$serviceTimestamp}"; // 不同身份证
        $teacherData['phone'] = "138{$serviceTimestamp}"; // 不同手机号
        $teacherData['teacherName'] = 'Test Teacher 2';

        $this->expectException(\Exception::class);
        $this->service->createTeacher($teacherData);
    }

    public function testCreateTeacherWithDuplicateIdCard(): void
    {
        $serviceTimestamp = time() + 6000;
        $duplicateIdCard = "110101199001016{$serviceTimestamp}";

        $teacherData1 = [
            'teacherCode' => "T1{$serviceTimestamp}",
            'teacherName' => 'Test Teacher 1',
            'teacherType' => 'full-time',
            'gender' => 'male',
            'birthDate' => new \DateTimeImmutable('1990-01-01'),
            'idCard' => $duplicateIdCard,
            'phone' => "139{$serviceTimestamp}",
            'education' => '本科',
            'major' => '计算机科学与技术',
            'graduateSchool' => '北京大学',
            'graduateDate' => new \DateTimeImmutable('2012-06-30'),
            'workExperience' => 5,
            'teacherStatus' => 'active',
            'joinDate' => new \DateTimeImmutable('2020-01-01'),
        ];

        // 创建第一个教师
        $this->service->createTeacher($teacherData1);

        // 尝试创建具有相同身份证的第二个教师
        $teacherData2 = $teacherData1;
        $teacherData2['teacherCode'] = "T2{$serviceTimestamp}";
        $teacherData2['phone'] = "140{$serviceTimestamp}";
        $teacherData2['teacherName'] = 'Test Teacher 2';

        $this->expectException(\Exception::class);
        $this->service->createTeacher($teacherData2);
    }

    public function testCreateTeacherWithDuplicatePhone(): void
    {
        $serviceTimestamp = time() + 7000;
        $duplicatePhone = "141{$serviceTimestamp}";

        $teacherData1 = [
            'teacherCode' => "T1{$serviceTimestamp}",
            'teacherName' => 'Test Teacher 1',
            'teacherType' => 'full-time',
            'gender' => 'male',
            'birthDate' => new \DateTimeImmutable('1990-01-01'),
            'idCard' => "110101199001017{$serviceTimestamp}",
            'phone' => $duplicatePhone,
            'education' => '本科',
            'major' => '计算机科学与技术',
            'graduateSchool' => '北京大学',
            'graduateDate' => new \DateTimeImmutable('2012-06-30'),
            'workExperience' => 5,
            'teacherStatus' => 'active',
            'joinDate' => new \DateTimeImmutable('2020-01-01'),
        ];

        // 创建第一个教师
        $this->service->createTeacher($teacherData1);

        // 尝试创建具有相同手机号的第二个教师
        $teacherData2 = $teacherData1;
        $teacherData2['teacherCode'] = "T2{$serviceTimestamp}";
        $teacherData2['idCard'] = "110101199001018{$serviceTimestamp}";
        $teacherData2['teacherName'] = 'Test Teacher 2';

        $this->expectException(\Exception::class);
        $this->service->createTeacher($teacherData2);
    }

    public function testUpdateTeacherWithValidData(): void
    {
        $serviceTimestamp = time() + 8000;
        $teacherData = [
            'teacherCode' => "UPDATE{$serviceTimestamp}",
            'teacherName' => 'Original Teacher',
            'teacherType' => 'full-time',
            'gender' => 'male',
            'birthDate' => new \DateTimeImmutable('1990-01-01'),
            'idCard' => "110101199001018{$serviceTimestamp}",
            'phone' => "142{$serviceTimestamp}",
            'education' => '本科',
            'major' => '计算机科学与技术',
            'graduateSchool' => '北京大学',
            'graduateDate' => new \DateTimeImmutable('2012-06-30'),
            'workExperience' => 5,
            'teacherStatus' => 'active',
            'joinDate' => new \DateTimeImmutable('2020-01-01'),
        ];

        $teacher = $this->service->createTeacher($teacherData);

        // 测试更新不冲突的字段
        $updateData = [
            'teacherName' => 'Updated Teacher',
            'email' => "updated{$serviceTimestamp}@example.com",
            'address' => '更新后的地址',
        ];

        $updatedTeacher = $this->service->updateTeacher($teacher->getId(), $updateData);
        $this->assertSame('Updated Teacher', $updatedTeacher->getTeacherName());
        $this->assertSame("updated{$serviceTimestamp}@example.com", $updatedTeacher->getEmail());
        $this->assertSame('更新后的地址', $updatedTeacher->getAddress());
    }

    public function testGenerateAutoTeacherCode(): void
    {
        $serviceTimestamp = time() + 9000;
        $teacherData = [
            // 不提供 teacherCode，应该自动生成
            'teacherName' => 'Auto Code Teacher',
            'teacherType' => 'full-time',
            'gender' => 'female',
            'birthDate' => new \DateTimeImmutable('1985-01-01'),
            'idCard' => "110101198501019{$serviceTimestamp}",
            'phone' => "143{$serviceTimestamp}",
            'education' => '硕士',
            'major' => '教育学',
            'graduateSchool' => '北京师范大学',
            'graduateDate' => new \DateTimeImmutable('2008-06-30'),
            'workExperience' => 15,
            'teacherStatus' => 'active',
            'joinDate' => new \DateTimeImmutable('2010-01-01'),
        ];

        $teacher = $this->service->createTeacher($teacherData);
        $this->assertNotEmpty($teacher->getTeacherCode());
        $this->assertStringStartsWith('T', $teacher->getTeacherCode());
    }
}
