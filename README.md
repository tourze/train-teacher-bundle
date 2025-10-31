# TrainTeacherBundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/train-teacher-bundle.svg?style=flat-square)]
(https://packagist.org/packages/tourze/train-teacher-bundle)
[![PHP Version Require](https://img.shields.io/packagist/php-v/tourze/train-teacher-bundle?style=flat-square)]
(https://packagist.org/packages/tourze/train-teacher-bundle)
[![License](https://img.shields.io/packagist/l/tourze/train-teacher-bundle?style=flat-square)]
(https://packagist.org/packages/tourze/train-teacher-bundle)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/php-monorepo/ci.yml?branch=master&style=flat-square)]
(https://github.com/tourze/php-monorepo/actions)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/php-monorepo?style=flat-square)]
(https://codecov.io/gh/tourze/php-monorepo)

A Symfony bundle for comprehensive teacher management in safety production 
training systems, providing full lifecycle management capabilities for 
instructor teams.

## Table of Contents

- [Features](#features)
  - [Core Features](#core-features)
  - [Technical Features](#technical-features)
- [Dependencies](#dependencies)
- [Installation](#installation)
  - [Using Composer](#using-composer)
  - [Register the Bundle](#register-the-bundle)
  - [Database Migration](#database-migration)
- [Configuration](#configuration)
- [Usage](#usage)
  - [Teacher Management](#teacher-management)
  - [Teacher Evaluation](#teacher-evaluation)
  - [Performance Management](#performance-management)
- [Advanced Usage](#advanced-usage)
- [Data Models](#data-models)
- [Console Commands](#console-commands)
- [API Documentation](#api-documentation)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)
- [Changelog](#changelog)

## Features

### Core Features
- **Teacher Information Management**: Basic information, contact details, 
  ID information, photos, resumes, and record management
- **Teacher Category Management**: Full-time/part-time teacher classification management
- **Multi-dimensional Evaluation System**: Student evaluations, peer evaluations, 
  management evaluations, self-evaluations
- **Performance Assessment System**: Teacher performance evaluation and ranking statistics
- **Statistical Analysis**: Evaluation result statistical analysis and feedback mechanisms
- **Status Management**: Teacher status management and data synchronization

### Technical Features
- Built on Symfony Framework
- Uses Doctrine ORM for data management
- Supports PHP 8.1+
- Follows PSR standards
- Complete unit test coverage

## Installation

### Using Composer

```bash
composer require tourze/train-teacher-bundle
```

### Register the Bundle

Add to `config/bundles.php`:

```php
return [
    // ...
    Tourze\TrainTeacherBundle\TrainTeacherBundle::class => ['all' => true],
];
```

### Database Migration

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

## Dependencies

This bundle requires the following packages:

### PHP Requirements
- PHP 8.1 or higher
- ext-filter
- ext-json

### Symfony Requirements
- symfony/config ^7.3
- symfony/console ^7.3
- symfony/dependency-injection ^7.3
- symfony/doctrine-bridge ^7.3
- symfony/framework-bundle ^7.3
- symfony/http-kernel ^7.3
- symfony/routing ^7.3
- symfony/yaml ^7.3

### Doctrine Requirements
- doctrine/collections ^2.3
- doctrine/dbal ^4.0
- doctrine/doctrine-bundle ^2.13
- doctrine/orm ^3.0
- doctrine/persistence ^4.1

### Tourze Requirements
- tourze/bundle-dependency 0.0.*

### Development Requirements
- phpstan/phpstan ^2.1
- phpunit/phpunit ^11.5

## Configuration

The bundle works with default configuration without additional setup.

## Usage

### Teacher Management

```php
use Tourze\TrainTeacherBundle\Service\TeacherService;

// Inject service
public function __construct(
    private TeacherService $teacherService
) {}

// Create teacher
$teacherData = [
    'teacherName' => 'John Doe',
    'teacherType' => 'full-time',
    'gender' => 'male',
    'birthDate' => new \DateTime('1980-01-01'),
    'idCard' => '110101198001011234',
    'phone' => '13800138000',
    'email' => 'john.doe@example.com',
    'education' => 'bachelor',
    'major' => 'Safety Engineering',
    'graduateSchool' => 'University of Safety',
    'graduateDate' => new \DateTime('2002-07-01'),
    'workExperience' => 20,
    'specialties' => ['Safety Management', 'Risk Assessment'],
    'teacherStatus' => 'active',
    'joinDate' => new \DateTime('2005-03-01'),
];

$teacher = $this->teacherService->createTeacher($teacherData);

// Get teacher information
$teacher = $this->teacherService->getTeacherById($teacherId);
$teacher = $this->teacherService->getTeacherByCode($teacherCode);

// Search teachers
$teachers = $this->teacherService->searchTeachers('John');

// Get statistics
$statistics = $this->teacherService->getTeacherStatistics();
```

### Teacher Evaluation

```php
use Tourze\TrainTeacherBundle\Service\EvaluationService;

// Inject service
public function __construct(
    private EvaluationService $evaluationService
) {}

// Submit evaluation
$evaluationData = [
    'evaluatorType' => 'student',
    'evaluationType' => 'course_evaluation',
    'evaluationItems' => ['Teaching Attitude', 'Professional Level', 'Communication'],
    'evaluationScores' => [
        'Teaching Attitude' => 5,
        'Professional Level' => 4.5,
        'Communication' => 4.8,
    ],
    'evaluationComments' => 'Excellent teaching, solid professional knowledge',
    'suggestions' => ['More practical case studies recommended'],
    'isAnonymous' => false,
];

$evaluation = $this->evaluationService->submitEvaluation(
    $teacherId, 
    $evaluatorId, 
    $evaluationData
);

// Get evaluation statistics
$statistics = $this->evaluationService->getEvaluationStatistics($teacherId);

// Generate evaluation report
$report = $this->evaluationService->generateEvaluationReport($teacherId);
```

### Performance Management

```php
use Tourze\TrainTeacherBundle\Service\PerformanceService;

// Inject service
public function __construct(
    private PerformanceService $performanceService
) {}

// Calculate performance
$period = new \DateTime('2024-01-01');
$performance = $this->performanceService->calculatePerformance($teacherId, $period);

// Get performance ranking
$ranking = $this->performanceService->getPerformanceRanking(20);

// Generate performance report
$report = $this->performanceService->generatePerformanceReport($teacherId);

// Compare teacher performance
$comparison = $this->performanceService->compareTeacherPerformance(
    [$teacherId1, $teacherId2], 
    $period
);
```

## Advanced Usage

### Custom Evaluation Types

You can define custom evaluation types by extending the base evaluation logic:

```php
use Tourze\TrainTeacherBundle\Service\EvaluationService;

class CustomEvaluationService extends EvaluationService
{
    public function processCustomEvaluation(array $criteria): array
    {
        // Custom evaluation logic
        return $this->processEvaluation($criteria);
    }
}
```

### Performance Metrics Customization

Customize performance calculation by implementing custom metrics:

```php
use Tourze\TrainTeacherBundle\Service\PerformanceService;

// Add custom metrics to performance calculation
$customMetrics = [
    'innovation_score' => 0.15,
    'collaboration_score' => 0.10,
    'technical_skills' => 0.25
];

$performance = $this->performanceService->calculatePerformanceWithCustomMetrics(
    $teacherId,
    $period,
    $customMetrics
);
```

### Data Export and Import

Export teacher data for external analysis:

```php
use Tourze\TrainTeacherBundle\Service\TeacherService;

// Export teacher data to CSV
$csvData = $this->teacherService->exportToCSV($filters);

// Import teacher data from external source
$importResult = $this->teacherService->importFromArray($teacherData);
```

### Event-Driven Architecture

The bundle supports event listeners for custom business logic:

```php
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tourze\TrainTeacherBundle\Event\TeacherEvaluatedEvent;

class TeacherEvaluationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            TeacherEvaluatedEvent::class => 'onTeacherEvaluated',
        ];
    }

    public function onTeacherEvaluated(TeacherEvaluatedEvent $event): void
    {
        // Custom logic after teacher evaluation
        $teacher = $event->getTeacher();
        $evaluation = $event->getEvaluation();
        
        // Send notifications, update external systems, etc.
    }
}
```

## Data Models

### Teacher
- Basic Information: Name, code, type, gender, birth date
- Contact Information: Phone, email, address
- Identity Information: ID card number
- Educational Background: Education level, major, graduate school, graduation date
- Work Information: Work experience, specialties, status, join date

### TeacherEvaluation
- Evaluation Information: Evaluator type, evaluator ID, evaluation type, evaluation date
- Evaluation Content: Evaluation items, evaluation scores, overall score, evaluation comments
- Suggestion Information: Suggestions, anonymous flag
- Status Information: Evaluation status

### TeacherPerformance
- Performance Information: Performance period, average evaluation score, performance indicators
- Assessment Results: Performance score, performance level, achievements

## Console Commands

This bundle provides several console commands for teacher management operations.

### teacher:evaluation:reminder
Send teacher evaluation reminder notifications to relevant personnel.

```bash
# Send all overdue evaluation reminders (7 days)
php bin/console teacher:evaluation:reminder

# Send specific type evaluation reminders
php bin/console teacher:evaluation:reminder --evaluation-type=student

# Send reminders for specific teacher
php bin/console teacher:evaluation:reminder --teacher-id=123

# Preview mode (don't actually send)
php bin/console teacher:evaluation:reminder --dry-run
```

### teacher:performance:calculate
Calculate teacher performance regularly, supporting batch and individual calculations.

```bash
# Calculate performance for all teachers in current month
php bin/console teacher:performance:calculate

# Calculate performance for specific month
php bin/console teacher:performance:calculate 2024-01

# Calculate performance for specific teacher
php bin/console teacher:performance:calculate --teacher-id=123

# Force recalculation
php bin/console teacher:performance:calculate --force
```

### teacher:data:sync
Synchronize teacher data, check data consistency and integrity.

```bash
# Synchronize all teacher data
php bin/console teacher:data:sync

# Check only without execution
php bin/console teacher:data:sync --dry-run

# Auto-fix data issues
php bin/console teacher:data:sync --fix-data

# Check duplicate data
php bin/console teacher:data:sync --check-duplicates
```

### teacher:report:generate
Generate various teacher-related reports.

```bash
# Generate performance reports
php bin/console teacher:report:generate performance

# Generate evaluation reports for specific month
php bin/console teacher:report:generate evaluation --period=2024-01

# Generate statistics reports and export to CSV
php bin/console teacher:report:generate statistics --output-format=csv --output-file=report.csv

# Generate summary reports for top 10 teachers
php bin/console teacher:report:generate summary --top-n=10 --include-details
```

## API Documentation

Please refer to the method comments in each service class for detailed API documentation.

## Testing

Run unit tests:

```bash
./vendor/bin/phpunit packages/train-teacher-bundle/tests
```

## Contributing

Issues and Pull Requests are welcome.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Changelog

### v1.0.0
- Initial release
- Implemented basic teacher management functionality
- Implemented multi-dimensional evaluation system
- Implemented performance assessment system
