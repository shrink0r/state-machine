<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\State;

use Shrink0r\PhpSchema\Error;
use Shrink0r\PhpSchema\SchemaInterface;
use Daikon\StateMachine\Error\InvalidInput;
use Daikon\StateMachine\Error\InvalidOutput;
use Daikon\StateMachine\Param\InputInterface;
use Daikon\StateMachine\Param\OutputInterface;
use Daikon\StateMachine\State\StateInterface;

final class Validator implements ValidatorInterface
{
    private $input_schema;

    private $output_schema;

    public function __construct(SchemaInterface $input_schema, SchemaInterface $output_schema)
    {
        $this->input_schema = $input_schema;
        $this->output_schema = $output_schema;
    }

    public function validateInput(StateInterface $state, InputInterface $input)
    {
        $result = $this->input_schema->validate($input->toArray());
        if ($result instanceof Error) {
            throw new InvalidInput(
                $result->unwrap(),
                sprintf("Trying to execute state '%s' with invalid input.", $state->getName())
            );
        }
    }

    public function validateOutput(StateInterface $state, OutputInterface $output)
    {
        $result = $this->output_schema->validate($output->toArray()['params']);
        if ($result instanceof Error) {
            throw new InvalidOutput(
                $result->unwrap(),
                sprintf("Trying to return invalid output from state: '%s'", $state->getName())
            );
        }
    }

    public function getInputSchema(): SchemaInterface
    {
        return $this->input_schema;
    }

    public function getOutputSchema(): SchemaInterface
    {
        return $this->output_schema;
    }
}
