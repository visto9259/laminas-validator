<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\ServiceManager\ServiceManager;
use Laminas\ServiceManager\Test\CommonPluginManagerTrait;
use Laminas\Validator\Barcode;
use Laminas\Validator\Bitwise;
use Laminas\Validator\Callback;
use Laminas\Validator\DateComparison;
use Laminas\Validator\Exception\RuntimeException;
use Laminas\Validator\Explode;
use Laminas\Validator\File\ExcludeExtension;
use Laminas\Validator\File\Extension;
use Laminas\Validator\File\FilesSize;
use Laminas\Validator\IsInstanceOf;
use Laminas\Validator\NumberComparison;
use Laminas\Validator\Regex;
use Laminas\Validator\ValidatorInterface;
use Laminas\Validator\ValidatorPluginManager;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

use function assert;
use function in_array;
use function is_string;

final class ValidatorPluginManagerCompatibilityTest extends TestCase
{
    use CommonPluginManagerTrait;

    private const SKIP_VALIDATORS = [
        Barcode::class,
        ExcludeExtension::class,
        Extension::class,
        FilesSize::class,
        Regex::class,
        Bitwise::class,
        Explode::class,
        Callback::class,
        DateComparison::class,
        NumberComparison::class,
        IsInstanceOf::class,
    ];

    protected static function getPluginManager(): ValidatorPluginManager
    {
        return new ValidatorPluginManager(new ServiceManager());
    }

    protected function getV2InvalidPluginException(): string
    {
        return RuntimeException::class;
    }

    protected function getInstanceOf(): string
    {
        return ValidatorInterface::class;
    }

    /** @return array<string, array{0: string, 1: string}> */
    public static function aliasProvider(): array
    {
        $out           = [];
        $pluginManager = self::getPluginManager();

        $r       = new ReflectionProperty($pluginManager, 'aliases');
        $aliases = $r->getValue($pluginManager);
        self::assertIsArray($aliases);

        foreach ($aliases as $alias => $target) {
            assert(is_string($target));
            assert(is_string($alias));

            // Skipping due to required options
            if (in_array($target, self::SKIP_VALIDATORS, true)) {
                continue;
            }

            $out[$alias] = [$alias, $target];
        }

        return $out;
    }
}
