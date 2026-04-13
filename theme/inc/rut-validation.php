<?php
/**
 * Validación de RUT chileno en checkout de WooCommerce.
 * Implementa algoritmo módulo 11 en servidor.
 *
 * @package Ropa_Deportiva
 * @author  Hector Riquelme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Agregar campo RUT al formulario de checkout.
 */
function rd_agregar_campo_rut( $fields ) {
    $fields['billing']['billing_rut'] = array(
        'type'        => 'text',
        'label'       => __( 'RUT', 'ropa-deportiva' ),
        'placeholder' => __( 'Ej: 12.345.678-9', 'ropa-deportiva' ),
        'required'    => true,
        'class'       => array( 'form-row-wide', 'rut-field-wrapper' ),
        'priority'    => 25,
        'custom_attributes' => array(
            'maxlength' => 12,
        ),
    );
    return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'rd_agregar_campo_rut' );

/**
 * Validar RUT en el servidor al procesar el checkout.
 */
function rd_validar_rut_checkout() {
    $rut = isset( $_POST['billing_rut'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_rut'] ) ) : '';

    if ( empty( $rut ) ) {
        wc_add_notice(
            __( 'El RUT es obligatorio.', 'ropa-deportiva' ),
            'error'
        );
        return;
    }

    if ( ! rd_validar_rut( $rut ) ) {
        wc_add_notice(
            __( 'El RUT ingresado no es válido. Verifique e intente nuevamente.', 'ropa-deportiva' ),
            'error'
        );
    }
}
add_action( 'woocommerce_checkout_process', 'rd_validar_rut_checkout' );

/**
 * Guardar RUT como meta del pedido.
 */
function rd_guardar_rut_pedido( $order_id ) {
    if ( ! empty( $_POST['billing_rut'] ) ) {
        $rut = sanitize_text_field( wp_unslash( $_POST['billing_rut'] ) );
        update_post_meta( $order_id, '_billing_rut', $rut );

        // Compatibilidad con HPOS (High-Performance Order Storage)
        $order = wc_get_order( $order_id );
        if ( $order ) {
            $order->update_meta_data( '_billing_rut', $rut );
            $order->save();
        }
    }
}
add_action( 'woocommerce_checkout_update_order_meta', 'rd_guardar_rut_pedido' );

/**
 * Mostrar RUT en el detalle del pedido en admin.
 */
function rd_mostrar_rut_admin( $order ) {
    $rut = $order->get_meta( '_billing_rut' );
    if ( $rut ) {
        printf(
            '<p><strong>%s:</strong> %s</p>',
            esc_html__( 'RUT', 'ropa-deportiva' ),
            esc_html( $rut )
        );
    }
}
add_action( 'woocommerce_admin_order_data_after_billing_address', 'rd_mostrar_rut_admin' );

/**
 * Exponer RUT en la WooCommerce REST API.
 */
function rd_rut_en_rest_api( $response, $order ) {
    $data = $response->get_data();
    $data['billing']['rut'] = $order->get_meta( '_billing_rut' );
    $response->set_data( $data );
    return $response;
}
add_filter( 'woocommerce_rest_prepare_shop_order_object', 'rd_rut_en_rest_api', 10, 2 );

/**
 * Validar RUT chileno con algoritmo módulo 11.
 *
 * @param string $rut RUT en cualquier formato (con o sin puntos/guión).
 * @return bool
 */
function rd_validar_rut( $rut ) {
    // Limpiar: eliminar puntos, guiones y espacios
    $rut = strtoupper( preg_replace( '/[\.\-\s]/', '', $rut ) );

    if ( strlen( $rut ) < 2 ) {
        return false;
    }

    $cuerpo = substr( $rut, 0, -1 );
    $dv     = substr( $rut, -1 );

    if ( ! ctype_digit( $cuerpo ) ) {
        return false;
    }

    $numero = intval( $cuerpo );
    if ( $numero < 1000000 || $numero > 99999999 ) {
        return false;
    }

    // Algoritmo módulo 11
    $suma     = 0;
    $multiplo = 2;

    for ( $i = strlen( $cuerpo ) - 1; $i >= 0; $i-- ) {
        $suma += intval( $cuerpo[ $i ] ) * $multiplo;
        $multiplo = $multiplo < 7 ? $multiplo + 1 : 2;
    }

    $resto       = $suma % 11;
    $dv_calculado = 11 - $resto;

    if ( 11 === $dv_calculado ) {
        $dv_esperado = '0';
    } elseif ( 10 === $dv_calculado ) {
        $dv_esperado = 'K';
    } else {
        $dv_esperado = strval( $dv_calculado );
    }

    return $dv === $dv_esperado;
}

/**
 * Validación AJAX del RUT.
 */
function rd_ajax_validar_rut() {
    check_ajax_referer( 'rd_checkout_nonce', 'nonce' );

    $rut = isset( $_POST['rut'] ) ? sanitize_text_field( wp_unslash( $_POST['rut'] ) ) : '';

    wp_send_json( array(
        'valido' => rd_validar_rut( $rut ),
    ) );
}
add_action( 'wp_ajax_rd_validar_rut', 'rd_ajax_validar_rut' );
add_action( 'wp_ajax_nopriv_rd_validar_rut', 'rd_ajax_validar_rut' );
