<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Param;

use Daikon\StateMachine\Param\ParamHolderTrait;

final class Settings implements ParamHolderInterface
{
    use ParamHolderTrait;

    /**
     * @param mixed[] $params
     */
    public function __construct(array $params = [])
    {
        $this->params = $params;
    }
}
