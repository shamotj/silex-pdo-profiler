<?php

namespace Shamotj\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Shamotj\DataCollector\PdoDataCollector;

class PdoProfilerServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $dataCollectors = $app['data_collectors'];
        $dataCollectors['db'] = $app->share(function ($app) {

            $collector = new PdoDataCollector($app['pdo']);

            return $collector;
        });
        $app['data_collectors'] = $dataCollectors;

        $dataCollectorTemplates = $app['data_collector.templates'];
        $dataCollectorTemplates[] = array('db', '@DoctrineBundle/Collector/db.html.twig');
        $app['data_collector.templates'] = $dataCollectorTemplates;

        $app['twig.loader.filesystem'] = $app->share($app->extend('twig.loader.filesystem', function ($loader) {
            /** @var \Twig_Loader_Filesystem $loader */
            $loader->addPath(dirname(__DIR__).'/Resources/views', 'DoctrineBundle');
            return $loader;
        }));
    }

    public function boot(Application $app)
    {
    }
}
