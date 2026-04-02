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
<style>
    /* Hide native calendar icon to prevent double icons when using flatpickr */
    input[type="date"]::-webkit-calendar-picker-indicator {
        display: none;
        -webkit-appearance: none;
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', () => {
    @stack('init')
    
    // Initialize Flatpickr globally for all date inputs
    const flatpickrConfig = {
        dateFormat: "Y-m-d",
        disableMobile: "true",
        animate: true,
        static: false, 
        onChange: function(selectedDates, dateStr, instance) {
            // Trigger native change/input event so Alpine's x-model picks it up
            instance.element.dispatchEvent(new Event('input', { bubbles: true }));
        }
    };

    // Auto-init on existing date inputs
    flatpickr('input[type="date"]', flatpickrConfig);

    // Watch for new date inputs (for Alpine.js templates/modals)
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === 1) { // Element node
                    const dateInputs = node.querySelectorAll('input[type="date"]');
                    if (dateInputs.length > 0) flatpickr(dateInputs, flatpickrConfig);
                }
            });
        });
    });
    observer.observe(document.body, { childList: true, subtree: true });

    setTimeout(() => { if (window.lucide) lucide.createIcons(); }, 100);
});
</script>
