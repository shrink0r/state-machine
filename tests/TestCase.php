<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Shrink0r\PhpSchema\Factory;
use Shrink0r\PhpSchema\Schema;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Daikon\StateMachine\Param\Settings;
use Daikon\StateMachine\State\Validator;
use Daikon\StateMachine\State\State;
use Daikon\StateMachine\State\StateInterface;

class TestCase extends PHPUnitTestCase
{
    private static $default_schema = [ 'type' => 'assoc', 'properties' =>  [ ':any_name:' => [ 'type' => 'any' ] ] ];

    public function createState(
        $name,
        $implementor = State::ClASS,
        $settings = null,
        $input_schema = null,
        $output_schema = null
    ): StateInterface {
        return new $implementor($name, ...$this->getDefaultStateArgs($settings, $input_schema, $output_schema));
    }

    public function getDefaultStateArgs($settings = null, $input_schema = null, $output_schema = null): array
    {
        return [
            $settings ?: new Settings,
            new Validator(
                $input_schema ?: new Schema('input_schema', self::$default_schema, new Factory),
                $output_schema ?: new Schema('output_schema', self::$default_schema, new Factory)
            ),
            new ExpressionLanguage
        ];
    }
}
