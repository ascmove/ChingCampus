<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitbd8ad024132b2658cccdbfbb59d566b9
{
    public static $prefixLengthsPsr4 = array (
        'O' => 
        array (
            'Overtrue\\ChineseCalendar\\' => 25,
        ),
        'H' => 
        array (
            'Hashids\\' => 8,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Overtrue\\ChineseCalendar\\' => 
        array (
            0 => __DIR__ . '/..' . '/overtrue/chinese-calendar/src',
        ),
        'Hashids\\' => 
        array (
            0 => __DIR__ . '/..' . '/hashids/hashids/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitbd8ad024132b2658cccdbfbb59d566b9::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitbd8ad024132b2658cccdbfbb59d566b9::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}