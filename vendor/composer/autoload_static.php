<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit3efc36633143023a891b49e15e18a46d
{
    public static $prefixLengthsPsr4 = array (
        'X' => 
        array (
            'Xsme\\Php4boxApi\\' => 16,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Xsme\\Php4boxApi\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit3efc36633143023a891b49e15e18a46d::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit3efc36633143023a891b49e15e18a46d::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit3efc36633143023a891b49e15e18a46d::$classMap;

        }, null, ClassLoader::class);
    }
}
