<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Tests\Fixture;

use Daikon\StateMachine\Param\InputInterface;
use Daikon\StateMachine\Param\OutputInterface;
use Daikon\StateMachine\Transition\TransitionInterface;

/**
 * @codeCoverageIgnore
 */
final class InactiveTransition implements TransitionInterface
{
    private $from;

    private $to;

    public function __construct(string $from, string $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function getLabel(): string
    {
        return 'inactive';
    }

    public function isActivatedBy(InputInterface $input, OutputInterface $output): bool
    {
        return false;
    }

    public function getConstraints(): array
    {
        return [];
    }

    public function hasConstraints(): bool
    {
        return false;
    }
}
