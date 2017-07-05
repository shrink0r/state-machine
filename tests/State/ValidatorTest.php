<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Tests\State;

use Shrink0r\PhpSchema\Factory;
use Shrink0r\PhpSchema\Ok;
use Shrink0r\PhpSchema\Schema;
use Shrink0r\PhpSchema\SchemaInterface;
use Daikon\StateMachine\Param\Input;
use Daikon\StateMachine\Param\Output;
use Daikon\StateMachine\State\StateInterface;
use Daikon\StateMachine\State\Validator;
use Daikon\StateMachine\Tests\TestCase;

final class ValidatorTest extends TestCase
{
    private static $default_schema = [ 'type' => 'assoc', 'properties' =>  [ ':any_name:' => [ 'type' => 'any' ] ] ];

    public function testGetInputSchema()
    {
        $validator = new Validator(
            new Schema('input_schema', self::$default_schema, new Factory),
            new Schema('output_schema', self::$default_schema, new Factory)
        );
        $this->assertInstanceOf(SchemaInterface::CLASS, $validator->getInputSchema());
    }

    public function testGetOutputSchema()
    {
        $validator = new Validator(
            new Schema('input_schema', self::$default_schema, new Factory),
            new Schema('output_schema', self::$default_schema, new Factory)
        );
        $this->assertInstanceOf(SchemaInterface::CLASS, $validator->getOutputSchema());
    }

    public function testValidateInput()
    {
        $validator = new Validator(
            new Schema('input_schema', self::$default_schema, new Factory),
            new Schema('output_schema', self::$default_schema, new Factory)
        );
        $mocked_state = $this->createMock(StateInterface::CLASS);
        $validator->validateInput($mocked_state, new Input([ 'foo' => 'bar' ]));
    }

    public function testValidateOutput()
    {
        $validator = new Validator(
            new Schema('input_schema', self::$default_schema, new Factory),
            new Schema('output_schema', self::$default_schema, new Factory)
        );
        $mocked_state = $this->createMock(StateInterface::CLASS);
        $validator->validateOutput($mocked_state, new Output('initial', [ 'foo' => 'bar' ]));
    }

    /**
     * @expectedException Daikon\StateMachine\Error\InvalidInput
     */
    public function testInvalidInput()
    {
        $input_schema = self::$default_schema;
        $input_schema['properties'] = [ 'foo' => [ 'type' => 'bool', 'required' => true ] ];
        $validator = new Validator(
            new Schema('input_schema', $input_schema, new Factory),
            new Schema('output_schema', self::$default_schema, new Factory)
        );
        $mocked_state = $this->createMock(StateInterface::CLASS);
        $validator->validateInput($mocked_state, new Input([ 'foo' => 'bar' ]));
    } // @codeCoverageIgnore

    /**
     * @expectedException Daikon\StateMachine\Error\InvalidOutput
     */
    public function testInvalidOutput()
    {
        $output_schema = self::$default_schema;
        $output_schema['properties'] = [ 'foo' => [ 'type' => 'bool', 'required' => true ] ];
        $validator = new Validator(
            new Schema('input_schema', self::$default_schema, new Factory),
            new Schema('output_schema', $output_schema, new Factory)
        );
        $mocked_state = $this->createMock(StateInterface::CLASS);
        $validator->validateOutput($mocked_state, new Output('initial', [ 'foo' => 'bar' ]));
    } // @codeCoverageIgnore
}
