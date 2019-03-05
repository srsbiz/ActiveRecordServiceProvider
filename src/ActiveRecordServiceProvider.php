<?php

namespace srsbiz\Silex;

use Silex\Application;
use Silex\ServiceProviderInterface;

class ActiveRecordServiceProvider implements ServiceProviderInterface
{
    function register(Application $app) {
        $app['ar.init'] = $app->share(function (Application $app) {
            \ActiveRecord\Config::initialize(function ($cfg) use ($app) {
                $cfg->set_model_directory($app['ar.model_dir']);
                $cfg->set_connections($app['ar.connections']);
                $cfg->set_default_connection($app['ar.default_connection']);
                
                if ($app['debug'] && isset($app['ar.logger'])) {
                    $cfg->set_logging(true);
                    $cfg->set_logger($app['ar.logger']);
                }
                if (isset($app['ar.cache'])) {
                    $cfg->set_cache($app['ar.cache'], isset($app['ar.cache_options']) ? $app['ar.cache_options'] : []);
                }
            });
        });

        if ($app['debug']) {
            $app['twig.loader.filesystem'] = $app->share($app->extend('twig.loader.filesystem', function ($loader, $app) {
                $loader->addPath(__DIR__, 'ActiveRecord');
                return $loader;
            }));
            $app['data_collector.templates'] = $app->share($app->extend('data_collector.templates', function ($templates, $app) {
                $templates[] = array('ar', '@ActiveRecord/ar.html.twig');
                return $templates;
            }));
             $app['data_collectors'] = $app->share($app->extend('data_collectors', function ($collectors, $app) {
                $collectors['ar'] = $app->share(function ($app) {
                    return new ActiveRecordDataCollector($app['ar.logger']);
                });
                return $collectors;
            }));
        }
    }

    function boot(Application $app){
        $app['ar.init'];
    }
}
