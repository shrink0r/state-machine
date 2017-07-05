<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\State;

use Daikon\StateMachine\StateMachineInterface;
use Ds\Map;
use Ds\Vector;
use Shrink0r\SuffixTree\Builder\SuffixTreeBuilder;

final class ExecutionTracker
{
    private $breadcrumbs;

    private $executionCounts;

    private $stateMachine;

    public function __construct(StateMachineInterface $stateMachine)
    {
        $this->stateMachine = $stateMachine;
        $this->breadcrumbs = new Vector;
        $this->executionCounts = new Map;
        foreach ($stateMachine->getStates() as $state) {
            $this->executionCounts[$state->getName()] = 0;
        }
    }

    public function track(StateInterface $state): int
    {
        $this->breadcrumbs->push($state->getName());
        $this->executionCounts[$state->getName()]++;
        return $this->executionCounts[$state->getName()];
    }

    public function getExecutionCount(StateInterface $state): int
    {
        return $this->executionCounts[$state->getName()];
    }

    public function getBreadcrumbs(): Vector
    {
        return clone $this->breadcrumbs;
    }

    public function detectExecutionLoop(): Vector
    {
        $executionPath = implode(' ', $this->breadcrumbs->toArray());
        $loopPath = $executionPath;
        $treeBuilder = new SuffixTreeBuilder;
        while (str_word_count($loopPath) > count($this->stateMachine->getStates())) {
            $suffixTree = $treeBuilder->build($loopPath.'$');
            $loopPath = trim($suffixTree->findLongestRepetition());
        }
        return new Vector(explode(' ', $loopPath));
    }
}
