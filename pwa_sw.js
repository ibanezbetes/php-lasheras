/**
 * pwa_sw.js - Service Worker para la PWA
 * 
 * Este archivo se ejecuta en un hilo independiente del navegador.
 * Se encarga de:
 * 1. Crear una caché con los recursos esenciales de la aplicación
 * 2. Interceptar las peticiones de red y servirlas desde caché si están disponibles
 * 3. Mostrar la página offline.html cuando no hay conexión a internet
 * 
 * Ciclo de vida del Service Worker:
 *   install  → Se cachean los archivos esenciales
 *   fetch    → Se interceptan peticiones para servir desde caché o red
 */

// =====================================================================
// CONFIGURACIÓN DE LA CACHÉ
// =====================================================================

/** Nombre de la caché principal */
const NOMBRE_CACHE = "cache_principal";

/** 
 * Lista de URLs a cachear durante la instalación
 * Estos archivos estarán disponibles sin conexión a internet
 */
const urls = [
    'iconos/logo.png',
    'offline.html',
    'css/estilos.css',
    'login.php',
    'librerias/bootstrap-5.3.8-dist/css/bootstrap.min.css',
    'librerias/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js'
];

// =====================================================================
// EVENTO: INSTALL (se ejecuta al instalar el Service Worker)
// =====================================================================

self.addEventListener("install", function (event) {
    // Esperar a que se descarguen y almacenen todos los archivos en caché
    event.waitUntil(
        caches.open(NOMBRE_CACHE).then(function (cache) {
            console.log("Caché abierta, almacenando recursos...");
            return cache.addAll(urls);
        })
    );
});

// =====================================================================
// EVENTO: FETCH (intercepta todas las peticiones de red)
// =====================================================================

self.addEventListener("fetch", function (evento) {
    evento.respondWith(
        // Buscar si la petición está en caché
        caches.match(evento.request).then(function (response) {
            if (response) {
                // Si está en caché, devolver directamente (sin ir a la red)
                return response;
            }
            // Si no está en caché, hacer la petición a la red normalmente
            return fetch(evento.request);
        }).catch(function (err) {
            // Si no hay red ni caché, mostrar la página offline
            // Solo para peticiones de navegación (no para AJAX, imágenes, etc.)
            if (evento.request.mode === "navigate") {
                return caches.match("./offline.html");
            }
        })
    );
});
