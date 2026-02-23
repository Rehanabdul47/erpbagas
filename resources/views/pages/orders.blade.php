@extends('layouts.app')

@section('title', 'Orders')
@section('breadcrumb', 'Orders')

@push('scripts')
<script src="{{ asset('js/sales.js') }}"></script>
@endpush

@push('init')
    renderOrders(document.getElementById("page-content"));
@endpush
