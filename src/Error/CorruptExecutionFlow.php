<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Error;

use RuntimeException;
use Daikon\StateMachine\Error\ErrorInterface;
use Daikon\StateMachine\State\ExecutionTracker;

class CorruptExecutionFlow extends RuntimeException implements ErrorInterface
{
    public static function fromExecutionTracker(ExecutionTracker $execution_tracker, int $max_cycles): self
    {
        $cycle_crumbs = $execution_tracker->detectExecutionLoop();
        $message = sprintf("Trying to execute more than the allowed number of %d workflow steps.\n", $max_cycles);
        if (count($cycle_crumbs) === count($execution_tracker->getBreadcrumbs())) {
            $message .= "It is likely that an intentional cycle inside the workflow isn't properly exiting.\n".
                "The executed states where:\n";
        } else {
            $message .= "Looks like there is a loop between: ";
        }
        $message .= implode(' -> ', $cycle_crumbs->toArray());
        return new self($message);
    }
}
