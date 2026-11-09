import { defineStore } from "pinia";
import { ref } from "vue";

export type ToastTone = "success" | "error";

export type ToastItem = {
    id: number;
    tone: ToastTone;
    message: string;
};

const MAX_TOASTS = 3;

export const useToastStore = defineStore("toast", () => {
    const items = ref<ToastItem[]>([]);

    let nextId = 1;

    function push(tone: ToastTone, message: string): void {
        items.value.push({ id: nextId++, tone, message });
        if (items.value.length > MAX_TOASTS) {
            items.value.splice(0, items.value.length - MAX_TOASTS);
        }
    }

    function success(message: string): void {
        push("success", message);
    }

    function error(message: string): void {
        push("error", message);
    }

    function dismiss(id: number): void {
        items.value = items.value.filter((item) => item.id !== id);
    }

    function clear(): void {
        items.value = [];
    }

    return { items, success, error, dismiss, clear };
});
