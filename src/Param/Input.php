<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Param;

final class Input implements InputInterface
{
    use ParamHolderTrait;

    private $event;

    public function __construct(array $params = [], string $event = '')
    {
        $this->params = $params;
        $this->event = $event;
    }

    public function getEvent(): string
    {
        return $this->event;
    }

    public function hasEvent(): bool
    {
        return !empty($this->event);
    }

    public function withEvent(string $event): InputInterface
    {
        $clone = clone $this;
        $clone->event = $event;
        return $clone;
    }

    public static function fromOutput(OutputInterface $output): InputInterface
    {
        return new static($output->toArray()['params']);
    }
}
