<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\TrainTeacherBundle\DependencyInjection\TrainTeacherExtension;

/**
 * TrainTeacherExtension测试
 */
class TrainTeacherExtensionTest extends TestCase
{
    private TrainTeacherExtension $extension;
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->extension = new TrainTeacherExtension();
        $this->container = new ContainerBuilder();
    }

    public function testLoad(): void
    {
        $this->extension->load([], $this->container);
        
        // 测试服务类是否正确注册为自动配置
        $this->assertTrue($this->container->hasDefinition('Tourze\TrainTeacherBundle\Service\TeacherService'));
        $this->assertTrue($this->container->hasDefinition('Tourze\TrainTeacherBundle\Service\EvaluationService'));
        $this->assertTrue($this->container->hasDefinition('Tourze\TrainTeacherBundle\Service\PerformanceService'));
    }
} 