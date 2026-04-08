<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * PWA: manifiesto y service worker (HTTPS recomendado en producción).
 */
class Pwa extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
    }

    public function manifest()
    {
        $base = rtrim(base_url(), '/');
        $name = 'POS';
        $short = 'POS';
        if (class_exists('Setting', false)) {
            try {
                $s = Setting::find(1);
                if ($s && ! empty($s->companyname)) {
                    $name = $s->companyname;
                    $short = function_exists('mb_substr')
                        ? mb_substr($name, 0, 24)
                        : substr($name, 0, 24);
                }
            } catch (Exception $e) {
            }
        }

        header('Content-Type: application/manifest+json; charset=utf-8');
        header('Cache-Control: public, max-age=3600');

        $icon = $base . '/assets/img/logo.png';

        $manifest = array(
            'id' => $base . '/',
            'name' => $name,
            'short_name' => $short,
            'description' => 'Point of sale',
            'start_url' => $base . '/',
            'scope' => $base . '/',
            'display' => 'standalone',
            'display_override' => array('standalone', 'browser'),
            'background_color' => '#2b2b2b',
            'theme_color' => '#222222',
            'orientation' => 'any',
            'icons' => array(
                array(
                    'src' => $icon,
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ),
                array(
                    'src' => $icon,
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ),
            ),
        );

        echo json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function sw()
    {
        $path = parse_url(base_url(), PHP_URL_PATH);
        $allowed = ($path !== null && $path !== '' && $path !== '/') ? rtrim($path, '/') . '/' : '/';

        header('Content-Type: application/javascript; charset=utf-8');
        header('Service-Worker-Allowed: ' . $allowed);
        header('Cache-Control: public, max-age=86400');

        echo <<<'JS'
/* Platea POS — service worker (PWA) */
self.addEventListener('install', function (event) {
	self.skipWaiting();
});
self.addEventListener('activate', function (event) {
	event.waitUntil(self.clients.claim());
});
self.addEventListener('fetch', function (event) {
	event.respondWith(fetch(event.request));
});
JS;
    }
}
