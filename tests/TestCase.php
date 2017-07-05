<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Tests;

use Daikon\StateMachine\Param\Settings;
use Daikon\StateMachine\State\State;
use Daikon\StateMachine\State\StateInterface;
use Daikon\StateMachine\State\Validator;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Shrink0r\PhpSchema\Factory;
use Shrink0r\PhpSchema\Schema;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class TestCase extends PHPUnitTestCase
{
    private static $defaultSchema = [ 'type' => 'assoc', 'properties' =>  [ ':any_name:' => [ 'type' => 'any' ] ] ];

    public function createState(
        $name,
        $implementor = State::ClASS,
        $settings = null,
        $inputSchema = null,
        $outputSchema = null
    ): StateInterface {
        return new $implementor($name, ...$this->getDefaultStateArgs($settings, $inputSchema, $outputSchema));
    }

    public function getDefaultStateArgs($settings = null, $inputSchema = null, $outputSchema = null): array
    {
        return [
            $settings ?: new Settings,
            new Validator(
                $inputSchema ?: new Schema('input_schema', self::$defaultSchema, new Factory),
                $outputSchema ?: new Schema('output_schema', self::$defaultSchema, new Factory)
            ),
            new ExpressionLanguage
        ];
    }
}
