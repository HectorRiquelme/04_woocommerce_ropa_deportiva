<?php
/**
 * Ropa Deportiva Chile - functions.php
 * Child theme de Storefront para tienda de ropa deportiva chilena.
 *
 * @package Ropa_Deportiva
 * @author  Hector Riquelme
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

define( 'RD_VERSION', '1.0.0' );
define( 'RD_DIR', get_stylesheet_directory() );
define( 'RD_URI', get_stylesheet_directory_uri() );

/**
 * Encolar estilos del theme padre y child.
 */
function rd_enqueue_styles() {
    wp_enqueue_style(
        'storefront-parent-style',
        get_template_directory_uri() . '/style.css',
        array(),
        RD_VERSION
    );

    wp_enqueue_style(
        'ropa-deportiva-style',
        get_stylesheet_uri(),
        array( 'storefront-parent-style' ),
        RD_VERSION
    );

    wp_enqueue_style(
        'ropa-deportiva-custom',
        RD_URI . '/assets/css/custom.css',
        array( 'ropa-deportiva-style' ),
        RD_VERSION
    );
}
add_action( 'wp_enqueue_scripts', 'rd_enqueue_styles' );

/**
 * Encolar scripts.
 */
function rd_enqueue_scripts() {
    if ( is_checkout() ) {
        wp_enqueue_script(
            'rd-rut-validator',
            RD_URI . '/assets/js/rut-validator.js',
            array(),
            RD_VERSION,
            true
        );

        wp_enqueue_script(
            'rd-checkout',
            RD_URI . '/assets/js/checkout.js',
            array( 'jquery', 'rd-rut-validator' ),
            RD_VERSION,
            true
        );

        wp_localize_script( 'rd-checkout', 'rdCheckout', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'rd_checkout_nonce' ),
            'msgs'    => array(
                'rutInvalido'  => __( 'El RUT ingresado no es válido.', 'ropa-deportiva' ),
                'rutValido'    => __( 'RUT válido.', 'ropa-deportiva' ),
                'rutRequerido' => __( 'El RUT es obligatorio.', 'ropa-deportiva' ),
            ),
        ) );
    }
}
add_action( 'wp_enqueue_scripts', 'rd_enqueue_scripts' );

/**
 * Cargar módulos del theme.
 */
require_once RD_DIR . '/inc/rut-validation.php';
require_once RD_DIR . '/inc/class-chilexpress-shipping.php';
require_once RD_DIR . '/inc/customizer.php';

/**
 * Registrar método de envío Chilexpress.
 */
function rd_registrar_chilexpress_shipping( $methods ) {
    $methods['chilexpress'] = 'RD_Chilexpress_Shipping';
    return $methods;
}
add_filter( 'woocommerce_shipping_methods', 'rd_registrar_chilexpress_shipping' );

/**
 * Banner de despacho gratis.
 */
function rd_banner_despacho_gratis() {
    $monto_minimo = get_option( 'rd_monto_despacho_gratis', 50000 );
    if ( $monto_minimo > 0 ) {
        printf(
            '<div class="banner-despacho-gratis">%s <span class="monto">$%s</span></div>',
            esc_html__( '¡Despacho GRATIS en compras sobre', 'ropa-deportiva' ),
            esc_html( number_format( $monto_minimo, 0, ',', '.' ) )
        );
    }
}
add_action( 'storefront_before_header', 'rd_banner_despacho_gratis' );

/**
 * Badge Chilexpress en producto individual.
 */
function rd_badge_chilexpress() {
    echo '<span class="badge-chilexpress">';
    echo '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18 18.5c.83 0 1.5-.67 1.5-1.5s-.67-1.5-1.5-1.5-1.5.67-1.5 1.5.67 1.5 1.5 1.5zm1.5-9H17V12h4.46L19.5 9.5zM6 18.5c.83 0 1.5-.67 1.5-1.5S6.83 15.5 6 15.5 4.5 16.17 4.5 17 5.17 18.5 6 18.5zM20 8l3 4v5h-2c0 1.66-1.34 3-3 3s-3-1.34-3-3H9c0 1.66-1.34 3-3 3s-3-1.34-3-3H1V6c0-1.11.89-2 2-2h14v4h3z"/></svg>';
    esc_html_e( 'Envío Chilexpress', 'ropa-deportiva' );
    echo '</span>';
}
add_action( 'woocommerce_single_product_summary', 'rd_badge_chilexpress', 25 );

/**
 * Hero banner en homepage.
 */
function rd_hero_homepage() {
    if ( is_front_page() || is_shop() ) {
        get_template_part( 'template-parts/hero-banner' );
    }
}
add_action( 'storefront_before_content', 'rd_hero_homepage', 5 );

/**
 * Declarar compatibilidad con WooCommerce.
 */
function rd_woocommerce_support() {
    add_theme_support( 'woocommerce' );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'rd_woocommerce_support' );

/**
 * Registrar widget de despacho gratis.
 */
function rd_registrar_widgets() {
    register_widget( 'RD_Widget_Despacho_Gratis' );
}
add_action( 'widgets_init', 'rd_registrar_widgets' );

/**
 * Widget de despacho gratis.
 */
class RD_Widget_Despacho_Gratis extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'rd_despacho_gratis',
            __( 'Despacho Gratis', 'ropa-deportiva' ),
            array( 'description' => __( 'Muestra banner de despacho gratis sobre cierto monto.', 'ropa-deportiva' ) )
        );
    }

    public function widget( $args, $instance ) {
        $monto = ! empty( $instance['monto'] ) ? absint( $instance['monto'] ) : 50000;

        echo $args['before_widget'];
        echo '<div class="banner-despacho-gratis">';
        printf(
            '%s <span class="monto">$%s</span>',
            esc_html__( '¡Despacho GRATIS sobre', 'ropa-deportiva' ),
            esc_html( number_format( $monto, 0, ',', '.' ) )
        );
        echo '</div>';
        echo $args['after_widget'];
    }

    public function form( $instance ) {
        $monto = ! empty( $instance['monto'] ) ? absint( $instance['monto'] ) : 50000;
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'monto' ) ); ?>">
                <?php esc_html_e( 'Monto mínimo (CLP):', 'ropa-deportiva' ); ?>
            </label>
            <input
                class="widefat"
                id="<?php echo esc_attr( $this->get_field_id( 'monto' ) ); ?>"
                name="<?php echo esc_attr( $this->get_field_name( 'monto' ) ); ?>"
                type="number"
                value="<?php echo esc_attr( $monto ); ?>"
            />
        </p>
        <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance          = array();
        $instance['monto'] = absint( $new_instance['monto'] );
        return $instance;
    }
}
