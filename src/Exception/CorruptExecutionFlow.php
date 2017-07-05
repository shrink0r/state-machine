<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Exception;

use Daikon\StateMachine\State\ExecutionTracker;
use RuntimeException;

class CorruptExecutionFlow extends RuntimeException implements ExceptionInterface
{
    public static function fromExecutionTracker(ExecutionTracker $executionTracker, int $maxCycles): self
    {
        $cycleCrumbs = $executionTracker->detectExecutionLoop();
        $message = sprintf("Trying to execute more than the allowed number of %d workflow steps.\n", $maxCycles);
        if (count($cycleCrumbs) === count($executionTracker->getBreadcrumbs())) {
            $message .= "It is likely that an intentional cycle inside the workflow isn't properly exiting.\n".
                "The executed states where:\n";
        } else {
            $message .= "Looks like there is a loop between: ";
        }
        $message .= implode(' -> ', $cycleCrumbs->toArray());
        return new self($message);
    }
}
