/**
 * pwa.js - Registro del Service Worker para PWA
 * 
 * Este script se incluye en index.php y login.php.
 * Comprueba si el navegador soporta Service Workers y, si no hay
 * uno registrado, lo registra con el archivo pwa_sw.js.
 * 
 * Esto permite que la aplicación funcione offline mostrando
 * una página de "Sin conexión" (offline.html) cuando no hay red.
 */

if ("serviceWorker" in navigator) {
    console.log("Navegador admite Service Worker.");

    if (navigator.serviceWorker.controller) {
        // Ya hay un Service Worker activo, no hace falta registrar otro
        console.log("El Service Worker ya existe, no se necesita registrarlo de nuevo.");
    } else {
        // Registrar el Service Worker en segundo plano (otro hilo)
        console.log("Registrando Service Worker...");
        navigator.serviceWorker.register("pwa_sw.js", {
            scope: "./"   // Alcance: toda la aplicación desde la raíz
        }).then(function (reg) {
            console.log("Service Worker registrado para: " + reg.scope);
        }).catch(function (err) {
            console.log("NO se ha podido registrar el Service Worker: ", err);
        });
    }
}