<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;
use Tourze\TrainTeacherBundle\DependencyInjection\TrainTeacherExtension;

/**
 * TrainTeacherExtension测试
 *
 * @internal
 */
#[CoversClass(TrainTeacherExtension::class)]
final class TrainTeacherExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    private TrainTeacherExtension $extension;

    private ContainerBuilder $container;

    public function testLoad(): void
    {
        $this->extension->load([], $this->container);

        // 测试服务类是否正确注册为自动配置
        $this->assertTrue($this->container->hasDefinition('Tourze\TrainTeacherBundle\Service\TeacherService'));
        $this->assertTrue($this->container->hasDefinition('Tourze\TrainTeacherBundle\Service\EvaluationService'));
        $this->assertTrue($this->container->hasDefinition('Tourze\TrainTeacherBundle\Service\PerformanceService'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->extension = new TrainTeacherExtension();
        $this->container = new ContainerBuilder();
        $this->container->setParameter('kernel.environment', 'test');
    }
}
