<?php
/**
 * Template override: Archivo de productos (catálogo / tienda).
 * Basado en WooCommerce archive-product.php con diseño deportivo.
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

        <?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
            <header class="rd-archive-header">
                <h1 class="woocommerce-products-header__title page-title">
                    <?php woocommerce_page_title(); ?>
                </h1>
            </header>
        <?php endif; ?>

        <?php
        /**
         * Hook: woocommerce_archive_description.
         */
        do_action( 'woocommerce_archive_description' );
        ?>

        <?php if ( woocommerce_product_loop() ) : ?>

            <?php
            /**
             * Hook: woocommerce_before_shop_loop.
             * - Conteo de resultados
             * - Ordenamiento
             */
            do_action( 'woocommerce_before_shop_loop' );
            ?>

            <?php woocommerce_product_loop_start(); ?>

            <?php if ( wc_get_loop_prop( 'total' ) ) : ?>
                <?php while ( have_posts() ) : the_post(); ?>
                    <?php
                    /**
                     * Hook: woocommerce_shop_loop.
                     */
                    do_action( 'woocommerce_shop_loop' );

                    wc_get_template_part( 'content', 'product' );
                    ?>
                <?php endwhile; ?>
            <?php endif; ?>

            <?php woocommerce_product_loop_end(); ?>

            <?php
            /**
             * Hook: woocommerce_after_shop_loop.
             * - Paginación
             */
            do_action( 'woocommerce_after_shop_loop' );
            ?>

        <?php else : ?>

            <div class="rd-no-productos" style="text-align:center;padding:60px 20px;">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="#BDBDBD" style="margin-bottom:20px;">
                    <path d="M11 9h2V6h3V4h-3V1h-2v3H8v2h3v3zm-4 9c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zm10 0c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2zm-9.83-3.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.86-7.01L19.42 4h-.01l-1.1 2-2.76 5H8.53l-.13-.27L6.16 6l-.95-2-.94-2H1v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25z"/>
                </svg>
                <h2><?php esc_html_e( 'No se encontraron productos', 'ropa-deportiva' ); ?></h2>
                <p><?php esc_html_e( 'Pronto agregaremos nuevos productos a nuestro catálogo deportivo.', 'ropa-deportiva' ); ?></p>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="button">
                    <?php esc_html_e( 'Volver al inicio', 'ropa-deportiva' ); ?>
                </a>
            </div>

            <?php
            /**
             * Hook: woocommerce_no_products_found.
             */
            do_action( 'woocommerce_no_products_found' );
            ?>

        <?php endif; ?>

    </main>
</div>

<?php
get_footer( 'shop' );
