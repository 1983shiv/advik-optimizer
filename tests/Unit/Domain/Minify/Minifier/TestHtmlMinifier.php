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
}
