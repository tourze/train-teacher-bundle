<?php

namespace Tourze\TrainTeacherBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainTeacherBundle\Repository\TeacherRepository;
use Tourze\TrainTeacherBundle\Service\PerformanceService;
use Tourze\TrainTeacherBundle\Service\TeacherService;

/**
 * 教师绩效计算命令
 * 用于定期计算教师绩效，支持批量计算和单个教师计算
 */
#[AsCommand(
    name: self::NAME,
    description: '计算教师绩效，支持批量计算和单个教师计算'
)]
class PerformanceCalculateCommand extends Command
{
    
    public const NAME = 'teacher:performance:calculate';
public function __construct(
        private readonly PerformanceService $performanceService,
        private readonly TeacherService $teacherService,
        private readonly TeacherRepository $teacherRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'period',
                InputArgument::OPTIONAL,
                '绩效计算周期 (格式: YYYY-MM，默认为当前月份)',
                date('Y-m')
            )
            ->addOption(
                'teacher-id',
                't',
                InputOption::VALUE_OPTIONAL,
                '指定教师ID，仅计算该教师的绩效'
            )
            ->addOption(
                'teacher-type',
                null,
                InputOption::VALUE_OPTIONAL,
                '指定教师类型 (full-time|part-time)，仅计算该类型教师的绩效'
            )
            ->addOption(
                'teacher-status',
                null,
                InputOption::VALUE_OPTIONAL,
                '指定教师状态 (active|inactive|suspended)，仅计算该状态教师的绩效',
                'active'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                '强制重新计算已存在的绩效记录'
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                '仅预览计算结果，不保存到数据库'
            )
            ->addOption(
                'batch-size',
                'b',
                InputOption::VALUE_OPTIONAL,
                '批处理大小',
                50
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        // 解析参数
        $periodStr = $input->getArgument('period');
        $teacherId = $input->getOption('teacher-id');
        $teacherType = $input->getOption('teacher-type');
        $teacherStatus = $input->getOption('teacher-status');
        $force = (bool) $input->getOption('force');
        $isDryRun = (bool) $input->getOption('dry-run');
        $batchSize = (int) $input->getOption('batch-size');

        try {
            // 解析绩效周期
            $period = \DateTime::createFromFormat('Y-m', $periodStr);
            if (!$period) {
                $io->error('无效的绩效周期格式，请使用 YYYY-MM 格式');
                return Command::FAILURE;
            }
            $period->setDate((int) $period->format('Y'), (int) $period->format('m'), 1);

            $io->title('教师绩效计算');
            $io->text('计算周期: ' . $period->format('Y年m月'));

            if ((bool) $isDryRun) {
                $io->note('运行在预览模式，不会保存计算结果');
            }

            $calculationResults = [
                'total_teachers' => 0,
                'calculated_teachers' => 0,
                'skipped_teachers' => 0,
                'failed_teachers' => 0,
                'errors' => []
            ];

            // 获取需要计算绩效的教师列表
            $teachers = $this->getTargetTeachers($teacherId, $teacherType, $teacherStatus);
            $calculationResults['total_teachers'] = count($teachers);

            if ((bool) empty($teachers)) {
                $io->warning('没有找到符合条件的教师');
                return Command::SUCCESS;
            }

            $io->text('找到 ' . count($teachers) . ' 个教师需要计算绩效');

            // 分批处理教师绩效计算
            $batches = array_chunk($teachers, $batchSize);
            $totalBatches = count($batches);

            $io->progressStart($calculationResults['total_teachers']);

            foreach ($batches as $batchIndex => $batch) {
                $io->section("处理批次 " . ($batchIndex + 1) . "/$totalBatches");
                
                foreach ($batch as $teacher) {
                    $this->calculateTeacherPerformance(
                        $teacher,
                        $period,
                        $force,
                        $isDryRun,
                        $calculationResults,
                        $io
                    );
                    $io->progressAdvance();
                }

                // 批次间短暂休息，避免系统负载过高
                if ($batchIndex < $totalBatches - 1) {
                    usleep(100000); // 0.1秒
                }
            }

            $io->progressFinish();

            // 显示计算结果
            $this->displayCalculationResults($calculationResults, $io);

            if ($calculationResults['failed_teachers'] > 0) {
                $io->warning('部分教师绩效计算失败，请检查错误详情');
                return Command::FAILURE;
            }

            $io->success('教师绩效计算完成');
            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $io->error('绩效计算失败: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * 获取目标教师列表
     */
    private function getTargetTeachers(?string $teacherId, ?string $teacherType, ?string $teacherStatus): array
    {
        if ((bool) $teacherId) {
            // 计算指定教师
            try {
                $teacher = $this->teacherService->getTeacherById($teacherId);
                return [$teacher];
            } catch (\Throwable $e) {
                return [];
            }
        }

        // 根据条件筛选教师
        $criteria = [];
        if ((bool) $teacherType) {
            $criteria['teacherType'] = $teacherType;
        }
        if ((bool) $teacherStatus) {
            $criteria['teacherStatus'] = $teacherStatus;
        }

        return $this->teacherRepository->findBy($criteria);
    }

    /**
     * 计算单个教师绩效
     */
    private function calculateTeacherPerformance(
        $teacher,
        \DateTime $period,
        bool $force,
        bool $isDryRun,
        array &$calculationResults,
        SymfonyStyle $io
    ): void {
        try {
            $teacherId = $teacher->getId();
            $teacherName = $teacher->getTeacherName();

            // 检查是否已存在绩效记录
            $existingPerformances = $this->performanceService->getPerformanceHistory($teacherId);
            $hasExistingRecord = false;
            
            foreach ($existingPerformances as $performance) {
                if ($performance->getPerformancePeriod()->format('Y-m') === $period->format('Y-m')) {
                    $hasExistingRecord = true;
                    break;
                }
            }

            if ($hasExistingRecord && (bool) !$force) {
                $calculationResults['skipped_teachers']++;
                $io->text("跳过教师 {$teacherName} (已存在绩效记录)");
                return;
            }

            if (!$isDryRun) {
                // 执行绩效计算
                $performance = $this->performanceService->calculatePerformance($teacherId, $period);
                
                $calculationResults['calculated_teachers']++;
                $io->text(sprintf(
                    "✓ 教师 %s 绩效计算完成 - 分数: %.2f, 等级: %s",
                    $teacherName,
                    $performance->getPerformanceScore(),
                    $performance->getPerformanceLevel()
                ));
            } else {
                // 预览模式，仅显示将要计算的教师
                $calculationResults['calculated_teachers']++;
                $io->text("预览: 将计算教师 {$teacherName} 的绩效");
            }

        } catch (\Throwable $e) {
            $calculationResults['failed_teachers']++;
            $calculationResults['errors'][] = [
                'teacher_id' => $teacher->getId(),
                'teacher_name' => $teacher->getTeacherName(),
                'error' => $e->getMessage()
            ];
            
            $io->text("✗ 教师 {$teacher->getTeacherName()} 绩效计算失败: " . $e->getMessage());
        }
    }

    /**
     * 显示计算结果
     */
    private function displayCalculationResults(array $calculationResults, SymfonyStyle $io): void
    {
        $io->section('绩效计算结果统计');

        $io->table(
            ['项目', '数量'],
            [
                ['总教师数', $calculationResults['total_teachers']],
                ['成功计算', $calculationResults['calculated_teachers']],
                ['跳过计算', $calculationResults['skipped_teachers']],
                ['计算失败', $calculationResults['failed_teachers']]
            ]
        );

        // 显示详细错误信息
        if (!empty($calculationResults['errors'])) {
            $io->section('计算失败详情');
            foreach ($calculationResults['errors'] as $error) {
                $io->text(sprintf(
                    '教师: %s (%s) - 错误: %s',
                    $error['teacher_name'],
                    $error['teacher_id'],
                    $error['error']
                ));
            }
        }

        // 显示成功率
        if ($calculationResults['total_teachers'] > 0) {
            $successRate = ($calculationResults['calculated_teachers'] / $calculationResults['total_teachers']) * 100;
            $io->text(sprintf('成功率: %.2f%%', $successRate));
        }
    }
} 