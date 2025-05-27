<?php

namespace Tourze\TrainTeacherBundle\Tests\Integration;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Tourze\TrainTeacherBundle\TrainTeacherBundle;

/**
 * 集成测试专用内核
 */
class IntegrationTestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new TrainTeacherBundle(),
        ];
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $container->loadFromExtension('framework', [
            'test' => true,
            'secret' => 'test-secret',
            'property_access' => true,
        ]);

        $container->loadFromExtension('doctrine', [
            'dbal' => [
                'driver' => 'pdo_sqlite',
                'path' => ':memory:',
                'charset' => 'utf8',
            ],
            'orm' => [
                'auto_generate_proxy_classes' => true,
                'auto_mapping' => true,
                'mappings' => [
                    'TrainTeacherBundle' => [
                        'is_bundle' => true,
                        'type' => 'attribute',
                        'dir' => 'Entity',
                        'prefix' => 'Tourze\TrainTeacherBundle\Entity',
                    ],
                ],
            ],
        ]);

        // 手动配置服务
        $container->autowire(\Tourze\TrainTeacherBundle\Service\TeacherService::class)
            ->setPublic(true);
        
        $container->autowire(\Tourze\TrainTeacherBundle\Service\EvaluationService::class)
            ->setPublic(true);
            
        $container->autowire(\Tourze\TrainTeacherBundle\Service\PerformanceService::class)
            ->setPublic(true);
            
        $container->autowire(\Tourze\TrainTeacherBundle\Repository\TeacherRepository::class)
            ->setArgument('$registry', new \Symfony\Component\DependencyInjection\Reference('doctrine'));
            
        $container->autowire(\Tourze\TrainTeacherBundle\Repository\TeacherEvaluationRepository::class)
            ->setArgument('$registry', new \Symfony\Component\DependencyInjection\Reference('doctrine'));
            
        $container->autowire(\Tourze\TrainTeacherBundle\Repository\TeacherPerformanceRepository::class)
            ->setArgument('$registry', new \Symfony\Component\DependencyInjection\Reference('doctrine'));
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        // 测试不需要路由配置
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/train_teacher_bundle_test/cache';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/train_teacher_bundle_test/logs';
    }
} 