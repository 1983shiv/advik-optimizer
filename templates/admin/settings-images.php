<div class="advik-settings-panel">
    <?php if ( isset( $_GET['saved'] ) && '1' === $_GET['saved'] ) : ?>
        <div class="notice notice-success is-dismissible"><p><?php echo esc_html__( 'Settings saved.', 'advik-optimizer' ); ?></p></div>
    <?php endif; ?>
    <?php if ( isset( $_GET['bulk_done'] ) ) : ?>
        <div class="notice notice-success is-dismissible"><p><?php echo esc_html( sprintf( __( 'Bulk optimization complete. %d images processed.', 'advik-optimizer' ), (int) $_GET['bulk_done'] ) ); ?></p></div>
    <?php endif; ?>

    <div class="advik-section">
        <h2 class="advik-section-title"><?php echo esc_html__( 'Image Compression', 'advik-optimizer' ); ?></h2>

        <div class="advik-field">
            <label class="advik-toggle">
                <input type="checkbox" name="module_images" value="1" <?php checked( ! empty( $settings['module_images'] ) ); ?>>
                <span class="advik-toggle-track"></span>
                <span class="advik-toggle-label"><?php echo esc_html__( 'Enable image optimization', 'advik-optimizer' ); ?></span>
            </label>
            <p class="advik-field-help"><?php echo esc_html__( 'Automatically converts uploaded JPEG, PNG, and GIF images to WebP format.', 'advik-optimizer' ); ?></p>
        </div>

        <div class="advik-field">
            <label for="image_quality"><?php echo esc_html__( 'Compression quality', 'advik-optimizer' ); ?></label>
            <div class="advik-range-wrapper">
                <input type="range" id="image_quality" name="image_quality" class="advik-range"
                    min="20" max="100" value="<?php echo esc_attr( $settings['image_quality'] ?? 82 ); ?>"
                    oninput="document.getElementById('image_quality_value').textContent=this.value">
                <span class="advik-range-value" id="image_quality_value"><?php echo esc_html( $settings['image_quality'] ?? 82 ); ?></span>
            </div>
            <p class="advik-field-help"><?php echo esc_html__( 'Higher quality keeps more detail; lower quality saves more space.', 'advik-optimizer' ); ?></p>
        </div>

        <div class="advik-field">
            <label class="advik-toggle">
                <input type="checkbox" name="image_lazy_loading" value="1" <?php checked( ! empty( $settings['image_lazy_loading'] ) ); ?>>
                <span class="advik-toggle-track"></span>
                <span class="advik-toggle-label"><?php echo esc_html__( 'Enable lazy loading', 'advik-optimizer' ); ?></span>
            </label>
            <p class="advik-field-help"><?php echo esc_html__( 'Adds loading="lazy" to content images. Featured images are excluded automatically.', 'advik-optimizer' ); ?></p>
        </div>
    </div>

    <div class="advik-section">
        <h2 class="advik-section-title"><?php echo esc_html__( 'Media Library', 'advik-optimizer' ); ?></h2>
        <div class="advik-actions" style="margin-bottom:20px;">
            <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=advik_optimizer_bulk_optimize' ), 'advik_optimizer_bulk_optimize' ) ); ?>" class="button button-primary" id="advik-bulk-optimize-btn">
                <?php echo esc_html__( 'Bulk Optimize Media Library', 'advik-optimizer' ); ?>
            </a>
            <span id="advik-bulk-progress" style="display:none;font-size:12px;color:#5B6570;">
                <?php echo esc_html__( 'Optimizing…', 'advik-optimizer' ); ?>
            </span>
        </div>

        <?php $queue = $queue ?? []; ?>
        <?php if ( ! empty( $queue ) ) : ?>
        <div class="advik-image-queue">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php echo esc_html__( 'Image', 'advik-optimizer' ); ?></th>
                        <th><?php echo esc_html__( 'Original Size', 'advik-optimizer' ); ?></th>
                        <th><?php echo esc_html__( 'Optimized Size', 'advik-optimizer' ); ?></th>
                        <th><?php echo esc_html__( 'Savings', 'advik-optimizer' ); ?></th>
                        <th><?php echo esc_html__( 'Status', 'advik-optimizer' ); ?></th>
                        <th><?php echo esc_html__( 'Action', 'advik-optimizer' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $queue as $record ) : ?>
                    <?php $attachment = get_post( $record->getAttachmentId() ); ?>
                    <tr>
                        <td>
                            <?php if ( $attachment ) : ?>
                                <?php echo wp_get_attachment_image( $record->getAttachmentId(), [ 40, 40 ], true ); ?>
                                <a href="<?php echo esc_url( get_edit_post_link( $record->getAttachmentId() ) ); ?>" style="vertical-align:middle;margin-left:8px;">
                                    <?php echo esc_html( $attachment->post_title ?: basename( get_attached_file( $record->getAttachmentId() ) ?: '' ) ); ?>
                                </a>
                            <?php else : ?>
                                <em><?php echo esc_html__( 'Attachment deleted', 'advik-optimizer' ); ?></em>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html( size_format( $record->getOriginalSize() ) ); ?></td>
                        <td><?php echo null !== $record->getOptimizedSize() ? esc_html( size_format( $record->getOptimizedSize() ) ) : '&mdash;'; ?></td>
                        <td>
                            <?php if ( null !== $record->getOptimizedSize() && $record->getOriginalSize() > 0 ) : ?>
                                <?php $savings = $record->getOriginalSize() - $record->getOptimizedSize(); ?>
                                <?php $pct = round( $savings / $record->getOriginalSize() * 100 ); ?>
                                <span class="advik-savings-pct"><?php echo esc_html( $pct ); ?>%</span>
                            <?php else : ?>
                                &mdash;
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="advik-status-badge advik-status-<?php echo esc_attr( $record->getStatus() ); ?>">
                                <?php
                                $statusLabels = [
                                    'pending'    => __( 'Pending', 'advik-optimizer' ),
                                    'processing' => __( 'Optimizing…', 'advik-optimizer' ),
                                    'done'       => __( 'Optimized', 'advik-optimizer' ),
                                    'failed'     => __( 'Failed', 'advik-optimizer' ),
                                    'restored'   => __( 'Restored', 'advik-optimizer' ),
                                ];
                                echo esc_html( $statusLabels[ $record->getStatus() ] ?? $record->getStatus() );
                                ?>
                            </span>
                        </td>
                        <td>
                            <?php if ( 'done' === $record->getStatus() ) : ?>
                                <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=advik_optimizer_restore_image&attachment_id=' . $record->getAttachmentId() ), 'advik_optimizer_restore_image_' . $record->getAttachmentId() ) ); ?>" class="advik-restore-link">
                                    <?php echo esc_html__( 'Restore original', 'advik-optimizer' ); ?>
                                </a>
                            <?php elseif ( 'failed' === $record->getStatus() ) : ?>
                                <span style="color:#EF4444;font-size:12px;"><?php echo esc_html__( 'Couldn\'t process this image.', 'advik-optimizer' ); ?></span>
                            <?php else : ?>
                                &mdash;
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else : ?>
        <p class="advik-empty-hint"><?php echo esc_html__( 'No images have been processed yet. Upload images and they\'ll be optimized automatically.', 'advik-optimizer' ); ?></p>
        <?php endif; ?>
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
