<?php

namespace MongoDB\Tests\SpecTests;

use PHPUnit\Framework\TestCase;

class DocumentsMatchConstraintTest extends TestCase
{
    public function testIgnoreExtraKeysInRoot()
    {
        $c = new DocumentsMatchConstraint(['x' => 1, 'y' => ['a' => 1, 'b' => 2]], true, false);

        $this->assertResult(false, $c, ['x' => 1, 'y' => 2], 'Incorrect value');
        $this->assertResult(true, $c, ['x' => 1, 'y' => ['a' => 1, 'b' => 2]], 'Exact match');
        $this->assertResult(true, $c, ['x' => 1, 'y' => ['a' => 1, 'b' => 2], 'z' => 3], 'Extra keys in root are permitted');
        $this->assertResult(false, $c, ['x' => 1, 'y' => ['a' => 1, 'b' => 2, 'c' => 3]], 'Extra keys in embedded are not permitted');
        $this->assertResult(true, $c, ['y' => ['b' => 2, 'a' => 1], 'x' => 1], 'Root and embedded key order is not significant');

        // Arrays are always intepretted as root documents
        $c = new DocumentsMatchConstraint([1, ['a' => 1]], true, false);

        $this->assertResult(false, $c, [1, 2], 'Incorrect value');
        $this->assertResult(true, $c, [1, ['a' => 1]], 'Exact match');
        $this->assertResult(true, $c, [1, ['a' => 1], 3], 'Extra keys in root are permitted');
        $this->assertResult(false, $c, [1, ['a' => 1, 'b' => 2]], 'Extra keys in embedded are not permitted');
    }

    public function testIgnoreExtraKeysInEmbedded()
    {
        $c = new DocumentsMatchConstraint(['x' => 1, 'y' => ['a' => 1, 'b' => 2]], false, true);

        $this->assertResult(false, $c, ['x' => 1, 'y' => 2], 'Incorrect value');
        $this->assertResult(false, $c, ['x' => 1, 'y' => ['a' => 1, 'b' => 3]], 'Incorrect value');
        $this->assertResult(true, $c, ['x' => 1, 'y' => ['a' => 1, 'b' => 2]], 'Exact match');
        $this->assertResult(false, $c, ['x' => 1, 'y' => ['a' => 1, 'b' => 2], 'z' => 3], 'Extra keys in root are not permitted');
        $this->assertResult(true, $c, ['x' => 1, 'y' => ['a' => 1, 'b' => 2, 'c' => 3]], 'Extra keys in embedded are permitted');
        $this->assertResult(true, $c, ['y' => ['b' => 2, 'a' => 1], 'x' => 1], 'Root and embedded Key order is not significant');

        // Arrays are always intepretted as root documents
        $c = new DocumentsMatchConstraint([1, ['a' => 1]], false, true);

        $this->assertResult(false, $c, [1, 2], 'Incorrect value');
        $this->assertResult(true, $c, [1, ['a' => 1]], 'Exact match');
        $this->assertResult(false, $c, [1, ['a' => 1], 3], 'Extra keys in root are not permitted');
        $this->assertResult(true, $c, [1, ['a' => 1, 'b' => 2]], 'Extra keys in embedded are permitted');
        $this->assertResult(false, $c, [1, ['a' => 2]], 'Keys must have the correct value');
    }

    public function testPlaceholders()
    {
        $c = new DocumentsMatchConstraint(['x' => '42', 'y' => 42, 'z' => ['a' => 24]], false, false, [24, 42]);

        $this->assertResult(true, $c, ['x' => '42', 'y' => 'foo', 'z' => ['a' => 1]], 'Placeholders accept any value');
        $this->assertResult(false, $c, ['x' => 42, 'y' => 'foo', 'z' => ['a' => 1]], 'Placeholder type must match');
        $this->assertResult(true, $c, ['x' => '42', 'y' => 42, 'z' => ['a' => 24]], 'Exact match');
    }

    private function assertResult($expectedResult, DocumentsMatchConstraint $constraint, $value, $message)
    {
        $this->assertSame($expectedResult, $constraint->evaluate($value, '', true), $message);
    }
}
