<?php
/**
 * Opciones del Customizer para Ropa Deportiva.
 *
 * @package Ropa_Deportiva
 * @author  Hector Riquelme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registrar secciones y controles del Customizer.
 */
function rd_customizer_register( $wp_customize ) {

    /* --- Sección principal --- */
    $wp_customize->add_section( 'rd_opciones', array(
        'title'    => __( 'Ropa Deportiva', 'ropa-deportiva' ),
        'priority' => 30,
    ) );

    /* Monto despacho gratis */
    $wp_customize->add_setting( 'rd_monto_despacho_gratis', array(
        'default'           => 50000,
        'sanitize_callback' => 'absint',
        'transport'         => 'refresh',
    ) );

    $wp_customize->add_control( 'rd_monto_despacho_gratis', array(
        'label'       => __( 'Monto mínimo para despacho gratis (CLP)', 'ropa-deportiva' ),
        'section'     => 'rd_opciones',
        'type'        => 'number',
        'input_attrs' => array(
            'min'  => 0,
            'step' => 1000,
        ),
    ) );

    /* Texto hero */
    $wp_customize->add_setting( 'rd_hero_titulo', array(
        'default'           => __( 'Ropa Deportiva de Alto Rendimiento', 'ropa-deportiva' ),
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ) );

    $wp_customize->add_control( 'rd_hero_titulo', array(
        'label'   => __( 'Título del hero banner', 'ropa-deportiva' ),
        'section' => 'rd_opciones',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'rd_hero_subtitulo', array(
        'default'           => __( 'Equipamiento profesional para atletas chilenos. Envío a todo Chile con Chilexpress.', 'ropa-deportiva' ),
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ) );

    $wp_customize->add_control( 'rd_hero_subtitulo', array(
        'label'   => __( 'Subtítulo del hero banner', 'ropa-deportiva' ),
        'section' => 'rd_opciones',
        'type'    => 'textarea',
    ) );

    $wp_customize->add_setting( 'rd_hero_cta_texto', array(
        'default'           => __( 'Ver Catálogo', 'ropa-deportiva' ),
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ) );

    $wp_customize->add_control( 'rd_hero_cta_texto', array(
        'label'   => __( 'Texto del botón CTA', 'ropa-deportiva' ),
        'section' => 'rd_opciones',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'rd_hero_cta_url', array(
        'default'           => '/tienda',
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'refresh',
    ) );

    $wp_customize->add_control( 'rd_hero_cta_url', array(
        'label'   => __( 'URL del botón CTA', 'ropa-deportiva' ),
        'section' => 'rd_opciones',
        'type'    => 'url',
    ) );

    /* Colores */
    $wp_customize->add_setting( 'rd_color_primario', array(
        'default'           => '#1B5E20',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'refresh',
    ) );

    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'rd_color_primario', array(
        'label'   => __( 'Color primario', 'ropa-deportiva' ),
        'section' => 'rd_opciones',
    ) ) );

    $wp_customize->add_setting( 'rd_color_secundario', array(
        'default'           => '#FF6F00',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'refresh',
    ) );

    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'rd_color_secundario', array(
        'label'   => __( 'Color secundario', 'ropa-deportiva' ),
        'section' => 'rd_opciones',
    ) ) );
}
add_action( 'customize_register', 'rd_customizer_register' );

/**
 * Inyectar CSS dinámico desde opciones del Customizer.
 */
function rd_customizer_css() {
    $primario   = get_theme_mod( 'rd_color_primario', '#1B5E20' );
    $secundario = get_theme_mod( 'rd_color_secundario', '#FF6F00' );
    ?>
    <style>
        :root {
            --rd-primary: <?php echo esc_attr( $primario ); ?>;
            --rd-secondary: <?php echo esc_attr( $secundario ); ?>;
        }
    </style>
    <?php
}
add_action( 'wp_head', 'rd_customizer_css' );

/**
 * Guardar monto de despacho gratis en option para uso global.
 */
function rd_guardar_monto_despacho() {
    $monto = get_theme_mod( 'rd_monto_despacho_gratis', 50000 );
    update_option( 'rd_monto_despacho_gratis', absint( $monto ) );
}
add_action( 'customize_save_after', 'rd_guardar_monto_despacho' );
