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
        private readonly TeacherRepository $teacherRepository
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
                'n',
                InputOption::VALUE_OPTIONAL,
                '仅显示前N名（用于排名报告）',
                10
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        // 解析参数
        $reportType = $input->getArgument('report-type');
        $teacherId = $input->getOption('teacher-id');
        $period = $input->getOption('period');
        $outputFormat = $input->getOption('output-format');
        $outputFile = $input->getOption('output-file');
        $teacherType = $input->getOption('teacher-type');
        $teacherStatus = $input->getOption('teacher-status');
        $includeDetails = $input->getOption('include-details');
        $topN = (int) $input->getOption('top-n');

        $io->title('教师报告生成');

        try {
            // 验证报告类型
            if (!in_array($reportType, ['performance', 'evaluation', 'statistics', 'summary'])) {
                $io->error('无效的报告类型。支持的类型: performance, evaluation, statistics, summary');
                return Command::FAILURE;
            }

            // 验证输出格式
            if (!in_array($outputFormat, ['json', 'csv', 'html', 'pdf'])) {
                $io->error('无效的输出格式。支持的格式: json, csv, html, pdf');
                return Command::FAILURE;
            }

            $io->text("生成报告类型: {$reportType}");
            $io->text("报告周期: {$period}");
            $io->text("输出格式: {$outputFormat}");

            // 生成报告数据
            $reportData = $this->generateReportData($reportType, $teacherId, $period, $teacherType, $teacherStatus, $includeDetails, $topN);

            if ((bool) empty($reportData)) {
                $io->warning('没有找到符合条件的数据');
                return Command::SUCCESS;
            }

            // 格式化输出
            $formattedOutput = $this->formatOutput($reportData, $outputFormat, $reportType);

            // 输出结果
            if ((bool) $outputFile) {
                $this->saveToFile($formattedOutput, $outputFile, $outputFormat);
                $io->success("报告已保存到: {$outputFile}");
            } else {
                $this->displayOutput($formattedOutput, $outputFormat, $io);
            }

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $io->error('报告生成失败: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * 生成报告数据
     */
    private function generateReportData(
        string $reportType,
        ?string $teacherId,
        string $period,
        ?string $teacherType,
        ?string $teacherStatus,
        bool $includeDetails,
        int $topN
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
                throw new \InvalidArgumentException('不支持的报告类型: ' . $reportType);
        }
    }

    /**
     * 生成绩效报告
     */
    private function generatePerformanceReport(
        ?string $teacherId,
        string $period,
        ?string $teacherType,
        ?string $teacherStatus,
        bool $includeDetails,
        int $topN
    ): array {
        if ((bool) $teacherId) {
            // 单个教师绩效报告
            return $this->performanceService->generatePerformanceReport($teacherId);
        } else {
            // 批量绩效报告
            $periodDate = $this->parsePeriod($period);
            $ranking = $this->performanceService->getPerformanceRankingByPeriod($periodDate, $topN);
            
            $report = [
                'report_type' => 'performance',
                'period' => $period,
                'generated_at' => new \DateTime(),
                'total_teachers' => count($ranking),
                'ranking' => []
            ];

            foreach ($ranking as $index => $performance) {
                $teacher = $performance->getTeacher();
                
                // 应用筛选条件
                if ($teacherType !== null && $teacher->getTeacherType() !== $teacherType) {
                    continue;
                }
                if ($teacherStatus !== null && $teacher->getTeacherStatus() !== $teacherStatus) {
                    continue;
                }

                $teacherData = [
                    'rank' => $index + 1,
                    'teacher_id' => $teacher->getId(),
                    'teacher_name' => $teacher->getTeacherName(),
                    'teacher_code' => $teacher->getTeacherCode(),
                    'teacher_type' => $teacher->getTeacherType(),
                    'performance_score' => $performance->getPerformanceScore(),
                    'performance_level' => $performance->getPerformanceLevel(),
                    'average_evaluation' => $performance->getAverageEvaluation(),
                ];

                if ((bool) $includeDetails) {
                    $teacherData['performance_metrics'] = $performance->getPerformanceMetrics();
                    $teacherData['achievements'] = $performance->getAchievements();
                }

                $report['ranking'][] = $teacherData;
            }

            return $report;
        }
    }

    /**
     * 生成评价报告
     */
    private function generateEvaluationReport(
        ?string $teacherId,
        string $period,
        ?string $teacherType,
        ?string $teacherStatus,
        bool $includeDetails
    ): array {
        if ((bool) $teacherId) {
            // 单个教师评价报告
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
                'generated_at' => new \DateTime(),
            ];
        } else {
            // 批量评价报告
            $teachers = $this->getFilteredTeachers($teacherType, $teacherStatus);
            $report = [
                'report_type' => 'evaluation',
                'period' => $period,
                'generated_at' => new \DateTime(),
                'total_teachers' => count($teachers),
                'teachers' => []
            ];

            foreach ($teachers as $teacher) {
                $statistics = $this->evaluationService->getEvaluationStatistics($teacher->getId());
                
                $teacherData = [
                    'teacher_id' => $teacher->getId(),
                    'teacher_name' => $teacher->getTeacherName(),
                    'teacher_code' => $teacher->getTeacherCode(),
                    'teacher_type' => $teacher->getTeacherType(),
                    'average_score' => $statistics['average_score'] ?? 0,
                    'evaluation_count' => $statistics['evaluation_count'] ?? 0,
                ];

                if ((bool) $includeDetails) {
                    $teacherData['statistics'] = $statistics;
                }

                $report['teachers'][] = $teacherData;
            }

            return $report;
        }
    }

    /**
     * 生成统计报告
     */
    private function generateStatisticsReport(
        string $period,
        ?string $teacherType,
        ?string $teacherStatus
    ): array {
        $teacherStats = $this->teacherService->getTeacherStatistics();
        $performanceStats = $this->performanceService->getPerformanceStatistics();
        
        return [
            'report_type' => 'statistics',
            'period' => $period,
            'generated_at' => new \DateTime(),
            'teacher_statistics' => $teacherStats,
            'performance_statistics' => $performanceStats,
            'filters' => [
                'teacher_type' => $teacherType,
                'teacher_status' => $teacherStatus,
            ]
        ];
    }

    /**
     * 生成综合报告
     */
    private function generateSummaryReport(
        string $period,
        ?string $teacherType,
        ?string $teacherStatus,
        bool $includeDetails
    ): array {
        $teachers = $this->getFilteredTeachers($teacherType, $teacherStatus);
        $topPerformers = $this->evaluationService->getTopRatedTeachers(5);
        
        $report = [
            'report_type' => 'summary',
            'period' => $period,
            'generated_at' => new \DateTime(),
            'overview' => [
                'total_teachers' => count($teachers),
                'active_teachers' => count(array_filter($teachers, fn($t) => $t->getTeacherStatus() === 'active')),
                'full_time_teachers' => count(array_filter($teachers, fn($t) => $t->getTeacherType() === 'full-time')),
                'part_time_teachers' => count(array_filter($teachers, fn($t) => $t->getTeacherType() === 'part-time')),
            ],
            'top_performers' => array_map(function($teacher) {
                return [
                    'teacher_id' => $teacher->getId(),
                    'teacher_name' => $teacher->getTeacherName(),
                    'teacher_code' => $teacher->getTeacherCode(),
                    'average_rating' => $teacher->getAverageRating() ?? 0,
                ];
            }, $topPerformers),
        ];

        if ((bool) $includeDetails) {
            $report['teacher_statistics'] = $this->teacherService->getTeacherStatistics();
            $report['performance_statistics'] = $this->performanceService->getPerformanceStatistics();
        }

        return $report;
    }

    /**
     * 获取筛选后的教师列表
     */
    private function getFilteredTeachers(?string $teacherType, ?string $teacherStatus): array
    {
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
     * 解析周期参数
     */
    private function parsePeriod(string $period): \DateTime
    {
        // 尝试解析 YYYY-MM 格式
        $date = \DateTime::createFromFormat('Y-m', $period);
        if ((bool) $date) {
            return $date->setDate((int) $date->format('Y'), (int) $date->format('m'), 1);
        }

        // 尝试解析 YYYY 格式
        $date = \DateTime::createFromFormat('Y', $period);
        if ((bool) $date) {
            return $date->setDate((int) $date->format('Y'), 1, 1);
        }

        throw new \InvalidArgumentException('无效的周期格式: ' . $period);
    }

    /**
     * 格式化输出
     */
    private function formatOutput(array $data, string $format, string $reportType): string
    {
        switch ($format) {
            case 'json':
                return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                
            case 'csv':
                return $this->formatCsv($data, $reportType);
                
            case 'html':
                return $this->formatHtml($data, $reportType);
                
            case 'pdf':
                return $this->formatPdf($data, $reportType);
                
            default:
                throw new \InvalidArgumentException('不支持的输出格式: ' . $format);
        }
    }

    /**
     * 格式化为CSV
     */
    private function formatCsv(array $data, string $reportType): string
    {
        $output = '';
        
        // 根据报告类型生成不同的CSV格式
        switch ($reportType) {
            case 'performance':
                if ((bool) isset($data['ranking'])) {
                    $output .= "排名,教师姓名,教师编号,教师类型,绩效分数,绩效等级,平均评价\n";
                    foreach ($data['ranking'] as $item) {
                        $output .= sprintf(
                            "%d,%s,%s,%s,%.2f,%s,%.2f\n",
                            $item['rank'],
                            $item['teacher_name'],
                            $item['teacher_code'],
                            $item['teacher_type'],
                            $item['performance_score'],
                            $item['performance_level'],
                            $item['average_evaluation']
                        );
                    }
                }
                break;
                
            case 'evaluation':
                if ((bool) isset($data['teachers'])) {
                    $output .= "教师姓名,教师编号,教师类型,平均分数,评价次数\n";
                    foreach ($data['teachers'] as $item) {
                        $output .= sprintf(
                            "%s,%s,%s,%.2f,%d\n",
                            $item['teacher_name'],
                            $item['teacher_code'],
                            $item['teacher_type'],
                            $item['average_score'],
                            $item['evaluation_count']
                        );
                    }
                }
                break;
        }
        
        return $output;
    }

    /**
     * 格式化为HTML
     */
    private function formatHtml(array $data, string $reportType): string
    {
        $html = "<html><head><title>教师报告</title></head><body>";
        $html .= "<h1>教师{$reportType}报告</h1>";
        $html .= "<p>生成时间: " . date('Y-m-d H:i:s') . "</p>";
        
        // 这里可以根据需要生成更复杂的HTML格式
        $html .= "<pre>" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
        
        $html .= "</body></html>";
        return $html;
    }

    /**
     * 格式化为PDF（简化实现）
     */
    private function formatPdf(array $data, string $reportType): string
    {
        // 这里应该使用PDF生成库，如TCPDF或DomPDF
        // 简化实现，返回JSON格式
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * 保存到文件
     */
    private function saveToFile(string $content, string $filePath, string $format): void
    {
        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
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
                $io->text($content);
                break;
                
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
} 