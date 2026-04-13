/**
 * Script de checkout - Validación de RUT en tiempo real.
 *
 * @package Ropa_Deportiva
 * @author  Hector Riquelme
 */
(function ($) {
    'use strict';

    if (typeof RutValidator === 'undefined') {
        return;
    }

    $(document).ready(function () {

        var $campo = $('#billing_rut');
        var $feedback = $campo.closest('.form-row').find('.rut-feedback');

        if (!$feedback.length) {
            $campo.after('<span class="rut-feedback"></span>');
            $feedback = $campo.siblings('.rut-feedback');
        }

        /* Formatear al perder foco */
        $campo.on('blur', function () {
            var valor = $(this).val().trim();
            if (valor.length > 0) {
                $(this).val(RutValidator.formatear(valor));
                validarCampo();
            }
        });

        /* Validar en tiempo real al escribir */
        $campo.on('input', function () {
            var valor = $(this).val().trim();
            if (valor.length >= 8) {
                validarCampo();
            } else {
                limpiarEstado();
            }
        });

        function validarCampo() {
            var valor = $campo.val().trim();

            if (valor.length === 0) {
                mostrarError(rdCheckout.msgs.rutRequerido);
                return false;
            }

            if (RutValidator.validar(valor)) {
                mostrarExito(rdCheckout.msgs.rutValido);
                return true;
            } else {
                mostrarError(rdCheckout.msgs.rutInvalido);
                return false;
            }
        }

        function mostrarError(msg) {
            $campo.removeClass('rut-valido').addClass('rut-invalido');
            $feedback.text(msg).css('color', '#c62828').show();
        }

        function mostrarExito(msg) {
            $campo.removeClass('rut-invalido').addClass('rut-valido');
            $feedback.text(msg).css('color', '#2E7D32').show();
        }

        function limpiarEstado() {
            $campo.removeClass('rut-valido rut-invalido');
            $feedback.text('').hide();
        }

        /* Interceptar envío del formulario de checkout */
        $('form.checkout').on('checkout_place_order', function () {
            if ($campo.length && !validarCampo()) {
                $('html, body').animate({
                    scrollTop: $campo.offset().top - 100
                }, 500);
                return false;
            }
            return true;
        });
    });

})(jQuery);
