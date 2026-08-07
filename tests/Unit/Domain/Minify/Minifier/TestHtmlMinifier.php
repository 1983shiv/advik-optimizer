<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Minify\Minifier;

use AdvikLabs\Optimizer\Domain\Minify\Minifier\HtmlMinifier;
use PHPUnit\Framework\TestCase;

class TestHtmlMinifier extends TestCase {

	public function testMinifyRemovesHtmlComments(): void {
		$minifier = new HtmlMinifier();
		$input    = '<div><!-- comment --><p>text</p></div>';
		$expected = '<div><p>text</p></div>';

		$this->assertEquals( $expected, $minifier->minify( $input ) );
	}

	public function testMinifyCollapsesWhitespace(): void {
		$minifier = new HtmlMinifier();
		$input    = "<div>\n  <p>text</p>\n</div>";
		$expected = '<div><p>text</p></div>';

		$this->assertEquals( $expected, $minifier->minify( $input ) );
	}

	public function testMinifyHandlesEmptyInput(): void {
		$minifier = new HtmlMinifier();

		$this->assertEquals( '', $minifier->minify( '' ) );
	}

	public function testMinifyMultipleTags(): void {
		$minifier = new HtmlMinifier();
		$input    = '<html><head><title>Test</title></head><body><h1>Hello</h1><p>World</p></body></html>';
		$expected = '<html><head><title>Test</title></head><body><h1>Hello</h1><p>World</p></body></html>';

		$this->assertEquals( $expected, $minifier->minify( $input ) );
	}

	public function testMinifyTrimsOuterWhitespace(): void {
		$minifier = new HtmlMinifier();
		$input    = "  \n  <p>text</p>  \n  ";
		$expected = '<p>text</p>';

		$this->assertEquals( $expected, $minifier->minify( $input ) );
	}

	public function testMinifyPreservesScriptContent(): void {
		$minifier = new HtmlMinifier();
		$input    = "<div>\n<script>\nvar url = 'https://example.com/a';\n// keep this\nvar x = 1;\n</script>\n</div>";
		$expected = "<div><script>\nvar url = 'https://example.com/a';\n// keep this\nvar x = 1;\n</script></div>";

		$this->assertEquals( $expected, $minifier->minify( $input ) );
	}

	public function testMinifyPreservesStyleContent(): void {
		$minifier = new HtmlMinifier();
		$input    = "<div>\n<style>\n.foo { color: red; }\n</style>\n</div>";
		$expected = "<div><style>\n.foo { color: red; }\n</style></div>";

		$this->assertEquals( $expected, $minifier->minify( $input ) );
	}

	public function testMinifyDoesNotStripHtmlCommentPlaceholderInsideScript(): void {
		$minifier = new HtmlMinifier();
		$input    = '<script><!--\nvar openHtml = "</div>";\n--></script><p>a</p>';
		$expected = '<script><!--\nvar openHtml = "</div>";\n--></script><p>a</p>';

		$this->assertEquals( $expected, $minifier->minify( $input ) );
	}
}
