{{-- Toast Notification Container --}}
<div id="toast-container" class="toast-container"></div>

{{-- Modal Container --}}
<div id="modal-overlay" class="modal-overlay hidden">
    <div class="modal" id="modal">
        <div class="modal-header" id="modal-header"></div>
        <div class="modal-body" id="modal-body"></div>
        <div class="modal-footer" id="modal-footer"></div>
    </div>
</div>

{{-- Shared Scripts --}}
<script src="{{ asset('js/store.js') }}"></script>
<script src="{{ asset('js/app-static.js') }}"></script>
@stack('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    initStaticApp();
    @stack('init')
    setTimeout(() => { if (window.lucide) lucide.createIcons(); }, 100);
});
</script>
