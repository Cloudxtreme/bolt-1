<?php

namespace Bolt\Provider;

use Bolt\EventListener\DoctrineListener;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Database provider.
 *
 * @author Carson Full <carsonfull@gmail.com>
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class DatabaseProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['db.config'] = $app->share(
            $app->extend('db.config',
                function ($config) use ($app) {
                    $config->setFilterSchemaAssetsExpression($app['schema.tables_filter']);

                    return $config;
                }
            )
        );

        // For each database connection add this class as an event subscriber
        $app['dbs.event_manager'] = $app->share(
            $app->extend(
                'dbs.event_manager',
                function ($managers) use ($app) {
                    /** @var \Pimple $managers */
                    foreach ($managers->keys() as $name) {
                        /** @var \Doctrine\Common\EventManager $manager */
                        $manager = $managers[$name];
                        $manager->addEventSubscriber(new DoctrineListener($app['logger.system']));
                    }

                    return $managers;
                }
            )
        );
    }

    public function boot(Application $app)
    {
    }
}
