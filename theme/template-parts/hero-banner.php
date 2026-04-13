<?php
/**
 * Template part: Hero banner para homepage.
 *
 * @package Ropa_Deportiva
 * @author  Hector Riquelme
 */

defined( 'ABSPATH' ) || exit;

$titulo    = get_theme_mod( 'rd_hero_titulo', __( 'Ropa Deportiva de Alto Rendimiento', 'ropa-deportiva' ) );
$subtitulo = get_theme_mod( 'rd_hero_subtitulo', __( 'Equipamiento profesional para atletas chilenos. Envío a todo Chile con Chilexpress.', 'ropa-deportiva' ) );
$cta_texto = get_theme_mod( 'rd_hero_cta_texto', __( 'Ver Catálogo', 'ropa-deportiva' ) );
$cta_url   = get_theme_mod( 'rd_hero_cta_url', wc_get_page_permalink( 'shop' ) );
?>

<section class="rd-hero" role="banner">
    <div class="rd-hero-content">
        <div class="rd-hero-logo" aria-hidden="true">RD</div>
        <h1><?php echo esc_html( $titulo ); ?></h1>
        <p><?php echo esc_html( $subtitulo ); ?></p>
        <a href="<?php echo esc_url( $cta_url ); ?>" class="rd-cta">
            <?php echo esc_html( $cta_texto ); ?>
        </a>
    </div>
</section>
