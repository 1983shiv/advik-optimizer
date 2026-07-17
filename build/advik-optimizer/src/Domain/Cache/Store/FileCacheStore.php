<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Domain\Cache\Store;

use AdvikLabs\Optimizer\Domain\Cache\Contract\CacheStoreInterface;

class FileCacheStore implements CacheStoreInterface {

	private string $baseDir;

	public function __construct( ?string $baseDir = null ) {
		if ( null !== $baseDir ) {
			$this->baseDir = $baseDir;
		} else {
			$uploadDir     = wp_upload_dir();
			$this->baseDir = $uploadDir['basedir'] . '/advik-optimizer/cache';
		}
	}

	public function get( string $key ): ?string {
		$path = $this->resolvePath( $key );

		if ( ! file_exists( $path ) ) {
			return null;
		}

		$data = file_get_contents( $path );

		if ( false === $data ) {
			return null;
		}

		$entry = json_decode( $data, true );

		if ( ! is_array( $entry ) || ! isset( $entry['expires_at'] ) || $entry['expires_at'] < time() ) {
			$this->delete( $key );
			return null;
		}

		return $entry['html'] ?? null;
	}

	public function put( string $key, string $data, int $ttl ): bool {
		$path = $this->resolvePath( $key );
		$dir  = dirname( $path );

		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}

		$entry = wp_json_encode(
			[
				'html'       => $data,
				'expires_at' => time() + $ttl,
				'created_at' => time(),
			]
		);

		return false !== file_put_contents( $path, $entry, LOCK_EX );
	}

	public function delete( string $key ): bool {
		$path = $this->resolvePath( $key );

		if ( file_exists( $path ) ) {
			return wp_delete_file( $path );
		}

		return true;
	}

	public function flush(): bool {
		if ( ! is_dir( $this->baseDir ) ) {
			return true;
		}

		$files = glob( $this->baseDir . '/*.cache' );

		if ( false === $files ) {
			return false;
		}

		foreach ( $files as $file ) {
			wp_delete_file( $file );
		}

		return true;
	}

	public function getBaseDir(): string {
		return $this->baseDir;
	}

	private function resolvePath( string $key ): string {
		return $this->baseDir . '/' . md5( $key ) . '.cache';
	}
}
