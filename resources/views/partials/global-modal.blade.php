{{-- Global Modal Component --}}
<div x-data="{ 
    isOpen: false,
    type: 'info', {{-- info, success, warning, error, confirm --}}
    title: '',
    message: '',
    confirmText: 'Konfirmasi',
    cancelText: 'Batal',
    onConfirm: null,
    onCancel: null,
    actionUrl: '',
    actionMethod: 'POST',

    showAlert(data) {
        this.type = data.type || 'info';
        this.title = data.title || 'Informasi';
        this.message = data.message || '';
        this.confirmText = data.confirmText || 'OK';
        this.onConfirm = data.onConfirm || null;
        this.actionUrl = '';
        this.isOpen = true;
    },

    showConfirm(data) {
        this.type = 'confirm';
        this.title = data.title || 'Konfirmasi';
        this.message = data.message || 'Apakah Anda yakin?';
        this.confirmText = data.confirmText || 'Ya, Lanjutkan';
        this.cancelText = data.cancelText || 'Batal';
        this.actionUrl = data.actionUrl || '';
        this.actionMethod = data.actionMethod || 'POST';
        this.onConfirm = data.onConfirm || null;
        this.isOpen = true;
    },

    handleConfirm() {
        if (this.actionUrl) {
            {{-- If it's a form action, we'll let the form in the template handle it --}}
            document.getElementById('global-confirm-form').submit();
        } else if (this.onConfirm) {
            this.onConfirm();
            this.isOpen = false;
        } else {
            this.isOpen = false;
        }
    }
}" 
@global-alert.window="showAlert($event.detail)"
@global-confirm.window="showConfirm($event.detail)"
x-show="isOpen" 
x-cloak
style="display: none;"
class="fixed inset-0 z-[999] flex items-center justify-center p-4">
    
    {{-- Backdrop --}}
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="absolute inset-0 bg-gray-900/80 backdrop-blur-md"></div>

    {{-- Modal Card --}}
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-4"
         class="bg-gray-800 border border-gray-700 rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden relative z-10">
        
        <div class="p-6 flex flex-col items-center text-center">
            {{-- Icon Section --}}
            <div class="w-16 h-16 rounded-full flex items-center justify-center mb-4 shadow-inner"
                 :class="{
                    'bg-indigo-500/10 text-indigo-400': type === 'info' || type === 'confirm',
                    'bg-emerald-500/10 text-emerald-400': type === 'success',
                    'bg-amber-500/10 text-amber-500': type === 'warning',
                    'bg-red-500/10 text-red-500': type === 'error'
                 }">
                <i data-lucide="info" x-show="type === 'info'" class="w-8 h-8"></i>
                <i data-lucide="check-circle" x-show="type === 'success'" class="w-8 h-8"></i>
                <i data-lucide="alert-triangle" x-show="type === 'warning' || type === 'confirm'" class="w-8 h-8"></i>
                <i data-lucide="x-circle" x-show="type === 'error'" class="w-8 h-8"></i>
            </div>

            <h3 class="text-xl font-black text-white mb-2 italic tracking-tight uppercase" x-text="title"></h3>
            <p class="text-gray-400 text-sm font-medium leading-relaxed" x-text="message"></p>
        </div>

        {{-- Actions --}}
        <div class="px-6 py-4 bg-gray-900/50 border-t border-gray-700/50 flex flex-col sm:flex-row gap-2">
            <template x-if="type === 'confirm'">
                <button type="button" @click="isOpen = false" 
                        class="flex-1 px-4 py-2.5 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-xl text-xs font-black uppercase tracking-widest border border-gray-700 transition-all">
                    <span x-text="cancelText"></span>
                </button>
            </template>
            
            <form id="global-confirm-form" :action="actionUrl" method="POST" class="flex-1 m-0">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <template x-if="actionMethod !== 'POST'">
                    <input type="hidden" name="_method" :value="actionMethod">
                </template>
                
                <button type="button" @click="handleConfirm()"
                        class="w-full px-4 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all shadow-lg active:scale-95"
                        :class="{
                            'bg-indigo-600 hover:bg-indigo-500 text-white shadow-indigo-600/20': type === 'info' || type === 'confirm',
                            'bg-emerald-600 hover:bg-emerald-500 text-white shadow-emerald-600/20': type === 'success',
                            'bg-amber-600 hover:bg-amber-500 text-white shadow-amber-600/20': type === 'warning',
                            'bg-red-600 hover:bg-red-500 text-white shadow-red-600/20': type === 'error'
                        }">
                    <span x-text="confirmText"></span>
                </button>
            </form>
        </div>
    </div>
</div>
