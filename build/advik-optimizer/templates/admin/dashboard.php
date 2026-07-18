<div class="wrap advik-dashboard">
    <?php if ( $notice ) : ?>
    <div class="notice notice-<?php echo esc_attr( $notice[0] ); ?> is-dismissible">
        <p><?php echo wp_kses_post( $notice[1] ); ?></p>
    </div>
    <?php endif; ?>

    <div class="advik-dashboard-header">
        <div class="advik-dashboard-header-left">
            <h1><?php echo esc_html__( 'Optimizer Overview', 'advik-optimizer' ); ?></h1>
            <span class="advik-status-line">
                <?php
                $mobileScores = $deviceData['mobile']['scores'];
                $total = array_sum( $mobileScores );
                if ( $total > 0 ) :
                    echo esc_html__( 'All systems green', 'advik-optimizer' );
                else :
                    echo esc_html__( 'No data yet. Run a scan.', 'advik-optimizer' );
                endif;
                ?>
                &middot; <span class="advik-badge-live"><?php echo esc_html__( 'Live', 'advik-optimizer' ); ?></span>
            </span>
        </div>
        <div class="advik-dashboard-header-right">
            <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=advik_optimizer_scan_now' ), 'advik_optimizer_scan_now' ) ); ?>" class="button button-secondary"><?php echo esc_html__( 'Run Scan Now', 'advik-optimizer' ); ?></a>
        </div>
    </div>

    <?php if ( $total > 0 ) : ?>
    <div class="advik-device-tabs">
        <button class="advik-device-tab active" data-device="mobile"><?php echo esc_html__( 'Mobile', 'advik-optimizer' ); ?></button>
        <button class="advik-device-tab" data-device="desktop"><?php echo esc_html__( 'Desktop', 'advik-optimizer' ); ?></button>
    </div>

    <?php foreach ( [ 'mobile', 'desktop' ] as $device ) : ?>
    <?php $scores = $deviceData[ $device ]['scores']; ?>
    <?php $metrics = $deviceData[ $device ]['metrics']; ?>
    <?php $trends = $deviceData[ $device ]['trends']; ?>
    <?php $audits = $deviceData[ $device ]['audits']; ?>
    <div class="advik-device-panel<?php echo 'mobile' === $device ? ' active' : ''; ?>" data-device="<?php echo esc_attr( $device ); ?>">
        <div class="advik-section advik-score-rings-row">
            <?php foreach ( [ 'performance', 'seo', 'accessibility', 'best_practices' ] as $type ) : ?>
                <?php $score = $scores[ $type ] ?? 0; ?>
                <?php $labelMap = [ 'performance' => __( 'Performance', 'advik-optimizer' ), 'seo' => __( 'SEO', 'advik-optimizer' ), 'accessibility' => __( 'Accessibility', 'advik-optimizer' ), 'best_practices' => __( 'Best Practices', 'advik-optimizer' ) ]; ?>
                <?php $color = $score >= 90 ? '#1FCB8E' : ( $score >= 50 ? '#F5A524' : '#EF4444' ); ?>
                <?php $dashOffset = 100 - $score; ?>
                <div class="advik-score-ring">
                    <div class="advik-score-ring-visual">
                        <svg width="96" height="96" viewBox="0 0 120 120">
                            <circle cx="60" cy="60" r="54" fill="none" stroke="#E3E8E6" stroke-width="8"/>
                            <circle cx="60" cy="60" r="54" fill="none" stroke="<?php echo esc_attr( $color ); ?>" stroke-width="8"
                                stroke-dasharray="339.292" stroke-dashoffset="<?php echo esc_attr( 339.292 * $dashOffset / 100 ); ?>"
                                stroke-linecap="round" transform="rotate(-90 60 60)" class="advik-ring-fill"/>
                        </svg>
                        <span class="advik-ring-value"><?php echo esc_html( $score ); ?></span>
                    </div>
                    <span class="advik-ring-label"><?php echo esc_html( $labelMap[ $type ] ); ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="advik-card advik-card-trend">
            <div class="advik-card-header">
                <h2><?php echo esc_html__( 'Page Load Trend', 'advik-optimizer' ); ?></h2>
                <?php if ( $metrics['lcp'] ) : ?>
                    <span class="advik-card-stat"><?php echo esc_html( round( $metrics['lcp'], 1 ) ); ?>s avg</span>
                <?php endif; ?>
            </div>
            <div class="advik-trend-ranges">
                <button class="advik-trend-range active" data-range="7d">7 <?php echo esc_html__( 'days', 'advik-optimizer' ); ?></button>
                <button class="advik-trend-range" data-range="30d">30 <?php echo esc_html__( 'days', 'advik-optimizer' ); ?></button>
                <button class="advik-trend-range" data-range="90d">90 <?php echo esc_html__( 'days', 'advik-optimizer' ); ?></button>
            </div>
            <div class="advik-metric-deltas">
                <?php
                $metricDefs = [
                    'lcp' => [ 'label' => 'LCP', 'unit' => 's', 'good' => 2.5, 'poor' => 4.0, 'invert' => true ],
                    'cls' => [ 'label' => 'CLS', 'unit' => '', 'good' => 0.1, 'poor' => 0.25, 'invert' => true ],
                    'inp' => [ 'label' => 'INP', 'unit' => 'ms', 'good' => 200, 'poor' => 500, 'invert' => true ],
                ];
                $activeRange = '7d';
                foreach ( $metricDefs as $key => $def ) :
                    $value = $metrics[ $key ];
                    $isGood = null !== $value && $value <= $def['good'];
                    $isPoor = null !== $value && $value > $def['poor'];
                    $statusClass = null === $value ? '' : ( $isGood ? 'good' : ( $isPoor ? 'poor' : 'warning' ) );
                    $formatVal = 'cls' === $key ? number_format( $value ?? 0, 3 ) : ( 'inp' === $key ? round( $value ?? 0 ) : ( null !== $value ? round( $value, 1 ) : '&ndash;' ) );
                    $displayVal = null !== $value ? $formatVal : '&ndash;';
                    $displayUnit = null !== $value && 'cls' !== $key ? $def['unit'] : '';
                ?>
                <div class="advik-metric-delta <?php echo esc_attr( $statusClass ); ?>">
                    <div class="advik-metric-delta-header">
                        <span class="advik-metric-delta-label"><?php echo esc_html( $def['label'] ); ?></span>
                        <?php if ( null !== $value ) : ?>
                            <span class="advik-metric-delta-badge <?php echo esc_attr( $statusClass ); ?>">
                                <?php echo $isGood ? '&#x25BC;' : '&#x25B2;'; ?>
                                <?php echo esc_html( $isGood ? 'Good' : ( $isPoor ? 'Poor' : 'Needs work' ) ); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <span class="advik-metric-delta-value"><?php echo $displayVal; ?><?php echo esc_html( $displayUnit ); ?></span>
                    <?php foreach ( [ '7d', '30d', '90d' ] as $range ) : ?>
                        <?php $trendData = $trends[ $key ][ $range ] ?? []; ?>
                        <?php if ( ! empty( $trendData ) ) : ?>
                        <div class="advik-sparkline<?php echo $range === $activeRange ? '' : ' advik-sparkline-hidden'; ?>" data-range="<?php echo esc_attr( $range ); ?>" data-metric="<?php echo esc_attr( $key ); ?>">
                            <?php
                            $vals = array_map( fn( $p ) => (float) $p['value'], $trendData );
                            $min  = min( $vals );
                            $max  = max( $vals );
                            $r = ( $max - $min ) ?: 1;
                            $points = [];
                            $count = count( $vals );
                            $w = 120;
                            $h = 24;
                            foreach ( $vals as $i => $v ) {
                                $x = round( $i / max( 1, $count - 1 ) * $w, 1 );
                                $y = round( $h - ( ( $v - $min ) / $r ) * $h, 1 );
                                $points[] = "{$x},{$y}";
                            }
                            $lineColor = $isGood ? '#1FCB8E' : ( $isPoor ? '#EF4444' : '#F5A524' );
                            ?>
                            <svg width="<?php echo esc_attr( $w ); ?>" height="<?php echo esc_attr( $h ); ?>" viewBox="0 0 <?php echo esc_attr( $w ); ?> <?php echo esc_attr( $h ); ?>">
                                <polyline points="<?php echo esc_attr( implode( ' ', $points ) ); ?>" fill="none" stroke="<?php echo esc_attr( $lineColor ); ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ( ! empty( $audits ) ) : ?>
        <div class="advik-card advik-card-insights">
            <h2><?php echo esc_html__( 'Actionable Insights', 'advik-optimizer' ); ?></h2>
            <div class="advik-insight-list">
                <?php foreach ( $audits as $audit ) : ?>
                <?php $sev = $audit->getSeverity(); ?>
                <div class="advik-insight-item advik-insight-<?php echo esc_attr( $sev ); ?>">
                    <span class="advik-insight-icon">
                        <?php if ( 'error' === $sev ) : ?>&#x2716;<?php elseif ( 'warning' === $sev ) : ?>&#x26A0;<?php else : ?>&#x2139;<?php endif; ?>
                    </span>
                    <div class="advik-insight-body">
                        <span class="advik-insight-title"><?php echo esc_html( $audit->getTitle() ); ?></span>
                        <span class="advik-insight-desc"><?php echo esc_html( $audit->getDescription() ); ?></span>
                    </div>
                    <div class="advik-insight-meta">
                        <?php if ( $audit->getEstimatedSavingsFormatted() ) : ?>
                            <span class="advik-insight-savings"><?php echo esc_html( $audit->getEstimatedSavingsFormatted() ); ?></span>
                        <?php endif; ?>
                        <span class="advik-insight-category advik-category-<?php echo esc_attr( $audit->getCategory() ); ?>"><?php echo esc_html( ucfirst( $audit->getCategory() ) ); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <?php else : ?>
    <div class="advik-section advik-score-rings-row">
        <?php foreach ( [ 'Performance', 'SEO', 'Accessibility', 'Best Practices' ] as $label ) : ?>
            <div class="advik-score-ring advik-score-ring-empty">
                <div class="advik-score-ring-visual">
                    <svg width="96" height="96" viewBox="0 0 120 120">
                        <circle cx="60" cy="60" r="54" fill="none" stroke="#E3E8E6" stroke-width="8"/>
                    </svg>
                    <span class="advik-ring-value advik-ring-value-skeleton">&ndash;</span>
                </div>
                <span class="advik-ring-label"><?php echo esc_html( $label ); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <p class="advik-empty-hint">
    <?php if ( $hasApiKey ) : ?>
        <?php esc_html_e( 'API key is configured. Click "Run Scan Now" to fetch data from PageSpeed Insights.', 'advik-optimizer' ); ?>
        <?php esc_html_e( 'Note: your site must be publicly accessible for Google\'s servers to reach it.', 'advik-optimizer' ); ?>
    <?php else : ?>
        <?php esc_html_e( 'Scores appear here after a successful scan.', 'advik-optimizer' ); ?>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=advik-optimizer-settings&tab=vitals' ) ); ?>"><?php esc_html_e( 'Configure a PageSpeed Insights API key on the Core Web Vitals settings tab', 'advik-optimizer' ); ?></a>
        <?php esc_html_e( 'to enable automated lab scans, or wait for real-user (RUM) data.', 'advik-optimizer' ); ?>
    <?php endif; ?>
    </p>
    <?php endif; ?>

    <div class="advik-card">
        <h2><?php echo esc_html__( 'Optimizations Active', 'advik-optimizer' ); ?></h2>
        <div class="advik-stat-tiles">
            <div class="advik-stat-tile">
                <span class="advik-stat-value"><?php echo esc_html( $cacheStats['hit_rate'] ?? 0 ); ?>%</span>
                <span class="advik-stat-label"><?php echo esc_html__( 'Cache Hit Rate', 'advik-optimizer' ); ?></span>
            </div>
            <div class="advik-stat-tile<?php echo ( $imageStats['count'] ?? 0 ) > 0 ? '' : ' advik-stat-tile-empty'; ?>">
                <span class="advik-stat-value"><?php echo ( $imageStats['count'] ?? 0 ) > 0 ? esc_html( $imageStats['savings'] ) : '&mdash;'; ?></span>
                <span class="advik-stat-label"><?php echo esc_html__( 'Images Saved', 'advik-optimizer' ); ?></span>
            </div>
            <div class="advik-stat-tile<?php echo ( $minifyStats['count'] ?? 0 ) > 0 ? '' : ' advik-stat-tile-empty'; ?>">
                <span class="advik-stat-value"><?php echo ( $minifyStats['count'] ?? 0 ) > 0 ? esc_html( $minifyStats['savings'] ) : '&mdash;'; ?></span>
                <span class="advik-stat-label"><?php echo esc_html__( 'JS/CSS Reduced', 'advik-optimizer' ); ?></span>
            </div>
        </div>
    </div>

    <div class="advik-workload-strip">
        <h3><?php echo esc_html__( 'Built for Every Workload', 'advik-optimizer' ); ?></h3>
        <span class="advik-workload-chip"><?php echo esc_html__( 'WooCommerce Stores', 'advik-optimizer' ); ?></span>
        <span class="advik-workload-chip"><?php echo esc_html__( 'News &amp; Publishing', 'advik-optimizer' ); ?></span>
        <span class="advik-workload-chip"><?php echo esc_html__( 'Agency Clients', 'advik-optimizer' ); ?></span>
        <span class="advik-workload-chip"><?php echo esc_html__( 'Portfolio &amp; SaaS', 'advik-optimizer' ); ?></span>
    </div>
</div>
