<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Minify\Minifier;

use AdvikLabs\Optimizer\Domain\Minify\Minifier\JsMinifier;
use PHPUnit\Framework\TestCase;

class TestJsMinifier extends TestCase {

	public function testMinifyRemovesSingleLineComments(): void {
		$minifier = new JsMinifier();
		$input    = "var x = 1; // this is a comment\nvar y = 2;";
		$expected = 'var x=1;var y=2;';

		$this->assertEquals( $expected, $minifier->minify( $input ) );
	}

	public function testMinifyRemovesMultiLineComments(): void {
		$minifier = new JsMinifier();
		$input    = 'var x = 1; /* comment */ var y = 2;';
		$expected = 'var x=1;var y=2;';

		$this->assertEquals( $expected, $minifier->minify( $input ) );
	}

	public function testMinifyCollapsesWhitespace(): void {
		$minifier = new JsMinifier();
		$input    = "function  test(  a , b ) {\n  return a + b;\n}";
		$expected = 'function test(a,b){return a+b}';

		$this->assertEquals( $expected, $minifier->minify( $input ) );
	}

	public function testMinifyHandlesEmptyInput(): void {
		$minifier = new JsMinifier();

		$this->assertEquals( '', $minifier->minify( '' ) );
	}

	public function testMinifyHandlesOperators(): void {
		$minifier = new JsMinifier();
		$input    = 'var x = 1 + 2 * 3 / 4;';
		$expected = 'var x=1+2*3/4;';

		$this->assertEquals( $expected, $minifier->minify( $input ) );
	}
}
