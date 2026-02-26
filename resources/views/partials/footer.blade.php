{{-- Toast Notification Container --}}
<div id="toast-container" class="fixed top-20 sm:top-5 right-0 sm:right-5 z-[200] space-y-3 pointer-events-none left-0 sm:left-auto px-4 sm:px-0 mx-auto w-full max-w-sm"></div>

{{-- Modal Container --}}
<div id="modal-overlay" class="hidden fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4 transition-opacity">
    <div class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col" id="modal">
        <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between" id="modal-header"></div>
        <div class="p-5 flex-1 overflow-y-auto" id="modal-body"></div>
        <div class="px-5 py-4 border-t border-gray-700/50 bg-gray-800/50 flex justify-end gap-3" id="modal-footer"></div>
    </div>
</div>

{{-- Shared Scripts --}}
@stack('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    @stack('init')
    setTimeout(() => { if (window.lucide) lucide.createIcons(); }, 100);
});
</script>
