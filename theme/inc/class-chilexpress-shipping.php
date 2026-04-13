<?php
/**
 * Método de envío Chilexpress para WooCommerce.
 * Consulta la API de cotización de Chilexpress en tiempo real.
 *
 * @package Ropa_Deportiva
 * @author  Hector Riquelme
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RD_Chilexpress_Shipping' ) ) {

    class RD_Chilexpress_Shipping extends WC_Shipping_Method {

        /** @var string URL base de la API de cotización Chilexpress */
        private $api_url = 'https://testservices.wschilexpress.com/rating/api/v1.0/rates/courier';

        /** @var string API key de Chilexpress */
        private $api_key = '';

        /** @var string Código de cobertura de origen (Santiago por defecto) */
        private $origen_codigo = 'STGO';

        public function __construct( $instance_id = 0 ) {
            $this->id                 = 'chilexpress';
            $this->instance_id        = absint( $instance_id );
            $this->method_title       = __( 'Chilexpress', 'ropa-deportiva' );
            $this->method_description = __( 'Envío vía Chilexpress con cotización en tiempo real.', 'ropa-deportiva' );
            $this->supports           = array(
                'shipping-zones',
                'instance-settings',
                'instance-settings-modal',
            );

            $this->init();
        }

        /**
         * Inicializar configuración.
         */
        private function init() {
            $this->init_form_fields();
            $this->init_settings();

            $this->title         = $this->get_option( 'title', __( 'Chilexpress', 'ropa-deportiva' ) );
            $this->api_key       = $this->get_option( 'api_key', '' );
            $this->origen_codigo = $this->get_option( 'origen_codigo', 'STGO' );
            $this->enabled       = $this->get_option( 'enabled', 'yes' );

            add_action(
                'woocommerce_update_options_shipping_' . $this->id,
                array( $this, 'process_admin_options' )
            );
        }

        /**
         * Campos de configuración del método.
         */
        public function init_form_fields() {
            $this->instance_form_fields = array(
                'title'         => array(
                    'title'   => __( 'Título', 'ropa-deportiva' ),
                    'type'    => 'text',
                    'default' => __( 'Chilexpress', 'ropa-deportiva' ),
                ),
                'api_key'       => array(
                    'title'       => __( 'API Key Chilexpress', 'ropa-deportiva' ),
                    'type'        => 'password',
                    'description' => __( 'Clave de API obtenida en el portal de desarrolladores de Chilexpress.', 'ropa-deportiva' ),
                    'default'     => '',
                ),
                'origen_codigo' => array(
                    'title'       => __( 'Código de origen', 'ropa-deportiva' ),
                    'type'        => 'text',
                    'description' => __( 'Código de cobertura Chilexpress de la comuna de origen (ej: STGO).', 'ropa-deportiva' ),
                    'default'     => 'STGO',
                ),
            );
        }

        /**
         * Calcular costo de envío consultando la API de Chilexpress.
         *
         * @param array $package Paquete WooCommerce con destino y productos.
         */
        public function calculate_shipping( $package = array() ) {
            if ( empty( $this->api_key ) ) {
                return;
            }

            $destino = $this->obtener_codigo_destino( $package );
            if ( empty( $destino ) ) {
                return;
            }

            $peso_total   = 0;
            $alto_total   = 0;
            $ancho_max    = 0;
            $largo_max    = 0;

            foreach ( $package['contents'] as $item ) {
                $producto = $item['data'];
                $qty      = $item['quantity'];

                $peso = (float) $producto->get_weight();
                $alto = (float) $producto->get_height();
                $ancho = (float) $producto->get_width();
                $largo = (float) $producto->get_length();

                $peso_total += ( $peso > 0 ? $peso : 0.5 ) * $qty;
                $alto_total += ( $alto > 0 ? $alto : 10 ) * $qty;
                $ancho_max  = max( $ancho_max, $ancho > 0 ? $ancho : 20 );
                $largo_max  = max( $largo_max, $largo > 0 ? $largo : 30 );
            }

            $body = array(
                'originCountyCode'      => sanitize_text_field( $this->origen_codigo ),
                'destinationCountyCode' => sanitize_text_field( $destino ),
                'package'               => array(
                    'weight' => round( $peso_total, 2 ),
                    'height' => round( $alto_total, 2 ),
                    'width'  => round( $ancho_max, 2 ),
                    'length' => round( $largo_max, 2 ),
                ),
                'productType'           => 3, // Encomienda
                'contentType'           => 1, // Mercadería
                'declaredWorth'         => round( $package['contents_cost'], 0 ),
                'deliveryTime'          => 0, // Todos los servicios
            );

            $response = wp_remote_post( $this->api_url, array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Cache-Control' => 'no-cache',
                    'Ocp-Apim-Subscription-Key' => $this->api_key,
                ),
                'body'    => wp_json_encode( $body ),
                'timeout' => 15,
            ) );

            if ( is_wp_error( $response ) ) {
                $this->agregar_tarifa_fallback( $package );
                return;
            }

            $status_code = wp_remote_retrieve_response_code( $response );
            $body_resp   = json_decode( wp_remote_retrieve_body( $response ), true );

            if ( 200 !== $status_code || empty( $body_resp['data']['courierServiceOptions'] ) ) {
                $this->agregar_tarifa_fallback( $package );
                return;
            }

            foreach ( $body_resp['data']['courierServiceOptions'] as $opcion ) {
                $servicio = sanitize_text_field( $opcion['serviceDescription'] ?? 'Chilexpress' );
                $costo    = floatval( $opcion['serviceValue'] ?? 0 );
                $dias     = intval( $opcion['deliveryTime'] ?? 0 );

                $label = sprintf(
                    '%s (%s)',
                    $servicio,
                    $dias > 0
                        ? sprintf( _n( '%d día hábil', '%d días hábiles', $dias, 'ropa-deportiva' ), $dias )
                        : __( 'consultar plazo', 'ropa-deportiva' )
                );

                $this->add_rate( array(
                    'id'    => $this->get_rate_id( sanitize_title( $servicio ) ),
                    'label' => $label,
                    'cost'  => $costo,
                ) );
            }
        }

        /**
         * Obtener código de cobertura Chilexpress desde la comuna de destino.
         */
        private function obtener_codigo_destino( $package ) {
            $ciudad = '';

            if ( ! empty( $package['destination']['city'] ) ) {
                $ciudad = $package['destination']['city'];
            } elseif ( ! empty( $package['destination']['state'] ) ) {
                $ciudad = $package['destination']['state'];
            }

            if ( empty( $ciudad ) ) {
                return '';
            }

            $ciudad = strtoupper( sanitize_text_field( $ciudad ) );

            $comunas = $this->obtener_mapa_comunas();

            if ( isset( $comunas[ $ciudad ] ) ) {
                return $comunas[ $ciudad ];
            }

            // Buscar coincidencia parcial
            foreach ( $comunas as $nombre => $codigo ) {
                if ( strpos( $nombre, $ciudad ) !== false || strpos( $ciudad, $nombre ) !== false ) {
                    return $codigo;
                }
            }

            // Si no se encuentra, usar la ciudad directamente como código
            return $ciudad;
        }

        /**
         * Mapa de comunas principales a códigos de cobertura Chilexpress.
         */
        private function obtener_mapa_comunas() {
            return array(
                'SANTIAGO'       => 'STGO',
                'PROVIDENCIA'    => 'PROV',
                'LAS CONDES'     => 'LCON',
                'ÑUÑOA'          => 'NUNO',
                'VITACURA'       => 'VITA',
                'LA FLORIDA'     => 'LFLO',
                'MAIPÚ'          => 'MAIP',
                'MAIPU'          => 'MAIP',
                'PUENTE ALTO'    => 'PALT',
                'SAN BERNARDO'   => 'SBER',
                'VALPARAÍSO'     => 'VALP',
                'VALPARAISO'     => 'VALP',
                'VIÑA DEL MAR'   => 'VMAR',
                'VINA DEL MAR'   => 'VMAR',
                'CONCEPCIÓN'     => 'CONC',
                'CONCEPCION'     => 'CONC',
                'TEMUCO'         => 'TEMU',
                'ANTOFAGASTA'    => 'ANTO',
                'LA SERENA'      => 'LSER',
                'RANCAGUA'       => 'RANC',
                'TALCA'          => 'TALC',
                'ARICA'          => 'ARIC',
                'IQUIQUE'        => 'IQUI',
                'PUERTO MONTT'   => 'PMON',
                'OSORNO'         => 'OSOR',
                'VALDIVIA'       => 'VALD',
                'COYHAIQUE'      => 'COYH',
                'PUNTA ARENAS'   => 'PARE',
                'CALAMA'         => 'CALA',
                'COPIAPÓ'        => 'COPI',
                'COPIAPO'        => 'COPI',
                'CHILLÁN'        => 'CHIL',
                'CHILLAN'        => 'CHIL',
                'LOS ÁNGELES'    => 'LANG',
                'LOS ANGELES'    => 'LANG',
            );
        }

        /**
         * Tarifa de respaldo cuando la API no responde.
         */
        private function agregar_tarifa_fallback( $package ) {
            $costo_base = 4990;
            $total      = floatval( $package['contents_cost'] ?? 0 );
            $monto_free = absint( get_option( 'rd_monto_despacho_gratis', 50000 ) );

            if ( $monto_free > 0 && $total >= $monto_free ) {
                $costo_base = 0;
            }

            $this->add_rate( array(
                'id'    => $this->get_rate_id( 'standard' ),
                'label' => __( 'Chilexpress Estándar (tarifa estimada)', 'ropa-deportiva' ),
                'cost'  => $costo_base,
            ) );
        }
    }
}
