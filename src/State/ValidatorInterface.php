<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\State;

use Shrink0r\PhpSchema\SchemaInterface;
use Daikon\StateMachine\Param\InputInterface;
use Daikon\StateMachine\Param\OutputInterface;
use Daikon\StateMachine\State\StateInterface;

interface ValidatorInterface
{
    public function validateInput(StateInterface $state, InputInterface $input);

    public function validateOutput(StateInterface $state, OutputInterface $output);

    public function getInputSchema(): SchemaInterface;

    public function getOutputSchema(): SchemaInterface;
}
