<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Tests\Transition;

use Daikon\StateMachine\Param\Input;
use Daikon\StateMachine\Param\Output;
use Daikon\StateMachine\Tests\TestCase;
use Daikon\StateMachine\Transition\ExpressionConstraint;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class ExpressionConstraintTest extends TestCase
{
    public function testToString()
    {
        $constraint = new ExpressionConstraint("input.get('foo')", new ExpressionLanguage);
        $this->assertEquals("input.get('foo')", (string)$constraint);
    }

    public function testAccepts()
    {
        $input = new Input([ 'foo' => 'bar' ]);
        $output = new Output('initial', [ 'foo' => 'baz' ]);
        $constraint = new ExpressionConstraint("input.get('foo') == 'bar'", new ExpressionLanguage);
        $this->assertTrue($constraint->accepts($input, $output));
    }
}
