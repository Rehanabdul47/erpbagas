@extends('layouts.app')

@section('title', 'Dashboard')
@section('breadcrumb', 'Dashboard')

@push('scripts')
<script src="{{ asset('js/dashboard.js') }}"></script>
@endpush

@push('init')
    renderDashboard(document.getElementById("page-content"));
@endpush
