<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Tests\Unit\Domain\Cache;

use AdvikLabs\Optimizer\Domain\Cache\Store\FileCacheStore;
use MockWP;
use PHPUnit\Framework\TestCase;

class TestFileCacheStore extends TestCase {
	private string $cacheDir;

	protected function setUp(): void {
		parent::setUp();
		MockWP::reset();

		$this->cacheDir = sys_get_temp_dir() . '/advik-test-cache-' . uniqid();
		MockWP::set( 'wp_upload_basedir', dirname( $this->cacheDir ) );
	}

	protected function tearDown(): void {
		$this->removeDir( $this->cacheDir );
		MockWP::reset();
		parent::tearDown();
	}

	private function removeDir( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}
		$files = glob( $dir . '/*' );
		if ( false !== $files ) {
			foreach ( $files as $file ) {
				is_file( $file ) && @unlink( $file );
			}
		}
		@rmdir( $dir );
	}

	public function testPutAndGet(): void {
		$store = new FileCacheStore( $this->cacheDir );

		$result = $store->put( 'test-key', '<html>data</html>', 3600 );
		$this->assertTrue( $result );

		$html = $store->get( 'test-key' );
		$this->assertSame( '<html>data</html>', $html );
	}

	public function testGetReturnsNullForMissingKey(): void {
		$store = new FileCacheStore( $this->cacheDir );

		$this->assertNull( $store->get( 'nonexistent' ) );
	}

	public function testDeleteRemovesCachedFile(): void {
		$store = new FileCacheStore( $this->cacheDir );
		$store->put( 'delete-key', 'data', 3600 );

		$this->assertNotNull( $store->get( 'delete-key' ) );

		$result = $store->delete( 'delete-key' );
		$this->assertTrue( $result );

		$this->assertNull( $store->get( 'delete-key' ) );
	}

	public function testDeleteReturnsTrueForNonExistentKey(): void {
		$store = new FileCacheStore( $this->cacheDir );

		$this->assertTrue( $store->delete( 'ghost-key' ) );
	}

	public function testFlushRemovesAllCacheFiles(): void {
		$store = new FileCacheStore( $this->cacheDir );
		$store->put( 'key1', 'data1', 3600 );
		$store->put( 'key2', 'data2', 3600 );

		$this->assertNotNull( $store->get( 'key1' ) );
		$this->assertNotNull( $store->get( 'key2' ) );

		$result = $store->flush();
		$this->assertTrue( $result );

		$this->assertNull( $store->get( 'key1' ) );
		$this->assertNull( $store->get( 'key2' ) );
	}

	public function testFlushReturnsTrueWhenNoCacheDir(): void {
		$store = new FileCacheStore( '/nonexistent/path/cache' );

		$this->assertTrue( $store->flush() );
	}

	public function testGetReturnsNullForExpiredEntry(): void {
		$store = new FileCacheStore( $this->cacheDir );
		$store->put( 'expires-fast', 'data', -1 );

		$this->assertNull( $store->get( 'expires-fast' ) );
	}

	public function testGetBaseDir(): void {
		$store = new FileCacheStore( $this->cacheDir );

		$this->assertSame( $this->cacheDir, $store->getBaseDir() );
	}
}
