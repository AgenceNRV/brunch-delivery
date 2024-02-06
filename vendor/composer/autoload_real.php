<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit85503b77f368509e4bbdbdc064b07044
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInit85503b77f368509e4bbdbdc064b07044', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInit85503b77f368509e4bbdbdc064b07044', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInit85503b77f368509e4bbdbdc064b07044::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}
