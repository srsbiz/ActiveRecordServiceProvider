<?php

/*
 * Author: Dwi Sasongko S <ruckuus@gmail.com>
 */

namespace Ruckuus\Silex;

use Silex\Application;
use Silex\ServiceProviderInterface;

class ActiveRecordServiceProvider implements ServiceProviderInterface
{
    function register(Application $app){
        $this->app = $app;

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
    }

    function boot(Application $app){
        $this->app['ar.init'];
    }
}
