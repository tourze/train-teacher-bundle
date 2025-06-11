<?php

namespace Tourze\TrainTeacherBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainTeacherBundle\Repository\TeacherRepository;
use Tourze\TrainTeacherBundle\Service\TeacherService;

/**
 * 教师数据同步命令
 * 用于定期同步教师数据，检查数据一致性和完整性
 */
#[AsCommand(
    name: 'teacher:data:sync',
    description: '同步教师数据，检查数据一致性和完整性'
)]
class TeacherDataSyncCommand extends Command
{
    public function __construct(
        private readonly TeacherService $teacherService,
        private readonly TeacherRepository $teacherRepository,
        private readonly EntityManagerInterface $entityManager
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
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $isDryRun = $input->getOption('dry-run');
        $fixData = $input->getOption('fix-data');
        $checkDuplicates = $input->getOption('check-duplicates');
        $updateStatus = $input->getOption('update-status');

        $io->title('教师数据同步');

        if ($isDryRun) {
            $io->note('运行在预览模式，不会执行实际的数据修改操作');
        }

        $syncResults = [
            'total_teachers' => 0,
            'checked_teachers' => 0,
            'fixed_issues' => 0,
            'duplicates_found' => 0,
            'status_updated' => 0,
            'errors' => []
        ];

        try {
            // 获取所有教师
            $teachers = $this->teacherRepository->findAll();
            $syncResults['total_teachers'] = count($teachers);

            $io->progressStart($syncResults['total_teachers']);

            foreach ($teachers as $teacher) {
                $this->processTeacher($teacher, $syncResults, $isDryRun, $fixData, $io);
                $io->progressAdvance();
            }

            $io->progressFinish();

            // 检查重复数据
            if ($checkDuplicates) {
                $this->checkDuplicateData($syncResults, $isDryRun, $fixData, $io);
            }

            // 更新教师状态
            if ($updateStatus) {
                $this->updateTeacherStatus($syncResults, $isDryRun, $io);
            }

            // 输出同步结果
            $this->displaySyncResults($syncResults, $io);

            if (!$isDryRun && $fixData && $syncResults['fixed_issues'] > 0) {
                $this->entityManager->flush();
                $io->success('数据同步完成，已修复 ' . $syncResults['fixed_issues'] . ' 个问题');
            } else {
                $io->success('数据同步检查完成');
            }

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $io->error('数据同步失败: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * 处理单个教师数据
     */
    private function processTeacher(
        $teacher,
        array &$syncResults,
        bool $isDryRun,
        bool $fixData,
        SymfonyStyle $io
    ): void {
        $syncResults['checked_teachers']++;
        $issues = [];

        // 检查必填字段
        if (empty($teacher->getTeacherName())) {
            $issues[] = '教师姓名为空';
        }

        if (empty($teacher->getTeacherCode())) {
            $issues[] = '教师编号为空';
            if ($fixData && !$isDryRun) {
                $teacher->setTeacherCode($this->generateTeacherCode());
                $syncResults['fixed_issues']++;
            }
        }

        if (empty($teacher->getPhone())) {
            $issues[] = '联系电话为空';
        }

        // 检查数据格式
        if ($teacher->getPhone() && !$this->isValidPhone($teacher->getPhone())) {
            $issues[] = '联系电话格式不正确';
        }

        if ($teacher->getEmail() && !$this->isValidEmail($teacher->getEmail())) {
            $issues[] = '邮箱格式不正确';
        }

        if ($teacher->getIdCard() && !$this->isValidIdCard($teacher->getIdCard())) {
            $issues[] = '身份证号格式不正确';
        }

        // 检查教师状态
        if (!in_array($teacher->getTeacherStatus(), ['active', 'inactive', 'suspended', 'resigned'])) {
            $issues[] = '教师状态无效: ' . $teacher->getTeacherStatus();
            if ($fixData && !$isDryRun) {
                $teacher->setTeacherStatus('active');
                $syncResults['fixed_issues']++;
            }
        }

        // 记录问题
        if (!empty($issues)) {
            $syncResults['errors'][] = [
                'teacher_id' => $teacher->getId(),
                'teacher_name' => $teacher->getTeacherName(),
                'teacher_code' => $teacher->getTeacherCode(),
                'issues' => $issues
            ];
        }
    }

    /**
     * 检查重复数据
     */
    private function checkDuplicateData(
        array &$syncResults,
        bool $isDryRun,
        bool $fixData,
        SymfonyStyle $io
    ): void {
        $io->section('检查重复数据');

        // 检查重复的教师编号
        $duplicateTeacherCodes = $this->teacherRepository->findDuplicateTeacherCodes();
        if (!empty($duplicateTeacherCodes)) {
            $syncResults['duplicates_found'] += count($duplicateTeacherCodes);
            $io->warning('发现重复的教师编号: ' . implode(', ', $duplicateTeacherCodes));
        }

        // 检查重复的身份证号
        $duplicateIdCards = $this->teacherRepository->findDuplicateIdCards();
        if (!empty($duplicateIdCards)) {
            $syncResults['duplicates_found'] += count($duplicateIdCards);
            $io->warning('发现重复的身份证号: ' . implode(', ', $duplicateIdCards));
        }

        // 检查重复的手机号
        $duplicatePhones = $this->teacherRepository->findDuplicatePhones();
        if (!empty($duplicatePhones)) {
            $syncResults['duplicates_found'] += count($duplicatePhones);
            $io->warning('发现重复的手机号: ' . implode(', ', $duplicatePhones));
        }
    }

    /**
     * 更新教师状态
     */
    private function updateTeacherStatus(
        array &$syncResults,
        bool $isDryRun,
        SymfonyStyle $io
    ): void {
        $io->section('更新教师状态');

        // 检查长期未活跃的教师
        $inactiveTeachers = $this->teacherRepository->findInactiveTeachers(90); // 90天未活跃
        foreach ($inactiveTeachers as $teacher) {
            if (!$isDryRun) {
                $teacher->setTeacherStatus('inactive');
                $syncResults['status_updated']++;
            }
        }

        if ($syncResults['status_updated'] > 0) {
            $io->info('更新了 ' . $syncResults['status_updated'] . ' 个教师的状态');
        }
    }

    /**
     * 显示同步结果
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
                ['发现错误数', count($syncResults['errors'])]
            ]
        );

        // 显示详细错误信息
        if (!empty($syncResults['errors'])) {
            $io->section('发现的问题详情');
            foreach ($syncResults['errors'] as $error) {
                $io->text(sprintf(
                    '教师: %s (%s) - %s',
                    $error['teacher_name'],
                    $error['teacher_code'],
                    implode(', ', $error['issues'])
                ));
            }
        }
    }

    /**
     * 生成教师编号
     */
    private function generateTeacherCode(): string
    {
        return 'T' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * 验证手机号格式
     */
    private function isValidPhone(string $phone): bool
    {
        return preg_match('/^1[3-9]\d{9}$/', $phone);
    }

    /**
     * 验证邮箱格式
     */
    private function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * 验证身份证号格式
     */
    private function isValidIdCard(string $idCard): bool
    {
        return preg_match('/^[1-9]\d{5}(18|19|20)\d{2}((0[1-9])|(1[0-2]))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$/', $idCard);
    }
} 