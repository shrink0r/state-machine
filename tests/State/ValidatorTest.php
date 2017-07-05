<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Tests\State;

use Daikon\StateMachine\Param\Input;
use Daikon\StateMachine\Param\Output;
use Daikon\StateMachine\State\StateInterface;
use Daikon\StateMachine\State\Validator;
use Daikon\StateMachine\Tests\TestCase;
use Shrink0r\PhpSchema\Factory;
use Shrink0r\PhpSchema\Schema;
use Shrink0r\PhpSchema\SchemaInterface;

final class ValidatorTest extends TestCase
{
    private static $defaultSchema = [ 'type' => 'assoc', 'properties' =>  [ ':any_name:' => [ 'type' => 'any' ] ] ];

    public function testGetInputSchema()
    {
        $validator = new Validator(
            new Schema('input_schema', self::$defaultSchema, new Factory),
            new Schema('output_schema', self::$defaultSchema, new Factory)
        );
        $this->assertInstanceOf(SchemaInterface::CLASS, $validator->getInputSchema());
    }

    public function testGetOutputSchema()
    {
        $validator = new Validator(
            new Schema('input_schema', self::$defaultSchema, new Factory),
            new Schema('output_schema', self::$defaultSchema, new Factory)
        );
        $this->assertInstanceOf(SchemaInterface::CLASS, $validator->getOutputSchema());
    }

    public function testValidateInput()
    {
        $validator = new Validator(
            new Schema('input_schema', self::$defaultSchema, new Factory),
            new Schema('output_schema', self::$defaultSchema, new Factory)
        );
        $mockedState = $this->createMock(StateInterface::CLASS);
        $validator->validateInput($mockedState, new Input([ 'foo' => 'bar' ]));
    }

    public function testValidateOutput()
    {
        $validator = new Validator(
            new Schema('input_schema', self::$defaultSchema, new Factory),
            new Schema('output_schema', self::$defaultSchema, new Factory)
        );
        $mockedState = $this->createMock(StateInterface::CLASS);
        $validator->validateOutput($mockedState, new Output('initial', [ 'foo' => 'bar' ]));
    }

    /**
     * @expectedException \Daikon\StateMachine\Exception\InvalidInput
     */
    public function testInvalidInput()
    {
        $inputSchema = self::$defaultSchema;
        $inputSchema['properties'] = [ 'foo' => [ 'type' => 'bool', 'required' => true ] ];
        $validator = new Validator(
            new Schema('input_schema', $inputSchema, new Factory),
            new Schema('output_schema', self::$defaultSchema, new Factory)
        );
        $mockedState = $this->createMock(StateInterface::CLASS);
        $validator->validateInput($mockedState, new Input([ 'foo' => 'bar' ]));
    } // @codeCoverageIgnore

    /**
     * @expectedException \Daikon\StateMachine\Exception\InvalidOutput
     */
    public function testInvalidOutput()
    {
        $outputSchema = self::$defaultSchema;
        $outputSchema['properties'] = [ 'foo' => [ 'type' => 'bool', 'required' => true ] ];
        $validator = new Validator(
            new Schema('input_schema', self::$defaultSchema, new Factory),
            new Schema('output_schema', $outputSchema, new Factory)
        );
        $mockedState = $this->createMock(StateInterface::CLASS);
        $validator->validateOutput($mockedState, new Output('initial', [ 'foo' => 'bar' ]));
    } // @codeCoverageIgnore
}
