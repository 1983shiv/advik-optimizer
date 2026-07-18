<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Minify\Minifier;

use AdvikLabs\Optimizer\Domain\Minify\Minifier\CssMinifier;
use PHPUnit\Framework\TestCase;

class TestCssMinifier extends TestCase {

	public function testMinifyRemovesComments(): void {
		$minifier = new CssMinifier();
		$input    = '/* comment */ body { color: red; }';
		$expected = 'body{color:red}';

		$this->assertEquals( $expected, $minifier->minify( $input ) );
	}

	public function testMinifyCollapsesWhitespace(): void {
		$minifier = new CssMinifier();
		$input    = "body  {\n  color:   red;\n}";
		$expected = 'body{color:red}';

		$this->assertEquals( $expected, $minifier->minify( $input ) );
	}

	public function testMinifyRemovesTrailingSemicolons(): void {
		$minifier = new CssMinifier();
		$input    = 'body { color: red; background: blue; }';
		$expected = 'body{color:red;background:blue}';

		$this->assertEquals( $expected, $minifier->minify( $input ) );
	}

	public function testMinifyHandlesEmptyInput(): void {
		$minifier = new CssMinifier();

		$this->assertEquals( '', $minifier->minify( '' ) );
	}

	public function testMinifyPreservesImportantContent(): void {
		$minifier = new CssMinifier();
		$input    = '.class { content: "/* not a comment */"; }';
		$expected = '.class{content:""}';

		$this->assertEquals( $expected, $minifier->minify( $input ) );
	}

	public function testMinifyNestedAtRules(): void {
		$minifier = new CssMinifier();
		$input    = '@media (min-width: 768px) { .class { color: red; } }';
		$expected = '@media (min-width:768px){.class{color:red}}';

		$this->assertEquals( $expected, $minifier->minify( $input ) );
	}
}
