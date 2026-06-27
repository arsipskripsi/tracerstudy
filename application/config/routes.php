<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Route Configuration for HMVC (Modular Extensions)
|--------------------------------------------------------------------------
|
| This file lets you remap URI requests to specific controllers and methods.
| HMVC routes are organized by module.
|
*/

// Default route
$route['default_controller'] = 'auth';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

/*
|--------------------------------------------------------------------------
| Module Routes - Auth
|--------------------------------------------------------------------------
*/
$route['auth'] = 'auth/auth';
$route['auth/login'] = 'auth/auth/login';
$route['auth/logout'] = 'auth/auth/logout';
$route['auth/register'] = 'auth/auth/register';
$route['auth/forgot-password'] = 'auth/auth/forgot_password';
$route['auth/reset-password'] = 'auth/auth/reset_password';
$route['auth/profile'] = 'auth/auth/profile';
$route['auth/change-password'] = 'auth/auth/change_password';

/*
|--------------------------------------------------------------------------
| Module Routes - Alumni
|--------------------------------------------------------------------------
*/
$route['alumni'] = 'alumni/alumni';
$route['alumni/dashboard'] = 'alumni/alumni/dashboard';
$route['alumni/profile'] = 'alumni/alumni/profile';
$route['alumni/pendidikan'] = 'alumni/alumni/pendidikan';
$route['alumni/pekerjaan'] = 'alumni/alumni/pekerjaan';
$route['alumni/prestasi'] = 'alumni/alumni/prestasi';
$route['alumni/sertifikasi'] = 'alumni/alumni/sertifikasi';

/*
|--------------------------------------------------------------------------
| Module Routes - Survey
|--------------------------------------------------------------------------
*/
$route['survey'] = 'survey/survey';
$route['survey/list'] = 'survey/survey/index';
$route['survey/fill/(:num)'] = 'survey/survey/fill/$1';
$route['survey/submit/(:num)'] = 'survey/survey/submit/$1';
$route['survey/results/(:num)'] = 'survey/survey/results/$1';

/*
|--------------------------------------------------------------------------
| Module Routes - IKU (Indikator Kinerja Utama)
|--------------------------------------------------------------------------
*/
$route['iku'] = 'iku/iku';
$route['iku/dashboard'] = 'iku/iku/dashboard';
$route['iku/indicator/(:num)'] = 'iku/iku/indicator/$1';
$route['iku/report'] = 'iku/iku/report';
$route['iku/export'] = 'iku/iku/export';

/*
|--------------------------------------------------------------------------
| Module Routes - Kurikulum
|--------------------------------------------------------------------------
*/
$route['kurikulum'] = 'kurikulum/kurikulum';
$route['kurikulum/mata-kuliah'] = 'kurikulum/kurikulum/mata_kuliah';
$route['kurikulum/cpmk'] = 'kurikulum/kurikulum/cpmk';
$route['kurikulum/evaluasi'] = 'kurikulum/kurikulum/evaluasi';
$route['kurikulum/review'] = 'kurikulum/kurikulum/review';

/*
|--------------------------------------------------------------------------
| Module Routes - Stakeholder
|--------------------------------------------------------------------------
*/
$route['stakeholder'] = 'stakeholder/stakeholder';
$route['stakeholder/perusahaan'] = 'stakeholder/stakeholder/perusahaan';
$route['stakeholder/user'] = 'stakeholder/stakeholder/user';
$route['stakeholder/feedback'] = 'stakeholder/stakeholder/feedback';
$route['stakeholder/survey'] = 'stakeholder/stakeholder/survey';

/*
|--------------------------------------------------------------------------
| Module Routes - Laporan
|--------------------------------------------------------------------------
*/
$route['laporan'] = 'laporan/laporan';
$route['laporan/alumni'] = 'laporan/laporan/alumni';
$route['laporan/iku'] = 'laporan/laporan/iku';
$route['laporan/survey'] = 'laporan/laporan/survey';
$route['laporan/stakeholder'] = 'laporan/laporan/stakeholder';
$route['laporan/export/(:any)'] = 'laporan/laporan/export/$1';
$route['laporan/download/(:any)'] = 'laporan/laporan/download/$1';

/*
|--------------------------------------------------------------------------
| API Routes (Optional)
|--------------------------------------------------------------------------
*/
$route['api/(:any)'] = '$1/api';

/*
|--------------------------------------------------------------------------
| Admin Routes (Optional - for future admin panel)
|--------------------------------------------------------------------------
*/
$route['admin'] = 'auth/admin/dashboard';
$route['admin/(:any)'] = 'auth/admin/$1';
