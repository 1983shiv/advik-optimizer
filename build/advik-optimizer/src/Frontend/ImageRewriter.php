<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Frontend;

class ImageRewriter {

	public function __construct() {
	}

	public function registerHooks(): void {
		add_filter( 'wp_get_attachment_image_attributes', [ $this, 'filterImageAttributes' ], 10, 3 );
		add_filter( 'wp_content_img_tag', [ $this, 'filterContentImageTag' ], 10, 3 );
		add_filter( 'the_content', [ $this, 'injectPictureTags' ], 100 );
	}

	public function filterImageAttributes( array $attr, \WP_Post $attachment, $size ): array {
		$settings = get_option( 'advik_optimizer_settings', [] );

		if ( empty( $settings['module_images'] ) ) {
			return $attr;
		}

		if ( $this->isLcpCandidate( $attachment->ID ) ) {
			if ( isset( $attr['loading'] ) && 'lazy' === $attr['loading'] ) {
				unset( $attr['loading'] );
			}

			return $attr;
		}

		if ( ! isset( $attr['loading'] ) ) {
			$attr['loading'] = 'lazy';
		}

		return $attr;
	}

	public function filterContentImageTag( string $filtered_image, string $context, int $attachment_id ): string {
		$settings = get_option( 'advik_optimizer_settings', [] );

		if ( empty( $settings['module_images'] ) ) {
			return $filtered_image;
		}

		if ( $this->isLcpCandidate( $attachment_id ) ) {
			return $filtered_image;
		}

		return $this->wrapWithWebp( $filtered_image, $attachment_id );
	}

	public function injectPictureTags( string $content ): string {
		$settings = get_option( 'advik_optimizer_settings', [] );

		if ( empty( $settings['module_images'] ) ) {
			return $content;
		}

		return preg_replace_callback(
			'/<img[^>]+>/i',
			function ( array $matches ): string {
				$imgTag = $matches[0];

				if ( preg_match( '/loading\s*=\s*"lazy"/i', $imgTag ) ) {
					return $this->maybeWrapWithWebp( $imgTag );
				}

				return $imgTag;
			},
			$content
		);
	}

	private function maybeWrapWithWebp( string $imgTag ): string {
		if ( ! preg_match( '/src\s*=\s*"([^"]+)"/i', $imgTag, $srcMatch ) ) {
			return $imgTag;
		}

		$src    = $srcMatch[1];
		$webpSrc = preg_replace( '/\.(jpe?g|png|gif)$/i', '.webp', $src );

		if ( $webpSrc === $src ) {
			return $imgTag;
		}

		$webpUrl = $this->urlExists( $webpSrc );

		if ( ! $webpUrl ) {
			return $imgTag;
		}

		return '<picture><source srcset="' . esc_url( $webpSrc ) . '" type="image/webp">' . $imgTag . '</picture>';
	}

	private function wrapWithWebp( string $imgTag, int $attachmentId ): string {
		$file = get_attached_file( $attachmentId );

		if ( false === $file ) {
			return $imgTag;
		}

		$info   = pathinfo( $file );
		$webp   = $info['dirname'] . '/' . $info['filename'] . '.webp';

		if ( ! file_exists( $webp ) ) {
			return $imgTag;
		}

		$uploadDir = wp_upload_dir();
		$relPath   = str_replace( wp_normalize_path( $uploadDir['basedir'] ), '', wp_normalize_path( $webp ) );
		$webpUrl   = $uploadDir['baseurl'] . $relPath;

		return '<picture><source srcset="' . esc_url( $webpUrl ) . '" type="image/webp">' . $imgTag . '</picture>';
	}

	private function isLcpCandidate( int $attachmentId ): bool {
		if ( is_singular() ) {
			$post         = get_post();
			$thumbnailId  = (int) get_post_thumbnail_id( $post );

			if ( $thumbnailId === $attachmentId ) {
				return true;
			}

			$content = $post->post_content ?? '';
			$blocks  = parse_blocks( $content );

			foreach ( $blocks as $block ) {
				if ( 'core/image' === ( $block['blockName'] ?? '' ) ) {
					$imgId = (int) ( $block['attrs']['id'] ?? 0 );

					if ( $imgId === $attachmentId ) {
						return true;
					}

					break;
				}
			}
		}

		return false;
	}

	private function urlExists( string $url ): bool {
		$response = wp_remote_head( $url, [ 'timeout' => 5 ] );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$code = wp_remote_retrieve_response_code( $response );

		return $code >= 200 && $code < 400;
	}
}
