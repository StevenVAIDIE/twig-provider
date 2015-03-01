<?php

namespace Spear\Silex\Provider;

use Silex\ServiceProviderInterface;
use Puzzle\Configuration;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;

class Twig implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $this->app = $app;
        $this->validatePuzzleConfiguration($app);
        $this->initializeTwigProvider($app);
    }

    public function addPath($paths, $prioritary = true)
    {
        if(! is_array($paths))
        {
            $paths = array($paths);
        }

        $twigPath = $this->retrieveExistingTwigPath();

        $arrayAddFunction = 'array_push';
        if($prioritary === true)
        {
            $arrayAddFunction = 'array_unshift';
            $paths = array_reverse($paths);
        }

        $this->app['twig.path'] = $this->app->share(function() use($twigPath, $paths, $arrayAddFunction) {
            foreach ($paths as $path)
            {
                $arrayAddFunction($twigPath, $path);
            }

            return $twigPath;
        });
    }

    public function boot(Application $app)
    {
    }

    private function initializeTwigProvider(Application $app)
    {
        $app->register(new TwigServiceProvider());

        $app['twig.cache.path'] = $app['var.path'] . $app['configuration']->read('twig/cache/directory', false);

        $app['twig.options'] = array(
            'cache' => $app['twig.cache.path'],
            'auto_reload' => $app['configuration']->read('twig/developmentMode', false),
        );

        $app['twig.path.manager'] = function ($c) {
            return $this;
        };
    }

    private function retrieveExistingTwigPath()
    {
        $path = array();

        if(isset($this->app['twig.path']))
        {
            $path = $this->app['twig.path'];

            if($path instanceof \Closure)
            {
                $path = $path();
            }
        }

        return $path;
    }

    private function validatePuzzleConfiguration(Application $app)
    {
        if(! isset($app['configuration']) || ! $app['configuration'] instanceof Configuration)
        {
            throw new \LogicException('AsseticProvider requires an instance of puzzle/configuration for the key configuration to be defined.');
        }
    }
}
