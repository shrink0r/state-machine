<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Transition;

use Daikon\StateMachine\Param\InputInterface;
use Daikon\StateMachine\Param\OutputInterface;

interface ConstraintInterface
{
    public function accepts(InputInterface $input, OutputInterface $output): bool;

    public function __toString(): string;
}
