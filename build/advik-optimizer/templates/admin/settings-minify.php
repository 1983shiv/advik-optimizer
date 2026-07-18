<div class="advik-settings-panel">
    <?php if ( isset( $_GET['saved'] ) && '1' === $_GET['saved'] ) : ?>
        <div class="notice notice-success is-dismissible"><p><?php echo esc_html__( 'Settings saved.', 'advik-optimizer' ); ?></p></div>
    <?php endif; ?>
    <?php if ( isset( $_GET['re-enabled'] ) && '1' === $_GET['re-enabled'] ) : ?>
        <div class="notice notice-success is-dismissible"><p><?php echo esc_html__( 'Minification re-enabled.', 'advik-optimizer' ); ?></p></div>
    <?php endif; ?>

    <div class="advik-section">
        <h2 class="advik-section-title"><?php echo esc_html__( 'Asset Minification', 'advik-optimizer' ); ?></h2>

        <div class="advik-field">
            <label class="advik-toggle">
                <input type="checkbox" name="module_minify" value="1" <?php checked( ! empty( $settings['module_minify'] ) ); ?>>
                <span class="advik-toggle-track"></span>
                <span class="advik-toggle-label"><?php echo esc_html__( 'Enable asset minification', 'advik-optimizer' ); ?></span>
            </label>
            <p class="advik-field-help"><?php echo esc_html__( 'Minify CSS, JavaScript, and HTML to reduce page size and improve load times.', 'advik-optimizer' ); ?></p>
        </div>

        <div class="advik-field">
            <label class="advik-toggle">
                <input type="checkbox" name="minify_css" value="1" <?php checked( ! empty( $settings['minify_css'] ) ); ?>>
                <span class="advik-toggle-track"></span>
                <span class="advik-toggle-label"><?php echo esc_html__( 'Minify CSS', 'advik-optimizer' ); ?></span>
            </label>
        </div>

        <div class="advik-field">
            <label class="advik-toggle">
                <input type="checkbox" name="minify_js" value="1" <?php checked( ! empty( $settings['minify_js'] ) ); ?>>
                <span class="advik-toggle-track"></span>
                <span class="advik-toggle-label"><?php echo esc_html__( 'Minify JavaScript', 'advik-optimizer' ); ?></span>
            </label>
        </div>

        <div class="advik-field">
            <label class="advik-toggle">
                <input type="checkbox" name="minify_html" value="1" <?php checked( ! empty( $settings['minify_html'] ) ); ?>>
                <span class="advik-toggle-track"></span>
                <span class="advik-toggle-label"><?php echo esc_html__( 'Minify HTML', 'advik-optimizer' ); ?></span>
            </label>
        </div>
    </div>

    <div class="advik-section">
        <h2 class="advik-section-title"><?php echo esc_html__( 'Exclude from minification', 'advik-optimizer' ); ?></h2>

        <div class="advik-field">
            <label for="minify_exclude_css"><?php echo esc_html__( 'CSS handles to exclude', 'advik-optimizer' ); ?></label>
            <input type="text" id="minify_exclude_css" name="minify_exclude_css" class="advik-text-input"
                value="<?php echo esc_attr( is_string( $settings['minify_exclude_css'] ?? '' ) ? $settings['minify_exclude_css'] : '' ); ?>"
                placeholder="e.g. admin-bar, dashicons">
            <p class="advik-field-help"><?php echo esc_html__( 'Comma-separated list of style handles to skip minification.', 'advik-optimizer' ); ?></p>
        </div>

        <div class="advik-field">
            <label for="minify_exclude_js"><?php echo esc_html__( 'JavaScript handles to exclude', 'advik-optimizer' ); ?></label>
            <input type="text" id="minify_exclude_js" name="minify_exclude_js" class="advik-text-input"
                value="<?php echo esc_attr( is_string( $settings['minify_exclude_js'] ?? '' ) ? $settings['minify_exclude_js'] : '' ); ?>"
                placeholder="e.g. jquery-core, jquery-migrate">
            <p class="advik-field-help"><?php echo esc_html__( 'Comma-separated list of script handles to skip minification.', 'advik-optimizer' ); ?></p>
        </div>
    </div>

    <div class="advik-section">
        <h2 class="advik-section-title"><?php echo esc_html__( 'Critical CSS', 'advik-optimizer' ); ?></h2>

        <?php $lastScan = $lastScanTime ?? null; ?>
        <?php $ruleCount = $ruleCount ?? 0; ?>

        <div class="advik-field">
            <label><?php echo esc_html__( 'Last scanned', 'advik-optimizer' ); ?></label>
            <p style="margin:4px 0;font-size:14px;color:#12161B;">
                <?php if ( $lastScan ) : ?>
                    <?php echo esc_html( sprintf( __( '%s ago', 'advik-optimizer' ), human_time_diff( strtotime( $lastScan ), current_time( 'timestamp' ) ) ) ); ?>
                <?php else : ?>
                    <?php echo esc_html__( 'Never', 'advik-optimizer' ); ?>
                <?php endif; ?>
            </p>
        </div>

        <div class="advik-field">
            <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=advik_optimizer_scan_critical_css' ), 'advik_optimizer_scan_critical_css' ) ); ?>" class="button button-secondary">
                <?php echo esc_html__( 'Rescan Now', 'advik-optimizer' ); ?>
            </a>
            <?php if ( $ruleCount > 0 ) : ?>
                <span style="margin-left:12px;font-size:12px;color:#5B6570;"><?php echo esc_html( sprintf( __( '%d rules stored', 'advik-optimizer' ), $ruleCount ) ); ?></span>
            <?php endif; ?>
        </div>

        <div class="advik-field">
            <div style="background:#FEF9E7;border:1px solid #F5A524;border-radius:8px;padding:12px 16px;display:flex;gap:10px;align-items:flex-start;">
                <span style="color:#F5A524;flex-shrink:0;font-size:16px;">&#x26A0;</span>
                <div>
                    <p style="margin:0;font-size:13px;color:#5B6570;">
                        <?php echo esc_html__( 'If a page ever looks broken after this runs, Advik Optimizer automatically pauses minification for it and notifies you here.', 'advik-optimizer' ); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="advik-section">
        <div class="advik-actions">
            <button type="submit" class="button button-primary">
                <?php echo esc_html__( 'Save Settings', 'advik-optimizer' ); ?>
            </button>
        </div>
    </div>
</div>
</form></div>
