<?php

namespace Tourze\TrainTeacherBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainTeacherBundle\Entity\Teacher;
use Tourze\TrainTeacherBundle\Repository\TeacherRepository;

/**
 * 教师数据同步命令
 * 用于定期同步教师数据，检查数据一致性和完整性
 */
#[AsCommand(
    name: self::NAME,
    description: '同步教师数据，检查数据一致性和完整性'
)]
class TeacherDataSyncCommand extends Command
{
    public const NAME = 'teacher:data:sync';

    public function __construct(
        private readonly TeacherRepository $teacherRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                '仅检查不执行实际同步操作'
            )
            ->addOption(
                'fix-data',
                null,
                InputOption::VALUE_NONE,
                '自动修复发现的数据问题'
            )
            ->addOption(
                'check-duplicates',
                null,
                InputOption::VALUE_NONE,
                '检查重复数据'
            )
            ->addOption(
                'update-status',
                null,
                InputOption::VALUE_NONE,
                '更新教师状态'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $params = $this->parseParameters($input, $io);
        $syncResults = $this->initializeSyncResults();

        try {
            $teachers = $this->teacherRepository->findAll();
            $syncResults['total_teachers'] = count($teachers);

            $syncResults = $this->processAllTeachers($teachers, $syncResults, $params, $io);

            if (true === $params['checkDuplicates']) {
                /** @var bool $isDryRun */
                $isDryRun = $params['isDryRun'];
                /** @var bool $fixData */
                $fixData = $params['fixData'];
                $syncResults = $this->checkDuplicateData($syncResults, $isDryRun, $fixData, $io);
            }

            if (true === $params['updateStatus']) {
                /** @var bool $isDryRun */
                $isDryRun = $params['isDryRun'];
                $syncResults = $this->updateTeacherStatus($syncResults, $isDryRun, $io);
            }

            $this->displaySyncResults($syncResults, $io);

            // 保存更改（如果不是预览模式且有修复）
            /** @var bool $isDryRun */
            $isDryRun = $params['isDryRun'];
            /** @var int $fixedIssues */
            $fixedIssues = $syncResults['fixed_issues'];
            if (false === $isDryRun && $fixedIssues > 0) {
                $this->entityManager->flush();
                $io->success('数据同步完成，已修复 ' . $fixedIssues . ' 个问题');
            } else {
                $io->success('数据同步检查完成');
            }

            if ([] !== $syncResults['errors']) {
                $io->warning('发现数据问题，但处理完成');
            }

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error('数据同步失败');
            $io->text('错误详情: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * 解析命令参数
     * @return array<string, mixed>
     */
    private function parseParameters(InputInterface $input, SymfonyStyle $io): array
    {
        $io->title('教师数据同步');

        $params = [
            'isDryRun' => (bool) $input->getOption('dry-run'),
            'fixData' => (bool) $input->getOption('fix-data'),
            'checkDuplicates' => (bool) $input->getOption('check-duplicates'),
            'updateStatus' => (bool) $input->getOption('update-status'),
        ];

        if ($params['isDryRun']) {
            $io->note('运行在预览模式，不会执行实际的数据修改操作');
        }

        return $params;
    }

    /**
     * 初始化同步结果
     * @return array<string, mixed>
     */
    private function initializeSyncResults(): array
    {
        return [
            'total_teachers' => 0,
            'checked_teachers' => 0,
            'fixed_issues' => 0,
            'duplicates_found' => 0,
            'status_updated' => 0,
            'errors' => [],
        ];
    }

    /**
     * 处理所有教师
     * @param array<int, Teacher> $teachers
     * @param array<string, mixed> $syncResults
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    private function processAllTeachers(array $teachers, array $syncResults, array $params, SymfonyStyle $io): array
    {
        /** @var int $totalTeachers */
        $totalTeachers = $syncResults['total_teachers'];
        $io->progressStart($totalTeachers);

        foreach ($teachers as $teacher) {
            /** @var bool $isDryRun */
            $isDryRun = $params['isDryRun'];
            /** @var bool $fixData */
            $fixData = $params['fixData'];
            $syncResults = $this->processTeacher($teacher, $syncResults, $isDryRun, $fixData, $io);
            $io->progressAdvance();
        }

        $io->progressFinish();

        return $syncResults;
    }

    /**
     * 处理单个教师数据
     * @param array<string, mixed> $syncResults
     * @return array<string, mixed>
     */
    private function processTeacher(
        Teacher $teacher,
        array $syncResults,
        bool $isDryRun,
        bool $fixData,
        SymfonyStyle $io,
    ): array {
        /** @var int $checkedTeachers */
        $checkedTeachers = $syncResults['checked_teachers'];
        $syncResults['checked_teachers'] = $checkedTeachers + 1;
        $issues = [];

        $result = $this->validateRequiredFields($teacher, $issues, $fixData, $isDryRun, $syncResults);
        $syncResults = $result['syncResults'];
        $issues = $result['issues'];

        $issues = $this->validateDataFormats($teacher, $issues);

        $result = $this->validateTeacherStatus($teacher, $issues, $fixData, $isDryRun, $syncResults);
        $syncResults = $result['syncResults'];
        $issues = $result['issues'];

        return $this->recordIssues($teacher, $issues, $syncResults);
    }

    /**
     * 验证必填字段
     * @param array<int, string> $issues
     * @param array<string, mixed> $syncResults
     * @return array{syncResults: array<string, mixed>, issues: array<int, string>}
     */
    private function validateRequiredFields(Teacher $teacher, array $issues, bool $fixData, bool $isDryRun, array $syncResults): array
    {
        $teacherName = $teacher->getTeacherName();
        if ('' === $teacherName) {
            $issues[] = '教师姓名为空';
        }

        $teacherCode = $teacher->getTeacherCode();
        if ('' === $teacherCode) {
            $issues[] = '教师编号为空';
            if ($fixData && !$isDryRun) {
                $teacher->setTeacherCode($this->generateTeacherCode());
                /** @var int $fixedIssues */
                $fixedIssues = $syncResults['fixed_issues'];
                $syncResults['fixed_issues'] = $fixedIssues + 1;
            }
        }

        $phone = $teacher->getPhone();
        if ('' === $phone) {
            $issues[] = '联系电话为空';
        }

        return ['syncResults' => $syncResults, 'issues' => $issues];
    }

    /**
     * 验证数据格式
     * @param array<int, string> $issues
     * @return array<int, string>
     */
    private function validateDataFormats(Teacher $teacher, array $issues): array
    {
        $phone = $teacher->getPhone();
        if ('' !== $phone && !$this->isValidPhone($phone)) {
            $issues[] = '联系电话格式不正确';
        }

        $email = $teacher->getEmail();
        if (null !== $email && '' !== $email && !$this->isValidEmail($email)) {
            $issues[] = '邮箱格式不正确';
        }

        $idCard = $teacher->getIdCard();
        if ('' !== $idCard && !$this->isValidIdCard($idCard)) {
            $issues[] = '身份证号格式不正确';
        }

        return $issues;
    }

    /**
     * 验证教师状态
     * @param array<int, string> $issues
     * @param array<string, mixed> $syncResults
     * @return array{syncResults: array<string, mixed>, issues: array<int, string>}
     */
    private function validateTeacherStatus(Teacher $teacher, array $issues, bool $fixData, bool $isDryRun, array $syncResults): array
    {
        $validStatuses = ['active', 'inactive', 'suspended', 'resigned'];
        $status = $teacher->getTeacherStatus();
        if (!in_array($status, $validStatuses, true)) {
            $issues[] = '教师状态无效: ' . $status;
            if ($fixData && !$isDryRun) {
                $teacher->setTeacherStatus('active');
                /** @var int $fixedIssues */
                $fixedIssues = $syncResults['fixed_issues'];
                $syncResults['fixed_issues'] = $fixedIssues + 1;
            }
        }

        return ['syncResults' => $syncResults, 'issues' => $issues];
    }

    /**
     * 记录问题
     * @param array<int, string> $issues
     * @param array<string, mixed> $syncResults
     * @return array<string, mixed>
     */
    private function recordIssues(Teacher $teacher, array $issues, array $syncResults): array
    {
        if ([] !== $issues) {
            /** @var array<int, array<string, mixed>> $errors */
            $errors = $syncResults['errors'];
            $errors[] = [
                'teacher_id' => $teacher->getId(),
                'teacher_name' => $teacher->getTeacherName(),
                'teacher_code' => $teacher->getTeacherCode(),
                'issues' => $issues,
            ];
            $syncResults['errors'] = $errors;
        }

        return $syncResults;
    }

    /**
     * 检查重复数据
     * @param array<string, mixed> $syncResults
     * @return array<string, mixed>
     */
    private function checkDuplicateData(
        array $syncResults,
        bool $isDryRun,
        bool $fixData,
        SymfonyStyle $io,
    ): array {
        $io->section('检查重复数据');

        // 检查重复的教师编号
        /** @var array<int, string> $duplicateTeacherCodes */
        $duplicateTeacherCodes = $this->teacherRepository->findDuplicateTeacherCodes();
        if ([] !== $duplicateTeacherCodes) {
            /** @var int $duplicatesFound */
            $duplicatesFound = $syncResults['duplicates_found'];
            $syncResults['duplicates_found'] = $duplicatesFound + count($duplicateTeacherCodes);
            $io->warning('发现重复的教师编号: ' . implode(', ', $duplicateTeacherCodes));
        }

        // 检查重复的身份证号
        /** @var array<int, string> $duplicateIdCards */
        $duplicateIdCards = $this->teacherRepository->findDuplicateIdCards();
        if ([] !== $duplicateIdCards) {
            /** @var int $duplicatesFound */
            $duplicatesFound = $syncResults['duplicates_found'];
            $syncResults['duplicates_found'] = $duplicatesFound + count($duplicateIdCards);
            $io->warning('发现重复的身份证号: ' . implode(', ', $duplicateIdCards));
        }

        // 检查重复的手机号
        /** @var array<int, string> $duplicatePhones */
        $duplicatePhones = $this->teacherRepository->findDuplicatePhones();
        if ([] !== $duplicatePhones) {
            /** @var int $duplicatesFound */
            $duplicatesFound = $syncResults['duplicates_found'];
            $syncResults['duplicates_found'] = $duplicatesFound + count($duplicatePhones);
            $io->warning('发现重复的手机号: ' . implode(', ', $duplicatePhones));
        }

        return $syncResults;
    }

    /**
     * 更新教师状态
     * @param array<string, mixed> $syncResults
     * @return array<string, mixed>
     */
    private function updateTeacherStatus(
        array $syncResults,
        bool $isDryRun,
        SymfonyStyle $io,
    ): array {
        $io->section('更新教师状态');

        // 检查长期未活跃的教师
        $inactiveTeachers = $this->teacherRepository->findInactiveTeachers(90); // 90天未活跃
        /** @var int $statusUpdated */
        $statusUpdated = $syncResults['status_updated'];
        foreach ($inactiveTeachers as $teacher) {
            if (!$isDryRun) {
                $teacher->setTeacherStatus('inactive');
                ++$statusUpdated;
            }
        }
        $syncResults['status_updated'] = $statusUpdated;

        if ($statusUpdated > 0) {
            $io->info('更新了 ' . $statusUpdated . ' 个教师的状态');
        }

        return $syncResults;
    }

    /**
     * 显示同步结果
     * @param array<string, mixed> $syncResults
     */
    private function displaySyncResults(array $syncResults, SymfonyStyle $io): void
    {
        $io->section('同步结果统计');

        $io->table(
            ['项目', '数量'],
            [
                ['总教师数', $syncResults['total_teachers']],
                ['已检查教师数', $syncResults['checked_teachers']],
                ['修复问题数', $syncResults['fixed_issues']],
                ['发现重复数据', $syncResults['duplicates_found']],
                ['状态更新数', $syncResults['status_updated']],
                ['发现错误数', count((array) $syncResults['errors'])],
            ]
        );

        // 显示详细错误信息
        /** @var array<int, array<string, mixed>> $errors */
        $errors = $syncResults['errors'];
        if ([] !== $errors) {
            $io->section('发现的问题详情');
            foreach ($errors as $error) {
                /** @var string $teacherName */
                $teacherName = $error['teacher_name'];
                /** @var string $teacherCode */
                $teacherCode = $error['teacher_code'];
                /** @var array<int, string> $issues */
                $issues = $error['issues'];
                $io->text(sprintf(
                    '教师: %s (%s) - %s',
                    $teacherName,
                    $teacherCode,
                    implode(', ', $issues)
                ));
            }
        }
    }

    /**
     * 生成教师编号
     */
    private function generateTeacherCode(): string
    {
        return 'T' . date('Ymd') . str_pad((string) mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * 验证手机号格式
     */
    private function isValidPhone(string $phone): bool
    {
        return 1 === preg_match('/^1[3-9]\d{9}$/', $phone);
    }

    /**
     * 验证邮箱格式
     */
    private function isValidEmail(string $email): bool
    {
        return false !== filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * 验证身份证号格式
     */
    private function isValidIdCard(string $idCard): bool
    {
        return 1 === preg_match('/^[1-9]\d{5}(18|19|20)\d{2}((0[1-9])|(1[0-2]))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$/', $idCard);
    }
}
