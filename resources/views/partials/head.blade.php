<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'GUPALA ERP') — GUPALA ERP</title>
<meta name="description" content="GUPALA ERP - Sistem manajemen perusahaan terintegrasi.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest"></script>
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
<link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('css/inventory.css') }}">
<link rel="stylesheet" href="{{ asset('css/finance.css') }}">
<link rel="stylesheet" href="{{ asset('css/sales.css') }}">
<link rel="stylesheet" href="{{ asset('css/hrm.css') }}">
<link rel="stylesheet" href="{{ asset('css/procurement.css') }}">
<link rel="stylesheet" href="{{ asset('css/settings.css') }}">
@stack('styles')
