<div class="advik-settings-panel">
    <?php if ( isset( $_GET['saved'] ) && '1' === $_GET['saved'] ) : ?>
        <div class="notice notice-success is-dismissible"><p><?php echo esc_html__( 'Settings saved.', 'advik-optimizer' ); ?></p></div>
    <?php endif; ?>
    <div class="advik-section">
        <h2 class="advik-section-title"><?php echo esc_html__('Page Caching', 'advik-optimizer'); ?></h2>
        <div class="advik-field">
            <label class="advik-toggle">
                <input type="checkbox" name="module_cache" value="1" <?php checked(! empty($settings['module_cache'])); ?>>
                <span class="advik-toggle-track"></span>
                <span class="advik-toggle-label"><?php echo esc_html__('Enable page caching', 'advik-optimizer'); ?></span>
            </label>
            <p class="advik-field-help"><?php echo esc_html__('Serves a saved copy of your pages to visitors instead of rebuilding them on every request.', 'advik-optimizer'); ?></p>
        </div>
    </div>

    <div class="advik-section">
        <h2 class="advik-section-title"><?php echo esc_html__('Exclusion Rules', 'advik-optimizer'); ?></h2>
        <div class="advik-field">
            <label class="advik-toggle">
                <input type="checkbox" name="exclude_logged_in" value="1" <?php checked(! empty($settings['exclude_logged_in'])); ?>>
                <span class="advik-toggle-track"></span>
                <span class="advik-toggle-label"><?php echo esc_html__('Never cache pages for logged-in visitors', 'advik-optimizer'); ?></span>
            </label>
        </div>
        <div class="advik-field">
            <label for="excluded_urls"><?php echo esc_html__('Never cache these URLs', 'advik-optimizer'); ?></label>
            <textarea id="excluded_urls" name="excluded_urls" rows="4" class="advik-textarea"><?php echo esc_textarea($settings['excluded_urls'] ?? ''); ?></textarea>
            <p class="advik-field-help"><?php echo esc_html__('One pattern per line, e.g. /cart/*', 'advik-optimizer'); ?></p>
        </div>
    </div>

    <div class="advik-section">
        <h2 class="advik-section-title"><?php echo esc_html__('Cache Warming', 'advik-optimizer'); ?></h2>
        <div class="advik-field">
            <label class="advik-toggle">
                <input type="checkbox" name="cache_warming" value="1" <?php checked(! empty($settings['cache_warming'])); ?>>
                <span class="advik-toggle-track"></span>
                <span class="advik-toggle-label"><?php echo esc_html__('Automatically rebuild cache after it\'s cleared', 'advik-optimizer'); ?></span>
            </label>
        </div>
    </div>

    <div class="advik-section">
        <div class="advik-actions">
            <button type="submit" class="button button-secondary" name="purge_cache" value="1">
                <?php echo esc_html__('Purge Cache Now', 'advik-optimizer'); ?>
            </button>
            <button type="submit" class="button button-primary">
                <?php echo esc_html__('Save Settings', 'advik-optimizer'); ?>
            </button>
        </div>
    </div>
</div>
</form></div>
