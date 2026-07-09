<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?>Sistem Tracer Study</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Font Awesome (fallback) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --sidebar-width: 250px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary-color), var(--secondary-color));
            color: white;
            overflow-y: auto;
            z-index: 1000;
        }

        .sidebar-brand {
            padding: 1.5rem;
            font-size: 1.2rem;
            font-weight: bold;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li a {
            display: block;
            padding: 1rem 1.5rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
        }

        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left: 4px solid white;
        }

        .sidebar-menu li a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
        }

        /* Card Styles */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background: white;
            border-bottom: 2px solid #f0f0f0;
            font-weight: bold;
            padding: 1rem 1.5rem;
        }

        /* Table Styles */
        .table thead th {
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .table-hover tbody tr:hover {
            background: #f8f9fa;
        }

        /* Button Styles */
        .btn {
            border-radius: 5px;
            font-weight: 500;
        }

        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-secondary {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        /* Badge Styles */
        .badge {
            padding: 0.5em 0.75em;
            font-weight: 500;
            border-radius: 5px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>

    <?= $this->render_section('css') ?>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="bi bi-mortarboard-fill"></i> Tracer Study
        </div>
        <ul class="sidebar-menu">
            <li><a href="<?= site_url('admin/dashboard'); ?>"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="<?= site_url('kohort'); ?>" class="<?= ($this->uri->segment(2) == 'kohort') ? 'active' : ''; ?>"><i class="bi bi-people-fill"></i> Kelola Kohort</a></li>
            <li><a href="<?= site_url('iku'); ?>"><i class="bi bi-graph-up"></i> IKU & Verifikasi</a></li>
            <li><a href="<?= site_url('integrasi'); ?>"><i class="bi bi-cloud-upload"></i> Integrasi Nasional</a></li>
            <li><a href="<?= site_url('auth/logout'); ?>"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
