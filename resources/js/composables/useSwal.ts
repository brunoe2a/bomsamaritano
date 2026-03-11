import Swal from 'sweetalert2';
import { router } from '@inertiajs/vue3';

const swalTheme = {
    background: '#1a1a2e',
    color: '#e0e0e0',
    confirmButtonColor: '#F5A623',
    cancelButtonColor: '#6b7280',
    iconColor: '#F5A623',
};

export function useSwal() {
    /**
     * Confirmação de exclusão com SweetAlert2
     */
    async function confirmDelete(
        message: string,
        deleteUrl: string,
        options?: { method?: 'delete' | 'post'; onSuccess?: () => void }
    ): Promise<void> {
        const result = await Swal.fire({
            title: 'Tem certeza?',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim, remover',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            ...swalTheme,
        });

        if (result.isConfirmed) {
            const method = options?.method || 'delete';
            router.visit(deleteUrl, {
                method,
                preserveScroll: true,
                onSuccess: () => {
                    options?.onSuccess?.();
                },
            });
        }
    }

    /**
     * Confirmação genérica
     */
    async function confirmAction(
        title: string,
        text: string,
        confirmText = 'Confirmar',
    ): Promise<boolean> {
        const result = await Swal.fire({
            title,
            text,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: confirmText,
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            ...swalTheme,
        });
        return result.isConfirmed;
    }

    /**
     * Toast de sucesso
     */
    function toastSuccess(message: string): void {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: message,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            background: '#1a1a2e',
            color: '#e0e0e0',
        });
    }

    /**
     * Toast de erro
     */
    function toastError(message: string): void {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: message,
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            background: '#1a1a2e',
            color: '#e0e0e0',
        });
    }

    /**
     * Toast de info
     */
    function toastInfo(message: string): void {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'info',
            title: message,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            background: '#1a1a2e',
            color: '#e0e0e0',
        });
    }

    /**
     * Alerta de erro completo (modal)
     */
    function alertError(title: string, text: string): void {
        Swal.fire({
            title,
            text,
            icon: 'error',
            confirmButtonText: 'Fechar',
            ...swalTheme,
        });
    }

    return {
        confirmDelete,
        confirmAction,
        toastSuccess,
        toastError,
        toastInfo,
        alertError,
    };
}
