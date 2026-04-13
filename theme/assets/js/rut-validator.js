/**
 * Validador de RUT chileno - Algoritmo módulo 11.
 *
 * @package Ropa_Deportiva
 * @author  Hector Riquelme
 */
(function (root) {
    'use strict';

    var RutValidator = {

        /**
         * Limpiar RUT: eliminar puntos, guiones y espacios.
         */
        limpiar: function (rut) {
            if (typeof rut !== 'string') {
                return '';
            }
            return rut.replace(/[\.\-\s]/g, '').toUpperCase();
        },

        /**
         * Calcular dígito verificador usando módulo 11.
         */
        calcularDV: function (rutNumerico) {
            var cuerpo = String(rutNumerico);
            var suma = 0;
            var multiplo = 2;

            for (var i = cuerpo.length - 1; i >= 0; i--) {
                suma += parseInt(cuerpo.charAt(i), 10) * multiplo;
                multiplo = multiplo < 7 ? multiplo + 1 : 2;
            }

            var resto = suma % 11;
            var dv = 11 - resto;

            if (dv === 11) {
                return '0';
            }
            if (dv === 10) {
                return 'K';
            }
            return String(dv);
        },

        /**
         * Validar RUT completo.
         * Acepta formatos: 12345678-9, 12.345.678-9, 123456789
         */
        validar: function (rut) {
            var limpio = this.limpiar(rut);

            if (limpio.length < 2) {
                return false;
            }

            var cuerpo = limpio.slice(0, -1);
            var dvIngresado = limpio.slice(-1);

            if (!/^\d+$/.test(cuerpo)) {
                return false;
            }

            var numero = parseInt(cuerpo, 10);
            if (numero < 1000000 || numero > 99999999) {
                return false;
            }

            var dvCalculado = this.calcularDV(cuerpo);
            return dvIngresado === dvCalculado;
        },

        /**
         * Formatear RUT con puntos y guión: 12.345.678-9
         */
        formatear: function (rut) {
            var limpio = this.limpiar(rut);

            if (limpio.length < 2) {
                return rut;
            }

            var cuerpo = limpio.slice(0, -1);
            var dv = limpio.slice(-1);
            var formateado = '';

            while (cuerpo.length > 3) {
                formateado = '.' + cuerpo.slice(-3) + formateado;
                cuerpo = cuerpo.slice(0, -3);
            }

            formateado = cuerpo + formateado + '-' + dv;
            return formateado;
        }
    };

    /* Exportar */
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = RutValidator;
    } else {
        root.RutValidator = RutValidator;
    }

})(typeof window !== 'undefined' ? window : this);
