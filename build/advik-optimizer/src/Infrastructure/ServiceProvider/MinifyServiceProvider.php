<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Infrastructure\ServiceProvider;

use AdvikLabs\Optimizer\Container\ContainerInterface;
use AdvikLabs\Optimizer\Domain\Minify\Minifier\CssMinifier;
use AdvikLabs\Optimizer\Domain\Minify\Minifier\JsMinifier;
use AdvikLabs\Optimizer\Domain\Minify\Minifier\HtmlMinifier;
use AdvikLabs\Optimizer\Domain\Minify\Repository\CriticalCssRepository;
use AdvikLabs\Optimizer\Domain\Minify\Service\CriticalCssService;
use AdvikLabs\Optimizer\Domain\Minify\Service\CriticalCssInjector;
use AdvikLabs\Optimizer\Domain\Minify\Service\MinifyService;
use AdvikLabs\Optimizer\Domain\Minify\Service\MinifyRollbackGuard;
use AdvikLabs\Optimizer\Domain\Minify\Service\MinifySavingsService;
use AdvikLabs\Optimizer\Domain\Minify\Service\AssetCombineService;

class MinifyServiceProvider extends AbstractServiceProvider {

	public function register( ContainerInterface $container ): void {
		$container->singleton(
			CssMinifier::class,
			function () {
				return new CssMinifier();
			}
		);

		$container->singleton(
			JsMinifier::class,
			function () {
				return new JsMinifier();
			}
		);

		$container->singleton(
			HtmlMinifier::class,
			function () {
				return new HtmlMinifier();
			}
		);

		$container->singleton(
			CriticalCssRepository::class,
			function () {
				global $wpdb;
				return new CriticalCssRepository( $wpdb );
			}
		);

		$container->singleton(
			CriticalCssService::class,
			function ( ContainerInterface $c ) {
				return new CriticalCssService(
					$c->get( CriticalCssRepository::class )
				);
			}
		);

		$container->singleton(
			CriticalCssInjector::class,
			function ( ContainerInterface $c ) {
				return new CriticalCssInjector(
					$c->get( CriticalCssService::class ),
					$c->get( CssMinifier::class )
				);
			}
		);

		$container->singleton(
			MinifyService::class,
			function ( ContainerInterface $c ) {
				return new MinifyService(
					$c->get( CssMinifier::class ),
					$c->get( JsMinifier::class ),
					$c->get( HtmlMinifier::class )
				);
			}
		);

		$container->singleton(
			MinifyRollbackGuard::class,
			function () {
				return new MinifyRollbackGuard();
			}
		);

		$container->singleton(
			MinifySavingsService::class,
			function () {
				return new MinifySavingsService();
			}
		);

		$container->singleton(
			AssetCombineService::class,
			function () {
				return new AssetCombineService();
			}
		);
	}
}
