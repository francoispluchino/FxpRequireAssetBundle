<?php

/*
 * This file is part of the Fxp RequireAssetBundle package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\RequireAssetBundle\DependencyInjection\Compiler;

use Fxp\Component\RequireAsset\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Adds custom assets.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class CustomAssetsPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $variables = $this->getCustomVariables($container);
        $cacheDir = $container->getParameter('kernel.cache_dir').'/fxp_require_asset';

        foreach ($container->findTaggedServiceIds('fxp_require_asset.assetic.custom_asset') as $serviceId => $tag) {
            $def = $container->getDefinition($serviceId);
            $this->buildAsset($serviceId, $def, $cacheDir, $variables);
            $container->removeDefinition($serviceId);
        }

        $container->setParameter('fxp_require_asset.assetic_filter.lessvariable.custom_variables', $variables);
    }

    /**
     * Get the custom variables for filter.
     *
     * @param ContainerBuilder $container The container service
     *
     * @return array
     */
    protected function getCustomVariables(ContainerBuilder $container)
    {
        $variables = array();

        if ($container->hasParameter('fxp_require_asset.assetic_filter.lessvariable.custom_variables')) {
            $variables = (array) $container->getParameter('fxp_require_asset.assetic_filter.lessvariable.custom_variables');
        }

        return $variables;
    }

    /**
     * Build the LESS asset.
     *
     * @param string     $serviceId  The service id of custom asset
     * @param Definition $definition The service definition of custom asset
     * @param string     $cacheDir   The cache directory
     * @param array      $variables  The variables for the assetic filter LessVariableFilter
     */
    protected function buildAsset($serviceId, Definition $definition, $cacheDir, array &$variables)
    {
        $args = $this->validateArguments($serviceId, $definition->getArguments());
        $path = $cacheDir.'/'.trim($args[0], '/');
        $fs = new Filesystem();
        $content = '';

        foreach ($args[1] as $input) {
            $content .= sprintf('@import "%s";', $input);
        }

        if (isset($args[2])) {
            $variables[$args[2]] = $path;
        }

        $fs->dumpFile($path, $content);
    }

    /**
     * Validate the arguments of custom asset definition.
     *
     * @param string $serviceId The service id of custom asset
     * @param array  $arguments The arguments of custom asset definition
     *
     * @return array
     */
    protected function validateArguments($serviceId, array $arguments)
    {
        if (!isset($arguments[0]) || !is_string($arguments[0])) {
            $mess = sprintf('The first argument "filename" is required and must be a string for the "%s" service', $serviceId);
            throw new InvalidArgumentException($mess);
        }

        if (!isset($arguments[1]) || !is_array($arguments[1])) {
            $mess = sprintf('The second argument "inputs" is required and must be a array for the "%s" service', $serviceId);
            throw new InvalidArgumentException($mess);
        }

        return $arguments;
    }
}
