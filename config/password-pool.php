<?php

class PasswordPool
{
    private static array $pool = [
        'BlueWhale@2024',
        'GoldenRiver!89',
        'SilverFox#77',
        'EmeraldStar$56',
        'CrimsonWave@34',
        'AmberMoon#12',
        'JadeTiger!99',
        'RubyCrown$23',
        'OnyxKnight@67',
        'PearlHarbor!45',
        'SapphireMtn#88',
        'TopazDream@11',
        'QuartzLight$39',
        'OpalBreeze!74',
        'CitrineSun#52',
        'PlatinumSky@28',
        'ObsidianFlame$91',
        'IvoryTower!16',
        'BronzeShield#83',
        'IronClad@47',
        'CopperCove$62',
        'ZincGrove!35',
        'TungstenPeak#19',
        'MarbleStone$71',
        'GraniteWall@58',
        'SlateRiver!94',
        'BasaltCave#27',
        'Limestone@43',
        'Sandstone!67',
        'Basilisk#81',
        'FalconEye@29',
        'HawkWing$55',
        'EagleClaw!73',
        'PhoenixRise#41',
        'DragonScale@87',
        'GriffinPride$33',
        'PhoenixFire!19',
        'DragonGold#95',
        'TigerStripe@61',
        'WolfPack$47',
        'BearPaw!82',
        'FoxTail#38',
        'OtterPlay@64',
        'BeaverDam$21',
        'HareSpeed!57',
        'LynxStep#93',
        'PantherNight@45',
        'JaguarFast$72',
        'LeopardSpot!26',
        'Mongoose@58',
    ];

    public static function getRandom(): string
    {
        return self::$pool[array_rand(self::$pool)];
    }

    public static function all(): array
    {
        return self::$pool;
    }
}
