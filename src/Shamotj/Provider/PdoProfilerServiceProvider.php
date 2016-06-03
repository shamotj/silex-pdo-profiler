<?php

namespace Shamotj\Provider;

use Pimple\Container;
use Silex\Application;
use Pimple\ServiceProviderInterface;
use Shamotj\DataCollector\PdoDataCollector;

class PdoProfilerServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app->extend('data_collectors', function ($collectors, $app) {
            $collectors['db'] = function ($app) {

                $collector = new PdoDataCollector($app['pdo']);

                return $collector;
            };
            return $collectors;
        });

        $app['data_collector.templates'] = $app->extend('data_collector.templates', function ($templates) {
            $templates[] = array('db', '@DoctrineBundle/Collector/db.html.twig');
            return $templates;
        });

        $app['twig.loader.filesystem'] = $app->extend('twig.loader.filesystem', function ($loader) {
            /** @var \Twig_Loader_Filesystem $loader */
            $loader->addPath(dirname(__DIR__).'/Resources/views', 'DoctrineBundle');
            return $loader;
        });
    }

    public function boot(Application $app)
    {
    }
}
