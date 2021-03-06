<?php
/**
 * This file is part of the KDN OZG package.
 *
 * @author    Gert Hammes <info@gerthammes.de>
 * @copyright 2020 Gert Hammes
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\DependencyInjection\Compiler;

use App\Statistics\ProviderLoader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ChartStatisticsCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // always first check if the primary service is defined
        if (!$container->has(ProviderLoader::class)) {
            return;
        }
        $definition = $container->findDefinition(ProviderLoader::class);

        // or processing tagged services:
        foreach ($container->findTaggedServiceIds('custom_statistics.provider') as $id => $tags) {
            $definition->addMethodCall('addProvider', array(new Reference($id)));
        }
    }
}