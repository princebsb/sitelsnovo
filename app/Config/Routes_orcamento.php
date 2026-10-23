<?php

/**
 * Rotas do Sistema de Orçamento
 *
 * Adicione estas rotas ao arquivo app/Config/Routes.php
 * dentro do grupo de rotas principal.
 *
 * Exemplo:
 * $routes->group('', function($routes) {
 *     // ... suas outras rotas ...
 *
 *     // Incluir rotas de orçamento
 *     include APPPATH . 'Config/Routes_orcamento.php';
 * });
 */

// Simulador de Orçamento
$routes->get('orcamento', 'Orcamento::index');
$routes->get('simular-orcamento', 'Orcamento::index'); // Alias amigável
$routes->post('orcamento/processar', 'Orcamento::processar');
$routes->get('orcamento/resultado/(:num)', 'Orcamento::resultado/$1');
$routes->post('orcamento/solicitar-proposta', 'Orcamento::solicitarProposta');
$routes->get('orcamento/status/(:num)', 'Orcamento::status/$1');

// API para consultas (opcional)
$routes->group('api/orcamento', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('(:num)', 'Orcamento::status/$1');
});
