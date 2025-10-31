<?php

namespace Tourze\TrainTeacherBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainTeacherBundle\Entity\Teacher;
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
        private readonly TeacherRepository $teacherRepository,
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
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $params = $this->parseInputParameters($input, $io);
        if (null === $params) {
            return Command::FAILURE;
        }

        // 显示基本信息
        $io->title('教师绩效计算');
        /** @var \DateTime $period */
        $period = $params['period'];
        $io->text('计算周期: ' . $period->format('Y年m月'));

        // 如果是预览模式，显示预览信息
        if (true === $params['isDryRun']) {
            $io->note('运行在预览模式，不会保存计算结果');
        }

        /** @var string|null $teacherId */
        $teacherId = $params['teacherId'];
        /** @var string|null $teacherType */
        $teacherType = $params['teacherType'];
        /** @var string|null $teacherStatus */
        $teacherStatus = $params['teacherStatus'];
        $teachers = $this->getTargetTeachers($teacherId, $teacherType, $teacherStatus);
        if ([] === $teachers) {
            $io->warning('没有找到符合条件的教师');

            return Command::SUCCESS;
        }

        $this->displayCalculationInfo($io, $params, $teachers);
        $calculationResults = $this->initializeResults(count($teachers));
        $calculationResults = $this->processTeachersBatch($teachers, $params, $calculationResults, $io);

        // 显示计算结果
        $this->displayCalculationResults($calculationResults, $io);

        if ($calculationResults['failed_teachers'] > 0) {
            $io->warning('部分教师绩效计算失败，请检查错误详情');

            return Command::FAILURE;
        }

        $io->success('教师绩效计算完成');

        return Command::SUCCESS;
    }

    /**
     * 解析输入参数
     * @return array<string, mixed>|null
     */
    private function parseInputParameters(InputInterface $input, SymfonyStyle $io): ?array
    {
        $periodStr = $input->getArgument('period');
        if (!is_string($periodStr)) {
            $io->error('无效的绩效周期参数');

            return null;
        }

        $period = \DateTime::createFromFormat('Y-m', $periodStr);
        if (false === $period) {
            $io->error('无效的绩效周期格式，请使用 YYYY-MM 格式');

            return null;
        }

        $period->setDate((int) $period->format('Y'), (int) $period->format('m'), 1);

        $teacherId = $input->getOption('teacher-id');
        $teacherType = $input->getOption('teacher-type');
        $teacherStatus = $input->getOption('teacher-status');
        $batchSize = $input->getOption('batch-size');

        return [
            'period' => $period,
            'teacherId' => is_string($teacherId) ? $teacherId : null,
            'teacherType' => is_string($teacherType) ? $teacherType : null,
            'teacherStatus' => is_string($teacherStatus) ? $teacherStatus : null,
            'force' => (bool) $input->getOption('force'),
            'isDryRun' => (bool) $input->getOption('dry-run'),
            'batchSize' => is_numeric($batchSize) ? (int) $batchSize : 50,
        ];
    }

    /**
     * 显示计算信息
     * @param array<string, mixed> $params
     * @param array<int, Teacher> $teachers
     */
    private function displayCalculationInfo(SymfonyStyle $io, array $params, array $teachers): void
    {
        $io->text('找到 ' . count($teachers) . ' 个教师需要计算绩效');
    }

    /**
     * 初始化结果
     * @return array<string, mixed>
     */
    private function initializeResults(int $totalTeachers): array
    {
        return [
            'total_teachers' => $totalTeachers,
            'calculated_teachers' => 0,
            'skipped_teachers' => 0,
            'failed_teachers' => 0,
            'errors' => [],
        ];
    }

    /**
     * 分批处理教师
     * @param array<int, Teacher> $teachers
     * @param array<string, mixed> $params
     * @param array<string, mixed> $calculationResults
     * @return array<string, mixed>
     */
    private function processTeachersBatch(array $teachers, array $params, array $calculationResults, SymfonyStyle $io): array
    {
        /** @var int $batchSize */
        $batchSize = $params['batchSize'];
        $batches = array_chunk($teachers, max(1, $batchSize));

        /** @var int $totalTeachers */
        $totalTeachers = $calculationResults['total_teachers'];
        $io->progressStart($totalTeachers);

        foreach ($batches as $batchIndex => $batch) {
            $calculationResults = $this->processSingleBatch($batch, $batchIndex, count($batches), $params, $calculationResults, $io);
        }

        $io->progressFinish();

        return $calculationResults;
    }

    /**
     * 处理单个批次
     * @param array<int, Teacher> $batch
     * @param array<string, mixed> $params
     * @param array<string, mixed> $calculationResults
     * @return array<string, mixed>
     */
    private function processSingleBatch(array $batch, int $batchIndex, int $totalBatches, array $params, array $calculationResults, SymfonyStyle $io): array
    {
        $io->section('处理批次 ' . ($batchIndex + 1) . "/{$totalBatches}");

        foreach ($batch as $teacher) {
            $result = $this->processTeacherCalculation($teacher, $params, $io);
            $calculationResults = $this->updateCalculationResults($calculationResults, $result);
            $io->progressAdvance();
        }

        $this->addBatchDelay($batchIndex, $totalBatches);

        return $calculationResults;
    }

    /**
     * 处理单个教师的绩效计算
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    private function processTeacherCalculation(Teacher $teacher, array $params, SymfonyStyle $io): array
    {
        /** @var \DateTime $period */
        $period = $params['period'];
        /** @var bool $force */
        $force = $params['force'];
        /** @var bool $isDryRun */
        $isDryRun = $params['isDryRun'];

        return $this->calculateTeacherPerformance($teacher, $period, $force, $isDryRun, $io);
    }

    /**
     * 更新计算结果统计
     * @param array<string, mixed> $calculationResults
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    private function updateCalculationResults(array $calculationResults, array $result): array
    {
        if (true === $result['calculated']) {
            return $this->incrementCalculatedCount($calculationResults);
        }

        if (true === $result['skipped']) {
            return $this->incrementSkippedCount($calculationResults);
        }

        if (true === $result['failed']) {
            return $this->incrementFailedCount($calculationResults, $result);
        }

        return $calculationResults;
    }

    /**
     * 增加计算成功计数
     * @param array<string, mixed> $calculationResults
     * @return array<string, mixed>
     */
    private function incrementCalculatedCount(array $calculationResults): array
    {
        $calculatedCount = \is_int($calculationResults['calculated_teachers']) ? $calculationResults['calculated_teachers'] : 0;
        $calculationResults['calculated_teachers'] = $calculatedCount + 1;

        return $calculationResults;
    }

    /**
     * 增加跳过计数
     * @param array<string, mixed> $calculationResults
     * @return array<string, mixed>
     */
    private function incrementSkippedCount(array $calculationResults): array
    {
        $skippedCount = \is_int($calculationResults['skipped_teachers']) ? $calculationResults['skipped_teachers'] : 0;
        $calculationResults['skipped_teachers'] = $skippedCount + 1;

        return $calculationResults;
    }

    /**
     * 增加失败计数
     * @param array<string, mixed> $calculationResults
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    private function incrementFailedCount(array $calculationResults, array $result): array
    {
        $failedCount = \is_int($calculationResults['failed_teachers']) ? $calculationResults['failed_teachers'] : 0;
        $calculationResults['failed_teachers'] = $failedCount + 1;

        if (null !== $result['error'] && \is_array($calculationResults['errors'])) {
            $calculationResults['errors'][] = $result['error'];
        }

        return $calculationResults;
    }

    /**
     * 添加批次间延迟
     */
    private function addBatchDelay(int $batchIndex, int $totalBatches): void
    {
        if ($batchIndex < $totalBatches - 1) {
            usleep(100000); // 0.1秒
        }
    }

    /**
     * 获取目标教师列表
     * @return array<int, Teacher>
     */
    private function getTargetTeachers(?string $teacherId, ?string $teacherType, ?string $teacherStatus): array
    {
        if (null !== $teacherId) {
            return $this->getSpecificTeacher($teacherId);
        }

        return $this->getTeachersByCriteria($teacherType, $teacherStatus);
    }

    /**
     * 获取指定教师
     * @return array<int, Teacher>
     */
    private function getSpecificTeacher(string $teacherId): array
    {
        try {
            $teacher = $this->teacherService->getTeacherById($teacherId);

            return [$teacher];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * 根据条件获取教师列表
     * @return array<int, Teacher>
     */
    private function getTeachersByCriteria(?string $teacherType, ?string $teacherStatus): array
    {
        $criteria = $this->buildSearchCriteria($teacherType, $teacherStatus);

        return $this->teacherRepository->findBy($criteria);
    }

    /**
     * 构建搜索条件
     * @return array<string, string>
     */
    private function buildSearchCriteria(?string $teacherType, ?string $teacherStatus): array
    {
        $criteria = [];
        if (null !== $teacherType) {
            $criteria['teacherType'] = $teacherType;
        }
        if (null !== $teacherStatus) {
            $criteria['teacherStatus'] = $teacherStatus;
        }

        return $criteria;
    }

    /**
     * 计算单个教师绩效
     * @return array<string, mixed>
     */
    private function calculateTeacherPerformance(
        Teacher $teacher,
        \DateTime $period,
        bool $force,
        bool $isDryRun,
        SymfonyStyle $io,
    ): array {
        $result = $this->initializeCalculationResult();

        try {
            $teacherId = $teacher->getId();
            $teacherName = $teacher->getTeacherName();

            if ($this->shouldSkipCalculation($teacherId, $period, $force)) {
                return $this->createSkippedResult($teacherName, $io);
            }

            return $this->executeCalculation($teacherId, $teacherName, $period, $isDryRun, $io);
        } catch (\Throwable $e) {
            return $this->createFailedResult($teacher, $e, $io);
        }
    }

    /**
     * 初始化计算结果
     * @return array<string, mixed>
     */
    private function initializeCalculationResult(): array
    {
        return [
            'calculated' => false,
            'skipped' => false,
            'failed' => false,
            'error' => null,
        ];
    }

    /**
     * 检查是否应该跳过计算
     */
    private function shouldSkipCalculation(string $teacherId, \DateTime $period, bool $force): bool
    {
        if ($force) {
            return false;
        }

        $existingPerformances = $this->performanceService->getPerformanceHistory($teacherId);

        return $this->hasExistingRecord($existingPerformances, $period);
    }

    /**
     * 检查是否已存在记录
     * @param array<int, object> $existingPerformances
     */
    private function hasExistingRecord(array $existingPerformances, \DateTime $period): bool
    {
        foreach ($existingPerformances as $performance) {
            if (\method_exists($performance, 'getPerformancePeriod')) {
                $performancePeriod = $performance->getPerformancePeriod();
                if ($performancePeriod instanceof \DateTimeInterface && $performancePeriod->format('Y-m') === $period->format('Y-m')) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * 创建跳过结果
     * @return array<string, mixed>
     */
    private function createSkippedResult(string $teacherName, SymfonyStyle $io): array
    {
        $io->text("跳过教师 {$teacherName} (已存在绩效记录)");

        return ['calculated' => false, 'skipped' => true, 'failed' => false, 'error' => null];
    }

    /**
     * 执行计算
     * @return array<string, mixed>
     */
    private function executeCalculation(string $teacherId, string $teacherName, \DateTime $period, bool $isDryRun, SymfonyStyle $io): array
    {
        if ($isDryRun) {
            return $this->createPreviewResult($teacherName, $io);
        }

        return $this->performActualCalculation($teacherId, $teacherName, $period, $io);
    }

    /**
     * 创建预览结果
     * @return array<string, mixed>
     */
    private function createPreviewResult(string $teacherName, SymfonyStyle $io): array
    {
        $io->text("预览: 将计算教师 {$teacherName} 的绩效");

        return ['calculated' => true, 'skipped' => false, 'failed' => false, 'error' => null];
    }

    /**
     * 执行实际计算
     * @return array<string, mixed>
     */
    private function performActualCalculation(string $teacherId, string $teacherName, \DateTime $period, SymfonyStyle $io): array
    {
        $performance = $this->performanceService->calculatePerformance($teacherId, $period);
        $io->text(sprintf(
            '✓ 教师 %s 绩效计算完成 - 分数: %.2f, 等级: %s',
            $teacherName,
            $performance->getPerformanceScore(),
            $performance->getPerformanceLevel()
        ));

        return ['calculated' => true, 'skipped' => false, 'failed' => false, 'error' => null];
    }

    /**
     * 创建失败结果
     * @return array<string, mixed>
     */
    private function createFailedResult(Teacher $teacher, \Throwable $e, SymfonyStyle $io): array
    {
        $error = [
            'teacher_id' => $teacher->getId(),
            'teacher_name' => $teacher->getTeacherName(),
            'error' => $e->getMessage(),
        ];

        $teacherName = $teacher->getTeacherName();
        $io->text("✗ 教师 {$teacherName} 绩效计算失败: " . $e->getMessage());

        return ['calculated' => false, 'skipped' => false, 'failed' => true, 'error' => $error];
    }

    /**
     * 显示计算结果
     * @param array<string, mixed> $calculationResults
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
                ['计算失败', $calculationResults['failed_teachers']],
            ]
        );

        // 显示详细错误信息
        /** @var array<int, array<string, string>> $errors */
        $errors = $calculationResults['errors'];
        if ([] !== $errors) {
            $io->section('计算失败详情');
            foreach ($errors as $error) {
                $io->text(sprintf(
                    '教师: %s (%s) - 错误: %s',
                    $error['teacher_name'],
                    $error['teacher_id'],
                    $error['error']
                ));
            }
        }

        // 显示成功率
        /** @var int $totalTeachers */
        $totalTeachers = $calculationResults['total_teachers'];
        /** @var int $calculatedTeachers */
        $calculatedTeachers = $calculationResults['calculated_teachers'];
        if ($totalTeachers > 0) {
            $successRate = ($calculatedTeachers / $totalTeachers) * 100;
            $io->text(sprintf('成功率: %.2f%%', $successRate));
        }
    }
}
