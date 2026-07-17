<?php

declare(strict_types=1);

namespace AdvikLabs\Optimizer\Admin\View;

class SettingsView extends AbstractView {

	public function render( string $template, array $data = [] ): void {
		$this->renderTemplateHeader( $data );
		parent::render( $template, $data );
	}

	private function renderTemplateHeader( array $data ): void {
		$tab = $data['tab'] ?? 'cache';
		?>
		<div class="wrap advik-optimizer-settings">
			<h1><?php echo esc_html__( 'Advik Optimizer', 'advik-optimizer' ); ?></h1>
			<nav class="advik-tabs">
				<?php $this->renderTab( 'cache', __( 'Cache', 'advik-optimizer' ), $tab ); ?>
				<span class="advik-tab-disabled"><?php echo esc_html__( 'Images', 'advik-optimizer' ); ?></span>
				<span class="advik-tab-disabled"><?php echo esc_html__( 'Minify', 'advik-optimizer' ); ?></span>
				<?php $this->renderTab( 'vitals', __( 'Core Web Vitals', 'advik-optimizer' ), $tab ); ?>
				<span class="advik-tab-disabled"><?php echo esc_html__( 'SEO', 'advik-optimizer' ); ?></span>
				<span class="advik-tab-disabled"><?php echo esc_html__( 'CDN & Database', 'advik-optimizer' ); ?></span>
			</nav>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php?action=advik_optimizer_save_settings' ) ); ?>">
				<?php wp_nonce_field( 'advik_optimizer_save_settings' ); ?>
				<input type="hidden" name="tab" value="<?php echo esc_attr( $tab ); ?>">
		<?php
	}

	private function renderTab( string $slug, string $label, string $active ): void {
		$url   = add_query_arg(
			[
				'page' => 'advik-optimizer',
				'tab'  => $slug,
			],
			admin_url( 'admin.php' )
		);
		$class = $slug === $active ? 'advik-tab active' : 'advik-tab';
		echo '<a href="' . esc_url( $url ) . '" class="' . esc_attr( $class ) . '">' . esc_html( $label ) . '</a>';
	}
}
