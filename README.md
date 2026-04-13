# Ropa Deportiva Chile - Child Theme WooCommerce

**Autor:** Hector Riquelme  
**Versión:** 1.0.0  
**Theme padre:** Storefront

Child theme de WooCommerce para tienda de ropa deportiva chilena con integración de pagos WebPay, envíos Chilexpress y validación de RUT.

---

## Instalación

### Requisitos

- WordPress 6.0+
- WooCommerce 8.0+
- PHP 7.4+
- Theme Storefront instalado

### 1. Entorno local

Usar **Local by Flywheel** o **Docker con WordPress**:

```bash
# Docker (alternativa)
docker run -d --name wp-ropa-deportiva \
  -p 8080:80 \
  -e WORDPRESS_DB_HOST=db \
  -e WORDPRESS_DB_NAME=wordpress \
  wordpress:latest
```

### 2. Instalar Storefront

Desde el admin: **Apariencia > Temas > Añadir nuevo** > buscar "Storefront" > Instalar.

### 3. Instalar child theme

Copiar la carpeta `theme/` dentro de `wp-content/themes/` renombrándola a `ropa-deportiva`:

```bash
cp -r theme/ /ruta/wp-content/themes/ropa-deportiva/
```

Activar en **Apariencia > Temas**.

### 4. Plugin Transbank WebPay

1. Instalar plugin oficial gratuito: **Plugins > Añadir nuevo** > buscar "Transbank Webpay Plus REST".
2. Activar el plugin.
3. Ir a **WooCommerce > Ajustes > Pagos > Webpay Plus**.
4. Configurar con credenciales de integración (sandbox):

| Campo | Valor sandbox |
|-------|--------------|
| Código de comercio | `597055555532` |
| API Key | `579B532A7440BB0C9079DED94D31EA1615BACEB56610332264630D42D0A36B1C` |
| Ambiente | Integración / Test |

5. Probar con tarjeta de prueba:
   - Número: `4051 8856 0044 6623`
   - CVV: `123`
   - Fecha: cualquier fecha futura

### 5. Configurar Chilexpress

1. Obtener API Key en [desarrolladores.chilexpress.cl](https://desarrolladores.chilexpress.cl).
2. Ir a **WooCommerce > Ajustes > Envío > Zona de envío** > agregar método "Chilexpress".
3. Ingresar la API Key y el código de comuna de origen.

### 6. Personalización

Acceder a **Apariencia > Personalizar > Ropa Deportiva** para configurar:

- Monto mínimo para despacho gratis
- Texto y colores del hero banner
- Colores primario y secundario del tema

---

## Integración REST API

Para consultar pedidos desde un panel externo, usar la WooCommerce REST API:

### Generar credenciales

**WooCommerce > Ajustes > Avanzado > REST API** > Añadir clave.

### Consultar pedidos

```bash
# Listar pedidos
curl https://tu-sitio.cl/wp-json/wc/v3/orders \
  -u consumer_key:consumer_secret

# Pedido específico (incluye RUT del cliente en billing.rut)
curl https://tu-sitio.cl/wp-json/wc/v3/orders/123 \
  -u consumer_key:consumer_secret

# Filtrar por estado
curl "https://tu-sitio.cl/wp-json/wc/v3/orders?status=processing" \
  -u consumer_key:consumer_secret

# Filtrar por fecha
curl "https://tu-sitio.cl/wp-json/wc/v3/orders?after=2025-01-01T00:00:00&before=2025-12-31T23:59:59" \
  -u consumer_key:consumer_secret
```

### Respuesta incluye campo RUT

El child theme extiende la respuesta REST para incluir `billing.rut` en cada pedido, permitiendo integración con sistemas de facturación electrónica chilena (SII).

---

## Estructura del tema

```
ropa-deportiva/
├── style.css                          # Estilos del child theme
├── functions.php                      # Funciones principales, hooks y widgets
├── screenshot.png                     # Captura de pantalla
├── assets/
│   ├── css/custom.css                 # Estilos adicionales (hero, responsive)
│   └── js/
│       ├── rut-validator.js           # Algoritmo módulo 11 (cliente)
│       └── checkout.js                # Validación en tiempo real en checkout
├── inc/
│   ├── class-chilexpress-shipping.php # WC_Shipping_Method para Chilexpress
│   ├── rut-validation.php             # Validación RUT servidor + campo checkout
│   └── customizer.php                 # Opciones del Customizer
├── woocommerce/
│   ├── single-product.php             # Template producto individual
│   └── archive-product.php            # Template catálogo de productos
└── template-parts/
    └── hero-banner.php                # Hero section para homepage
```

## Funcionalidades

- **WebPay**: Pago con tarjetas vía Transbank (plugin oficial).
- **Chilexpress**: Cotización de envío en tiempo real vía API.
- **RUT**: Validación módulo 11 en cliente y servidor.
- **Despacho gratis**: Banner configurable por monto mínimo.
- **Customizer**: Colores, textos del hero y monto de despacho gratis.
- **REST API**: RUT expuesto en endpoint de pedidos para facturación.
