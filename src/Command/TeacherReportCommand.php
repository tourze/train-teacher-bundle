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
use Tourze\TrainTeacherBundle\Entity\TeacherPerformance;
use Tourze\TrainTeacherBundle\Exception\InvalidPeriodFormatException;
use Tourze\TrainTeacherBundle\Exception\InvalidReportTypeException;
use Tourze\TrainTeacherBundle\Exception\UnsupportedOutputFormatException;
use Tourze\TrainTeacherBundle\Helper\ReportCsvFormatter;
use Tourze\TrainTeacherBundle\Repository\TeacherRepository;
use Tourze\TrainTeacherBundle\Service\EvaluationService;
use Tourze\TrainTeacherBundle\Service\PerformanceService;
use Tourze\TrainTeacherBundle\Service\TeacherService;

/**
 * 教师报告生成命令
 * 用于生成各种教师相关的报告，包括绩效报告、评价报告、统计报告等
 */
#[AsCommand(
    name: self::NAME,
    description: '生成教师报告，支持多种报告类型'
)]
class TeacherReportCommand extends Command
{
    public const NAME = 'teacher:report:generate';

    public function __construct(
        private readonly TeacherService $teacherService,
        private readonly EvaluationService $evaluationService,
        private readonly PerformanceService $performanceService,
        private readonly TeacherRepository $teacherRepository,
        private readonly ReportCsvFormatter $csvFormatter,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'report-type',
                InputArgument::REQUIRED,
                '报告类型 (performance|evaluation|statistics|summary)'
            )
            ->addOption(
                'teacher-id',
                't',
                InputOption::VALUE_OPTIONAL,
                '指定教师ID，生成该教师的个人报告'
            )
            ->addOption(
                'period',
                'p',
                InputOption::VALUE_OPTIONAL,
                '报告周期 (格式: YYYY-MM 或 YYYY)，默认为当前月份',
                date('Y-m')
            )
            ->addOption(
                'output-format',
                'f',
                InputOption::VALUE_OPTIONAL,
                '输出格式 (json|csv|html|pdf)',
                'json'
            )
            ->addOption(
                'output-file',
                'o',
                InputOption::VALUE_OPTIONAL,
                '输出文件路径，不指定则输出到控制台'
            )
            ->addOption(
                'teacher-type',
                null,
                InputOption::VALUE_OPTIONAL,
                '教师类型筛选 (full-time|part-time)'
            )
            ->addOption(
                'teacher-status',
                null,
                InputOption::VALUE_OPTIONAL,
                '教师状态筛选 (active|inactive|suspended)',
                'active'
            )
            ->addOption(
                'include-details',
                null,
                InputOption::VALUE_NONE,
                '包含详细信息'
            )
            ->addOption(
                'top-n',
                null,
                InputOption::VALUE_OPTIONAL,
                '仅显示前N名（用于排名报告）',
                10
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $params = $this->parseInputParameters($input);
            $validationResult = $this->validateInputParameters($params, $io);
            if (Command::SUCCESS !== $validationResult) {
                return $validationResult;
            }

            return $this->executeReportGeneration($params, $io);
        } catch (\Throwable $e) {
            $io->error('报告生成失败: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * 解析输入参数
     * @return array{
     *     reportType: string,
     *     teacherId: string|null,
     *     period: string,
     *     outputFormat: string,
     *     outputFile: string|null,
     *     teacherType: string|null,
     *     teacherStatus: string|null,
     *     includeDetails: bool,
     *     topN: int
     * }
     */
    private function parseInputParameters(InputInterface $input): array
    {
        return [
            'reportType' => $this->getStringArgument($input, 'report-type', ''),
            'teacherId' => $this->getStringOption($input, 'teacher-id'),
            'period' => $this->getStringOption($input, 'period') ?? date('Y-m'),
            'outputFormat' => $this->getStringOption($input, 'output-format') ?? 'json',
            'outputFile' => $this->getStringOption($input, 'output-file'),
            'teacherType' => $this->getStringOption($input, 'teacher-type'),
            'teacherStatus' => $this->getStringOption($input, 'teacher-status'),
            'includeDetails' => (bool) $input->getOption('include-details'),
            'topN' => $this->getIntOption($input, 'top-n', 10),
        ];
    }

    /**
     * 安全获取字符串参数
     */
    private function getStringArgument(InputInterface $input, string $name, string $default): string
    {
        $value = $input->getArgument($name);

        return is_string($value) ? $value : $default;
    }

    /**
     * 安全获取字符串选项
     */
    private function getStringOption(InputInterface $input, string $name): ?string
    {
        $value = $input->getOption($name);

        return is_string($value) ? $value : null;
    }

    /**
     * 安全获取整数选项
     */
    private function getIntOption(InputInterface $input, string $name, int $default): int
    {
        $value = $input->getOption($name);

        return is_numeric($value) ? (int) $value : $default;
    }

    /**
     * 验证输入参数
     * @param array{
     *     reportType: string,
     *     teacherId: string|null,
     *     period: string,
     *     outputFormat: string,
     *     outputFile: string|null,
     *     teacherType: string|null,
     *     teacherStatus: string|null,
     *     includeDetails: bool,
     *     topN: int
     * } $params
     */
    private function validateInputParameters(array $params, SymfonyStyle $io): int
    {
        if (!in_array($params['reportType'], ['performance', 'evaluation', 'statistics', 'summary'], true)) {
            $io->error('无效的报告类型。支持的类型: performance, evaluation, statistics, summary');

            return Command::FAILURE;
        }

        if (!in_array($params['outputFormat'], ['json', 'csv', 'html', 'pdf'], true)) {
            $io->error('无效的输出格式。支持的格式: json, csv, html, pdf');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * 执行报告生成
     * @param array{
     *     reportType: string,
     *     teacherId: string|null,
     *     period: string,
     *     outputFormat: string,
     *     outputFile: string|null,
     *     teacherType: string|null,
     *     teacherStatus: string|null,
     *     includeDetails: bool,
     *     topN: int
     * } $params
     */
    private function executeReportGeneration(array $params, SymfonyStyle $io): int
    {
        $io->title('教师报告生成');
        $this->displayReportInfo($params, $io);

        $reportData = $this->generateReportData(
            $params['reportType'],
            $params['teacherId'],
            $params['period'],
            $params['teacherType'],
            $params['teacherStatus'],
            $params['includeDetails'],
            $params['topN']
        );

        if ([] === $reportData) {
            $io->warning('没有找到符合条件的数据');

            return Command::SUCCESS;
        }

        $formattedOutput = $this->formatOutput($reportData, $params['outputFormat'], $params['reportType']);
        $this->handleOutput($formattedOutput, $params, $io);

        return Command::SUCCESS;
    }

    /**
     * 显示报告信息
     * @param array{
     *     reportType: string,
     *     period: string,
     *     outputFormat: string
     * } $params
     */
    private function displayReportInfo(array $params, SymfonyStyle $io): void
    {
        $io->text("生成报告类型: {$params['reportType']}");
        $io->text("报告周期: {$params['period']}");
        $io->text("输出格式: {$params['outputFormat']}");
    }

    /**
     * 处理输出
     * @param array{
     *     outputFile: string|null,
     *     outputFormat: string
     * } $params
     */
    private function handleOutput(string $formattedOutput, array $params, SymfonyStyle $io): void
    {
        if (null !== $params['outputFile']) {
            $this->saveToFile($formattedOutput, $params['outputFile'], $params['outputFormat']);
            $io->success("报告已保存到: {$params['outputFile']}");
        } else {
            $this->displayOutput($formattedOutput, $params['outputFormat'], $io);
        }
    }

    /**
     * 生成报告数据
     * @return array<string, mixed>
     */
    private function generateReportData(
        string $reportType,
        ?string $teacherId,
        string $period,
        ?string $teacherType,
        ?string $teacherStatus,
        bool $includeDetails,
        int $topN,
    ): array {
        switch ($reportType) {
            case 'performance':
                return $this->generatePerformanceReport($teacherId, $period, $teacherType, $teacherStatus, $includeDetails, $topN);

            case 'evaluation':
                return $this->generateEvaluationReport($teacherId, $period, $teacherType, $teacherStatus, $includeDetails);

            case 'statistics':
                return $this->generateStatisticsReport($period, $teacherType, $teacherStatus);

            case 'summary':
                return $this->generateSummaryReport($period, $teacherType, $teacherStatus, $includeDetails);

            default:
                throw new InvalidReportTypeException('不支持的报告类型: ' . $reportType);
        }
    }

    /**
     * 生成绩效报告
     * @return array<string, mixed>
     */
    private function generatePerformanceReport(
        ?string $teacherId,
        string $period,
        ?string $teacherType,
        ?string $teacherStatus,
        bool $includeDetails,
        int $topN,
    ): array {
        if (null !== $teacherId) {
            return $this->performanceService->generatePerformanceReport($teacherId);
        }

        return $this->generateBatchPerformanceReport($period, $teacherType, $teacherStatus, $includeDetails, $topN);
    }

    /**
     * 生成批量绩效报告
     * @return array<string, mixed>
     */
    private function generateBatchPerformanceReport(
        string $period,
        ?string $teacherType,
        ?string $teacherStatus,
        bool $includeDetails,
        int $topN,
    ): array {
        $periodDate = $this->parsePeriod($period);
        $ranking = $this->performanceService->getPerformanceRankingByPeriod($periodDate, $topN);

        $report = $this->createPerformanceReportStructure($period, $ranking);
        $report['ranking'] = $this->processPerformanceRanking($ranking, $teacherType, $teacherStatus, $includeDetails);

        return $report;
    }

    /**
     * 创建绩效报告基础结构
     * @param array<mixed> $ranking
     * @return array<string, mixed>
     */
    private function createPerformanceReportStructure(string $period, array $ranking): array
    {
        return [
            'report_type' => 'performance',
            'period' => $period,
            'generated_at' => new \DateTimeImmutable(),
            'total_teachers' => count($ranking),
            'ranking' => [],
        ];
    }

    /**
     * 处理绩效排名数据
     * @param array<mixed> $ranking
     * @return array<int, array<string, mixed>>
     */
    private function processPerformanceRanking(
        array $ranking,
        ?string $teacherType,
        ?string $teacherStatus,
        bool $includeDetails,
    ): array {
        $processedRanking = [];

        foreach ($ranking as $index => $performance) {
            if (!$performance instanceof TeacherPerformance) {
                continue;
            }

            $teacher = $performance->getTeacher();
            if (!$this->matchesTeacherFilters($teacher, $teacherType, $teacherStatus)) {
                continue;
            }

            $processedRanking[] = $this->buildPerformanceTeacherData($performance, $teacher, $index + 1, $includeDetails);
        }

        return $processedRanking;
    }

    /**
     * 构建绩效教师数据
     * @return array<string, mixed>
     */
    private function buildPerformanceTeacherData(
        TeacherPerformance $performance,
        Teacher $teacher,
        int $rank,
        bool $includeDetails,
    ): array {
        $teacherData = [
            'rank' => $rank,
            'teacher_id' => $teacher->getId(),
            'teacher_name' => $teacher->getTeacherName(),
            'teacher_code' => $teacher->getTeacherCode(),
            'teacher_type' => $teacher->getTeacherType(),
            'performance_score' => $performance->getPerformanceScore(),
            'performance_level' => $performance->getPerformanceLevel(),
            'average_evaluation' => $performance->getAverageEvaluation(),
        ];

        if ($includeDetails) {
            $teacherData['performance_metrics'] = $performance->getPerformanceMetrics();
            $teacherData['achievements'] = $performance->getAchievements();
        }

        return $teacherData;
    }

    /**
     * 生成评价报告
     * @return array<string, mixed>
     */
    private function generateEvaluationReport(
        ?string $teacherId,
        string $period,
        ?string $teacherType,
        ?string $teacherStatus,
        bool $includeDetails,
    ): array {
        if (null !== $teacherId) {
            return $this->generateSingleTeacherEvaluationReport($teacherId, $period);
        }

        return $this->generateBatchEvaluationReport($period, $teacherType, $teacherStatus, $includeDetails);
    }

    /**
     * 生成单个教师评价报告
     * @return array<string, mixed>
     */
    private function generateSingleTeacherEvaluationReport(string $teacherId, string $period): array
    {
        $teacher = $this->teacherService->getTeacherById($teacherId);
        $statistics = $this->evaluationService->getEvaluationStatistics($teacherId);

        return [
            'report_type' => 'evaluation',
            'teacher' => [
                'id' => $teacher->getId(),
                'name' => $teacher->getTeacherName(),
                'code' => $teacher->getTeacherCode(),
                'type' => $teacher->getTeacherType(),
            ],
            'period' => $period,
            'statistics' => $statistics,
            'generated_at' => new \DateTimeImmutable(),
        ];
    }

    /**
     * 生成批量评价报告
     * @return array<string, mixed>
     */
    private function generateBatchEvaluationReport(
        string $period,
        ?string $teacherType,
        ?string $teacherStatus,
        bool $includeDetails,
    ): array {
        $teachers = $this->getFilteredTeachers($teacherType, $teacherStatus);
        $report = $this->createEvaluationReportStructure($period, $teachers);
        $report['teachers'] = $this->processEvaluationTeachers($teachers, $includeDetails);

        return $report;
    }

    /**
     * 创建评价报告基础结构
     * @param array<int, Teacher> $teachers
     * @return array<string, mixed>
     */
    private function createEvaluationReportStructure(string $period, array $teachers): array
    {
        return [
            'report_type' => 'evaluation',
            'period' => $period,
            'generated_at' => new \DateTimeImmutable(),
            'total_teachers' => count($teachers),
            'teachers' => [],
        ];
    }

    /**
     * 处理评价教师数据
     * @param array<int, Teacher> $teachers
     * @return array<int, array<string, mixed>>
     */
    private function processEvaluationTeachers(array $teachers, bool $includeDetails): array
    {
        $processedTeachers = [];

        foreach ($teachers as $teacher) {
            $statistics = $this->evaluationService->getEvaluationStatistics($teacher->getId());
            $processedTeachers[] = $this->buildTeacherEvaluationData($teacher, $statistics, $includeDetails);
        }

        return $processedTeachers;
    }

    /**
     * 生成统计报告
     * @return array<string, mixed>
     */
    private function generateStatisticsReport(
        string $period,
        ?string $teacherType,
        ?string $teacherStatus,
    ): array {
        $teacherStats = $this->teacherService->getTeacherStatistics();
        $performanceStats = $this->performanceService->getPerformanceStatistics();

        return [
            'report_type' => 'statistics',
            'period' => $period,
            'generated_at' => new \DateTimeImmutable(),
            'teacher_statistics' => $teacherStats,
            'performance_statistics' => $performanceStats,
            'filters' => [
                'teacher_type' => $teacherType,
                'teacher_status' => $teacherStatus,
            ],
        ];
    }

    /**
     * 生成综合报告
     * @return array<string, mixed>
     */
    private function generateSummaryReport(
        string $period,
        ?string $teacherType,
        ?string $teacherStatus,
        bool $includeDetails,
    ): array {
        $teachers = $this->getFilteredTeachers($teacherType, $teacherStatus);
        $topPerformers = $this->getValidTopPerformers();

        $report = [
            'report_type' => 'summary',
            'period' => $period,
            'generated_at' => new \DateTimeImmutable(),
            'overview' => $this->generateTeacherOverview($teachers),
            'top_performers' => $this->formatTopPerformers($topPerformers),
        ];

        if ($includeDetails) {
            $report = $this->addDetailedStatistics($report);
        }

        return $report;
    }

    /**
     * 获取有效的顶级表现者数据
     * @return array<mixed>
     */
    private function getValidTopPerformers(): array
    {
        return $this->evaluationService->getTopRatedTeachers(5);
    }

    /**
     * 生成教师概览统计
     * @param array<int, Teacher> $teachers
     * @return array<string, int>
     */
    private function generateTeacherOverview(array $teachers): array
    {
        return [
            'total_teachers' => count($teachers),
            'active_teachers' => $this->countTeachersByStatus($teachers, 'active'),
            'full_time_teachers' => $this->countTeachersByType($teachers, 'full-time'),
            'part_time_teachers' => $this->countTeachersByType($teachers, 'part-time'),
        ];
    }

    /**
     * 按状态统计教师数量
     * @param array<int, Teacher> $teachers
     */
    private function countTeachersByStatus(array $teachers, string $status): int
    {
        return count(array_filter($teachers, static fn ($t) => $status === $t->getTeacherStatus()));
    }

    /**
     * 按类型统计教师数量
     * @param array<int, Teacher> $teachers
     */
    private function countTeachersByType(array $teachers, string $type): int
    {
        return count(array_filter($teachers, static fn ($t) => $type === $t->getTeacherType()));
    }

    /**
     * 格式化顶级表现者数据
     * @param array<mixed> $topPerformers
     * @return array<int, array<string, mixed>>
     */
    private function formatTopPerformers(array $topPerformers): array
    {
        return array_values(array_map(fn ($performer) => $this->formatSinglePerformer($performer), $topPerformers));
    }

    /**
     * 格式化单个表现者数据
     * @param mixed $performerData
     * @return array<string, mixed>
     */
    private function formatSinglePerformer($performerData): array
    {
        if (!is_array($performerData)) {
            return $this->getEmptyPerformerData();
        }

        /** @var array<string, mixed> $performerArray */
        $performerArray = $performerData;

        return [
            'teacher_id' => $this->extractScalarValue($performerArray, 'id', ''),
            'teacher_name' => $this->extractScalarValue($performerArray, 'teacherName', ''),
            'teacher_code' => $this->extractScalarValue($performerArray, 'teacherCode', ''),
            'average_rating' => $this->extractNumericValue($performerArray, 'avgScore', 0.0),
        ];
    }

    /**
     * 获取空的表现者数据结构
     * @return array<string, mixed>
     */
    private function getEmptyPerformerData(): array
    {
        return [
            'teacher_id' => '',
            'teacher_name' => '',
            'teacher_code' => '',
            'average_rating' => 0.0,
        ];
    }

    /**
     * 添加详细统计信息
     * @param array<string, mixed> $report
     * @return array<string, mixed>
     */
    private function addDetailedStatistics(array $report): array
    {
        $report['teacher_statistics'] = $this->teacherService->getTeacherStatistics();
        $report['performance_statistics'] = $this->performanceService->getPerformanceStatistics();

        return $report;
    }

    /**
     * 获取筛选后的教师列表
     * @return array<int, Teacher>
     */
    private function getFilteredTeachers(?string $teacherType, ?string $teacherStatus): array
    {
        /** @var array<string, string> $criteria */
        $criteria = [];
        if (null !== $teacherType) {
            $criteria['teacherType'] = $teacherType;
        }
        if (null !== $teacherStatus) {
            $criteria['teacherStatus'] = $teacherStatus;
        }

        return $this->teacherRepository->findBy($criteria);
    }

    /**
     * 解析周期参数
     */
    private function parsePeriod(string $period): \DateTimeImmutable
    {
        // 尝试解析 YYYY-MM 格式
        $date = \DateTimeImmutable::createFromFormat('Y-m', $period);
        if (false !== $date) {
            return $date->setDate((int) $date->format('Y'), (int) $date->format('m'), 1);
        }

        // 尝试解析 YYYY 格式
        $date = \DateTimeImmutable::createFromFormat('Y', $period);
        if (false !== $date) {
            return $date->setDate((int) $date->format('Y'), 1, 1);
        }

        throw new InvalidPeriodFormatException('无效的周期格式: ' . $period);
    }

    /**
     * 格式化输出
     * @param array<string, mixed> $data
     */
    private function formatOutput(array $data, string $format, string $reportType): string
    {
        return match ($format) {
            'json' => $this->formatJson($data),
            'csv' => $this->formatCsv($data, $reportType),
            'html' => $this->formatHtml($data, $reportType),
            'pdf' => $this->formatPdf($data, $reportType),
            default => throw new UnsupportedOutputFormatException('不支持的输出格式: ' . $format),
        };
    }

    /**
     * 格式化为CSV
     * @param array<string, mixed> $data
     */
    private function formatCsv(array $data, string $reportType): string
    {
        return match ($reportType) {
            'performance' => $this->csvFormatter->formatPerformance($data),
            'evaluation' => $this->csvFormatter->formatEvaluation($data),
            'summary' => $this->csvFormatter->formatSummary($data),
            'statistics' => $this->csvFormatter->formatStatistics($data),
            default => '',
        };
    }

    /**
     * 检查教师是否匹配筛选条件
     */
    private function matchesTeacherFilters(
        Teacher $teacher,
        ?string $teacherType,
        ?string $teacherStatus,
    ): bool {
        if (null !== $teacherType && $teacher->getTeacherType() !== $teacherType) {
            return false;
        }
        if (null !== $teacherStatus && $teacher->getTeacherStatus() !== $teacherStatus) {
            return false;
        }

        return true;
    }

    /**
     * 构建教师评价数据
     * @param mixed $statistics
     * @return array<string, mixed>
     */
    private function buildTeacherEvaluationData(
        Teacher $teacher,
        $statistics,
        bool $includeDetails,
    ): array {
        /** @var array<string, mixed> $safeStatistics */
        $safeStatistics = is_array($statistics) ? $statistics : [];

        $teacherData = [
            'teacher_id' => $teacher->getId(),
            'teacher_name' => $teacher->getTeacherName(),
            'teacher_code' => $teacher->getTeacherCode(),
            'teacher_type' => $teacher->getTeacherType(),
            'average_score' => $this->extractNumericValue($safeStatistics, 'average_score', 0.0),
            'evaluation_count' => $this->extractIntValue($safeStatistics, 'evaluation_count', 0),
        ];

        if ($includeDetails) {
            $teacherData['statistics'] = $safeStatistics;
        }

        return $teacherData;
    }

    /**
     * 格式化为HTML
     * @param array<string, mixed> $data
     */
    private function formatHtml(array $data, string $reportType): string
    {
        $html = '<html><head><title>教师报告</title></head><body>';
        $html .= "<h1>教师{$reportType}报告</h1>";
        $html .= '<p>生成时间: ' . date('Y-m-d H:i:s') . '</p>';

        // 这里可以根据需要生成更复杂的HTML格式
        $html .= '<pre>' . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';

        $html .= '</body></html>';

        return $html;
    }

    /**
     * 格式化为PDF（简化实现）
     * @param array<string, mixed> $data
     */
    private function formatPdf(array $data, string $reportType): string
    {
        // 这里应该使用PDF生成库，如TCPDF或DomPDF
        // 简化实现，返回JSON格式
        $result = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return false === $result ? '{}' : $result;
    }

    /**
     * 保存到文件
     */
    private function saveToFile(string $content, string $filePath, string $format): void
    {
        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0o755, true);
        }

        file_put_contents($filePath, $content);
    }

    /**
     * 显示输出
     */
    private function displayOutput(string $content, string $format, SymfonyStyle $io): void
    {
        switch ($format) {
            case 'json':
            case 'csv':
                $io->text($content);
                break;

            case 'html':
                $io->text('HTML格式输出，请使用 --output-file 选项保存到文件');
                break;

            case 'pdf':
                $io->text('PDF格式输出，请使用 --output-file 选项保存到文件');
                break;
        }
    }

    /**
     * 提取标量值
     * @param array<string, mixed> $data
     * @param string|int|float $defaultValue
     * @return string|int|float
     */
    private function extractScalarValue(array $data, string $key, $defaultValue)
    {
        $value = $data[$key] ?? null;

        return (is_scalar($value) && !is_bool($value)) ? $value : $defaultValue;
    }

    /**
     * 提取数值
     * @param array<string, mixed> $data
     */
    private function extractNumericValue(array $data, string $key, float $defaultValue): float
    {
        $value = $data[$key] ?? null;

        return is_numeric($value) ? (float) $value : $defaultValue;
    }

    /**
     * 提取整数值
     * @param array<string, mixed> $data
     */
    private function extractIntValue(array $data, string $key, int $defaultValue): int
    {
        $value = $data[$key] ?? null;

        return is_numeric($value) ? (int) $value : $defaultValue;
    }

    /**
     * 格式化为JSON
     * @param array<string, mixed> $data
     */
    private function formatJson(array $data): string
    {
        $result = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return false !== $result ? $result : '{}';
    }
}
