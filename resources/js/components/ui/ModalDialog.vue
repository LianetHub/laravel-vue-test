<template>
    <dialog
        ref="dialog"
        class="modal"
        :aria-labelledby="titleId"
        @cancel.prevent="close"
        @click="backdrop"
    >
        <div class="modal__header">
            <AppTitle as="h2" size="lg" :id="titleId">{{ title }}</AppTitle>
            <button
                class="icon-button"
                type="button"
                aria-label="Закрыть"
                :disabled="busy"
                @click="close"
            >
                <IconClose aria-hidden="true" />
            </button>
        </div>
        <slot />
    </dialog>
</template>

<script setup lang="ts">
    import { onMounted, onBeforeUnmount, ref } from "vue";
    import AppTitle from "@/components/ui/AppTitle.vue";
    import IconClose from "@/icons/close.svg";

    const props = defineProps<{ title: string; titleId: string; busy?: boolean }>();
    const emit = defineEmits<{ close: [] }>();
    const dialog = ref<HTMLDialogElement>();
    let previousFocus: HTMLElement | null = null;

    function close() {
        if (!props.busy) emit("close");
    }

    function backdrop(event: MouseEvent) {
        if (event.target !== dialog.value) return;
        const box = dialog.value.getBoundingClientRect();
        if (
            event.clientX < box.left ||
            event.clientX > box.right ||
            event.clientY < box.top ||
            event.clientY > box.bottom
        )
            close();
    }

    onMounted(() => {
        previousFocus = document.activeElement as HTMLElement;
        dialog.value?.showModal();
    });
    onBeforeUnmount(() => {
        dialog.value?.close();
        previousFocus?.focus();
    });
</script>

<style lang="scss">
    .modal {
        width: min(480px, calc(100vw - 32px));
        padding: 24px;
        border: 0;
        border-radius: 20px;
        color: var(--text-primary);
        background: var(--bg-surface);
        box-shadow: 0 16px 60px var(--shadow-25);

        &::backdrop {
            background: var(--overlay);
        }

        @include mq-sm {
            padding: 20px;
        }

        &__header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;

            .icon-button {
                color: var(--text-secondary);

                &:hover {
                    color: var(--text-primary);
                }
            }
        }

        &__body {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        &__actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 8px;

            @include mq-sm {
                flex-wrap: wrap;

                .app-button {
                    flex: 1;
                    font-size: 14px;
                    padding-inline: 10px;
                }
            }
        }

        &__error {
            font-size: 14px;
            color: var(--danger);
        }
    }
</style>
