<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Minify\Model;

use AdvikLabs\Optimizer\Domain\Minify\Model\CriticalCssRule;
use PHPUnit\Framework\TestCase;

class TestCriticalCssRule extends TestCase {

	public function testGettersReturnConstructedValues(): void {
		$rule = new CriticalCssRule( 7, 'front_page', 'body{color:red}', '2024-01-01 00:00:00' );

		$this->assertSame( 7, $rule->getId() );
		$this->assertSame( 'front_page', $rule->getTemplate() );
		$this->assertSame( 'body{color:red}', $rule->getCss() );
		$this->assertSame( '2024-01-01 00:00:00', $rule->getCreatedAt() );
	}

	public function testIdCanBeNull(): void {
		$rule = new CriticalCssRule( null, 'archive', '', '2024-01-01 00:00:00' );

		$this->assertNull( $rule->getId() );
	}
}