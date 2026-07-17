<div class="advik-settings-panel">
    <?php if ( isset( $_GET['saved'] ) && '1' === $_GET['saved'] ) : ?>
        <div class="notice notice-success is-dismissible"><p><?php echo esc_html__( 'Settings saved.', 'advik-optimizer' ); ?></p></div>
    <?php endif; ?>

    <div class="advik-section">
        <h2 class="advik-section-title"><?php echo esc_html__( 'Monitoring', 'advik-optimizer' ); ?></h2>
        <div class="advik-field">
            <label for="vitals_sampling_rate"><?php echo esc_html__( 'Track % of visitor sessions', 'advik-optimizer' ); ?></label>
            <div class="advik-range-wrapper">
                <input type="range" id="vitals_sampling_rate" name="vitals_sampling_rate" min="0" max="100" step="1"
                    value="<?php echo esc_attr( $settings['vitals_sampling_rate'] ?? 10 ); ?>"
                    class="advik-range" data-prefix="">
                <span class="advik-range-value"><?php echo esc_html( $settings['vitals_sampling_rate'] ?? 10 ); ?>%</span>
            </div>
            <p class="advik-field-help"><?php echo esc_html__( 'Higher sampling gives more accurate data but adds a small script to more page loads.', 'advik-optimizer' ); ?></p>
        </div>
        <div class="advik-field">
            <label for="vitals_psi_api_key"><?php echo esc_html__( 'PageSpeed Insights API Key', 'advik-optimizer' ); ?></label>
            <input type="text" id="vitals_psi_api_key" name="vitals_psi_api_key" class="advik-text-input"
                value="<?php echo esc_attr( $settings['vitals_psi_api_key'] ?? '' ); ?>"
                placeholder="AIzaSy...">
            <p class="advik-field-help"><?php echo esc_html__( 'Required for automated lab scans.', 'advik-optimizer' ); ?> <a href="https://developers.google.com/speed/docs/insights/v5/get-started#APIKey" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Get a free key from Google Cloud Console', 'advik-optimizer' ); ?></a>.</p>
        </div>
    </div>

    <div class="advik-section">
        <h2 class="advik-section-title"><?php echo esc_html__( 'Get notified when scores drop', 'advik-optimizer' ); ?></h2>
        <div class="advik-field">
            <label for="vitals_alert_lcp"><?php echo esc_html__( 'Alert me if LCP goes above (seconds)', 'advik-optimizer' ); ?></label>
            <input type="number" id="vitals_alert_lcp" name="vitals_alert_lcp" class="advik-text-input advik-input-sm"
                value="<?php echo esc_attr( $settings['vitals_alert_lcp'] ?? 2.5 ); ?>" step="0.1" min="0.5" max="10">
        </div>
        <div class="advik-field">
            <label for="vitals_alert_cls"><?php echo esc_html__( 'Alert me if CLS goes above', 'advik-optimizer' ); ?></label>
            <input type="number" id="vitals_alert_cls" name="vitals_alert_cls" class="advik-text-input advik-input-sm"
                value="<?php echo esc_attr( $settings['vitals_alert_cls'] ?? 0.1 ); ?>" step="0.01" min="0.01" max="1">
        </div>
        <div class="advik-field">
            <label for="vitals_alert_inp"><?php echo esc_html__( 'Alert me if INP goes above (ms)', 'advik-optimizer' ); ?></label>
            <input type="number" id="vitals_alert_inp" name="vitals_alert_inp" class="advik-text-input advik-input-sm"
                value="<?php echo esc_attr( $settings['vitals_alert_inp'] ?? 200 ); ?>" step="10" min="50" max="1000">
        </div>
        <div class="advik-field">
            <label class="advik-toggle">
                <input type="checkbox" name="vitals_alert_email" value="1" <?php checked( ! empty( $settings['vitals_alert_email'] ) ); ?>
                    class="advik-toggle-trigger" data-target="vitals_alert_email">
                <span class="advik-toggle-track"></span>
                <span class="advik-toggle-label"><?php echo esc_html__( 'Email me', 'advik-optimizer' ); ?></span>
            </label>
        </div>
        <div class="advik-field advik-field-conditional" id="vitals_alert_email_field" <?php echo empty( $settings['vitals_alert_email'] ) ? 'style="display:none"' : ''; ?>>
            <label for="vitals_alert_email_address"><?php echo esc_html__( 'Email address', 'advik-optimizer' ); ?></label>
            <input type="email" id="vitals_alert_email_address" name="vitals_alert_email_address" class="advik-text-input"
                value="<?php echo esc_attr( $settings['vitals_alert_email_address'] ?? '' ); ?>"
                placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>">
            <p class="advik-field-help"><?php echo esc_html__( 'Leave empty to use the WordPress admin email.', 'advik-optimizer' ); ?></p>
        </div>
        <div class="advik-field">
            <label class="advik-toggle">
                <input type="checkbox" name="vitals_alert_webhook" value="1" <?php checked( ! empty( $settings['vitals_alert_webhook'] ) ); ?>
                    class="advik-toggle-trigger" data-target="vitals_webhook_url">
                <span class="advik-toggle-track"></span>
                <span class="advik-toggle-label"><?php echo esc_html__( 'Send to a webhook', 'advik-optimizer' ); ?></span>
            </label>
        </div>
        <div class="advik-field advik-field-conditional" id="vitals_webhook_url_field" <?php echo empty( $settings['vitals_alert_webhook'] ) ? 'style="display:none"' : ''; ?>>
            <label for="vitals_webhook_url"><?php echo esc_html__( 'Webhook URL', 'advik-optimizer' ); ?></label>
            <input type="url" id="vitals_webhook_url" name="vitals_webhook_url" class="advik-text-input"
                value="<?php echo esc_attr( $settings['vitals_webhook_url'] ?? '' ); ?>"
                placeholder="https://hooks.example.com/alert">
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
