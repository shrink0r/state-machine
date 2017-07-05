<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Renderer;

use Daikon\StateMachine\StateMachineInterface;
use Ds\Map;

final class DotGraphRenderer implements RendererInterface
{
    public function render(StateMachineInterface $stateMachine)
    {
        $nodeId = 0;
        $nodeIdMap = new Map;
        foreach ($stateMachine->getStates() as $state) {
            $nodeIdMap->put($state->getName(), sprintf('node%d', ++$nodeId));
        }
        return sprintf(
            "digraph \"%s\" {\n    %s\n\n    %s\n}",
            $stateMachine->getName(),
            implode("\n    ", $this->renderStateNodes($stateMachine, $nodeIdMap)),
            implode("\n    ", $this->renderTransitionEdges($stateMachine, $nodeIdMap))
        );
    }

    private function renderStateNodes(StateMachineInterface $stateMachine, Map $nodeIdMap): array
    {
        $stateNodes = [];
        foreach ($stateMachine->getStates() as $stateName => $state) {
            $attributes = sprintf('label="%s"', $stateName);
            $attributes .= ' fontname="Arial" fontsize="13" fontcolor="#000000" color="#607d8b"';
            if ($state->isFinal() || $state->isInitial()) {
                $attributes .= ' style="bold"';
            }
            $stateNodes[] = sprintf('%s [%s];', $nodeIdMap->get($stateName), $attributes);
        }
        return $stateNodes;
    }

    private function renderTransitionEdges(StateMachineInterface $stateMachine, Map $nodeIdMap): array
    {
        $edges = [];
        foreach ($stateMachine->getStateTransitions() as $stateName => $stateTransitions) {
            foreach ($stateTransitions as $transition) {
                $fromNode = $nodeIdMap->get($transition->getFrom());
                $toNode = $nodeIdMap->get($transition->getTo());
                $transitionLabel = (string)$transition;
                $attributes = sprintf('label="%s" ', trim(addslashes($transitionLabel)));
                $attributes .= 'fontname="Arial" fontsize="12" fontcolor="#7f8c8d" color="#2ecc71"';
                $edges[] = sprintf('%s -> %s [%s];', $fromNode, $toNode, $attributes);
            }
        }
        return $edges;
    }
}
