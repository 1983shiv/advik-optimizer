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

	public function testMinifyPreservesUrlInsideString(): void {
		$minifier = new JsMinifier();
		$input    = 'var url = "https://example.com/path?q=1";';
		$expected = 'var url="https://example.com/path?q=1";';

		$this->assertEquals( $expected, $minifier->minify( $input ) );
	}

	public function testMinifyPreservesWhitespaceInsideString(): void {
		$minifier = new JsMinifier();
		$input    = "var msg = 'hello world';";
		$expected = "var msg='hello world';";

		$this->assertEquals( $expected, $minifier->minify( $input ) );
	}

	public function testMinifyPreservesRegexLiteralContainingSlashes(): void {
		$minifier = new JsMinifier();
		$input    = 'var re = /https?:\\/\\/example\\.com\\//;';
		$expected = 'var re=/https?:\\/\\/example\\.com\\//;';

		$this->assertEquals( $expected, $minifier->minify( $input ) );
	}

	public function testMinifyTreatsSlashAfterAssignmentAsRegex(): void {
		$minifier = new JsMinifier();
		$input    = 'var re = /foo\\/bar/; var x = a / b;';
		$expected = 'var re=/foo\\/bar/;var x=a/b;';

		$this->assertEquals( $expected, $minifier->minify( $input ) );
	}

	public function testMinifyPreservesTemplateLiteral(): void {
		$minifier = new JsMinifier();
		$input    = 'var t = `http://example.com`; foo();';
		$expected = 'var t=`http://example.com`;foo();';

		$this->assertEquals( $expected, $minifier->minify( $input ) );
	}

	public function testMinifyKeepsCommentAfterWordChars(): void {
		$minifier = new JsMinifier();
		$input    = "var x=1; // trailing\nfoo();";
		$expected = 'var x=1;foo();';

		$this->assertEquals( $expected, $minifier->minify( $input ) );
	}
}
