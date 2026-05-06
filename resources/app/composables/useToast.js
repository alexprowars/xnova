import { toast } from 'vue3-toastify';

export const useToast = (message, type) => {
	toast(message, { type: type || 'default' });
}

export const useSuccessNotification = (message) => {
	toast(message, { type: 'success' });
}

export const useErrorNotification = (message) => {
	toast(message, { type: 'error' });
}

export default useToast;