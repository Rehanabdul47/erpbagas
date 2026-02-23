@extends('layouts.app')

@section('title', 'Settings')
@section('breadcrumb', 'Settings')

@push('scripts')
<script src="{{ asset('js/settings.js') }}"></script>
@endpush

@push('init')
    renderSettings(document.getElementById("page-content"));
@endpush
