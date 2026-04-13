<?php
/**
 * Template override: Producto individual.
 * Basado en WooCommerce single-product.php con personalizaciones deportivas.
 *
 * @package Ropa_Deportiva
 * @author  Hector Riquelme
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">

        <?php while ( have_posts() ) : the_post(); ?>

            <?php wc_get_template_part( 'content', 'single-product' ); ?>

            <div class="rd-product-badges">
                <span class="rd-product-badge rd-product-badge--envio">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M18 18.5c.83 0 1.5-.67 1.5-1.5s-.67-1.5-1.5-1.5-1.5.67-1.5 1.5.67 1.5 1.5 1.5zm1.5-9H17V12h4.46L19.5 9.5zM6 18.5c.83 0 1.5-.67 1.5-1.5S6.83 15.5 6 15.5 4.5 16.17 4.5 17 5.17 18.5 6 18.5zM20 8l3 4v5h-2c0 1.66-1.34 3-3 3s-3-1.34-3-3H9c0 1.66-1.34 3-3 3s-3-1.34-3-3H1V6c0-1.11.89-2 2-2h14v4h3z"/>
                    </svg>
                    <?php esc_html_e( 'Envío Chilexpress a todo Chile', 'ropa-deportiva' ); ?>
                </span>
                <span class="rd-product-badge rd-product-badge--garantia">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/>
                    </svg>
                    <?php esc_html_e( 'Garantía de calidad', 'ropa-deportiva' ); ?>
                </span>
            </div>

            <?php
            /* Productos relacionados */
            $monto_free = absint( get_option( 'rd_monto_despacho_gratis', 50000 ) );
            if ( $monto_free > 0 ) :
            ?>
                <div class="rd-info-envio" style="background:#E8F5E9;padding:15px 20px;border-radius:8px;margin:20px 0;border-left:4px solid #2E7D32;">
                    <p style="margin:0;color:#1B5E20;font-weight:600;">
                        <?php
                        printf(
                            /* translators: %s: monto mínimo para despacho gratis */
                            esc_html__( 'Despacho gratis en compras sobre $%s a todo Chile.', 'ropa-deportiva' ),
                            esc_html( number_format( $monto_free, 0, ',', '.' ) )
                        );
                        ?>
                    </p>
                </div>
            <?php endif; ?>

        <?php endwhile; ?>

    </main>
</div>

<?php
get_footer( 'shop' );
