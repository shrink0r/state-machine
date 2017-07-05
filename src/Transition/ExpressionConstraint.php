<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Transition;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Daikon\StateMachine\Param\InputInterface;
use Daikon\StateMachine\Param\OutputInterface;

final class ExpressionConstraint implements ConstraintInterface
{
    private $expression;

    private $engine;

    public function __construct(string $expression, ExpressionLanguage $engine)
    {
        $this->expression = $expression;
        $this->engine = $engine;
    }

    public function accepts(InputInterface $input, OutputInterface $output): bool
    {
        return (bool)$this->engine->evaluate(
            $this->expression,
            [ 'event' => $input->getEvent(), 'input' => $input, 'output' => $output ]
        );
    }

    public function __toString(): string
    {
        return str_replace('and', "\nand", $this->expression);
    }
}
