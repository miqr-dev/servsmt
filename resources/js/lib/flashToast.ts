import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

type FlashProps = {
    success?: string | null;
    error?: string | null;
    status?: string | null;
    // Older controllers (Role/Permission/User CRUD) flash
    // ['message' => ..., 'alert-type' => 'success'|'danger'|...] instead.
    message?: string | null;
    alertType?: string | null;
};

/**
 * The app's controllers flash plain session strings (->with('success', '...'),
 * session('status'), etc. - see HandleInertiaRequests::share()). This just
 * surfaces those as toasts after every Inertia visit, instead of introducing
 * a new flash-message convention the existing controllers don't use.
 */
export function initializeFlashToast(): void {
    router.on('success', (event) => {
        const flash = (event as CustomEvent).detail?.page?.props?.flash as
            | FlashProps
            | undefined;

        if (!flash) {
            return;
        }

        if (flash.success) {
            toast.success(flash.success);
        }

        if (flash.status) {
            toast.success(flash.status);
        }

        if (flash.error) {
            toast.error(flash.error);
        }

        if (flash.message) {
            if (flash.alertType === 'danger' || flash.alertType === 'error') {
                toast.error(flash.message);
            } else {
                toast.success(flash.message);
            }
        }
    });
}
