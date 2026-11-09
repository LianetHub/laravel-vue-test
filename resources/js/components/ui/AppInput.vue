<template>
    <div
        class="app-input-field"
        :class="[
            attrs.class,
            {
                'app-input-field--inline': hasIcon && !label,
            },
        ]"
        :style="attrs.style as string | Record<string, string> | undefined"
    >
        <label v-if="label" class="app-input-field__label" :for="inputId">{{ label }}</label>
        <div
            class="app-input"
            :class="[
                `app-input--${tone}`,
                `app-input--${size}`,
                {
                    'app-input--with-icon': hasIcon,
                    'app-input--invalid': !!error || invalid,
                    'app-input--disabled': disabled,
                },
            ]"
        >
            <span v-if="hasIcon" class="app-input__icon" aria-hidden="true">
                <slot name="icon" />
            </span>
            <input
                :id="inputId"
                :value="modelValue"
                :type="type"
                :placeholder="placeholder"
                :disabled="disabled"
                :required="required"
                :maxlength="maxlength"
                :aria-label="ariaLabel || (!label ? placeholder || undefined : undefined)"
                :aria-invalid="error || invalid ? true : undefined"
                :aria-describedby="describedBy"
                v-bind="inputAttrs"
                @input="onInput"
            />
        </div>
        <p v-if="error" :id="errorId" class="app-input-field__error" role="alert">
            {{ error }}
        </p>
    </div>
</template>

<script setup lang="ts">
    import { computed, useAttrs, useId, useSlots } from "vue";

    defineOptions({ inheritAttrs: false });

    const props = withDefaults(
        defineProps<{
            modelValue: string;
            label?: string;
            tone?: "surface" | "muted";
            size?: "md" | "lg";
            type?: string;
            placeholder?: string;
            disabled?: boolean;
            required?: boolean;
            maxlength?: number | string;
            id?: string;
            error?: string;
            invalid?: boolean;
            ariaLabel?: string;
            ariaDescribedby?: string;
        }>(),
        {
            tone: "muted",
            size: "md",
            type: "text",
            disabled: false,
            required: false,
            invalid: false,
        },
    );

    const emit = defineEmits<{ "update:modelValue": [value: string] }>();

    const attrs = useAttrs();
    const slots = useSlots();
    const uid = useId();

    const hasIcon = computed(() => !!slots.icon);
    const inputId = computed(() => props.id || `app-input-${uid}`);
    const errorId = computed(() => `${inputId.value}-error`);
    const describedBy = computed(() => {
        const ids = [props.ariaDescribedby, props.error ? errorId.value : undefined].filter(
            Boolean,
        );
        return ids.length ? ids.join(" ") : undefined;
    });

    const inputAttrs = computed(() => {
        const { class: _class, style: _style, ...rest } = attrs;
        return rest;
    });

    function onInput(event: Event) {
        emit("update:modelValue", (event.target as HTMLInputElement).value);
    }
</script>

<style lang="scss" scoped>
    .app-input-field {
        min-width: 0;

        &--inline {
            flex: 0 1 398px;
        }

        &__label {
            display: block;
            font-size: 12px;
            line-height: 17px;
            color: var(--text-secondary);
            margin-bottom: 4px;
        }

        &__error {
            font-size: 12px;
            line-height: 16px;
            color: var(--danger);
            margin-top: 4px;
        }
    }

    .app-input {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
        width: 100%;
        padding: 12px 16px;
        border: 1px solid transparent;
        border-radius: 8px;
        outline: none;
        color: var(--text-secondary);

        &--surface {
            background: var(--bg-surface);
        }

        &--muted {
            background: var(--bg-muted);
        }

        &--md {
            height: 46px;
        }

        &--lg {
            height: 48px;
        }

        &--with-icon {
            padding: 12px;
            color: var(--text-primary);

            &.app-input--lg {
                border-radius: 12px;
            }

            &:focus-within {
                border-color: transparent;
                box-shadow: inset 0 0 0 2px var(--brand);
            }

            input {
                color: var(--text-primary);
                line-height: 22px;
                padding: 0;
            }

            &.app-input--lg input {
                line-height: 24px;
            }
        }

        &:not(.app-input--with-icon) {
            &:focus-within {
                border-color: var(--brand);
                color: var(--text-primary);

                input {
                    color: var(--text-primary);
                }
            }
        }

        &--invalid:not(.app-input--with-icon),
        &--invalid.app-input--with-icon {
            border-color: var(--danger-border);
            box-shadow: none;
        }

        &--disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }

        &__icon {
            display: inline-flex;
            flex-shrink: 0;
            color: var(--text-secondary);

            :deep(svg) {
                width: 16px;
                height: 16px;
            }
        }

        &--lg &__icon :deep(svg) {
            width: 24px;
            height: 24px;
        }

        input {
            width: 100%;
            min-width: 0;
            border: 0;
            padding: 0;
            background: transparent;
            outline: none;
            color: inherit;
            font-size: 16px;
            line-height: 24px;

            &::placeholder {
                color: var(--text-secondary);
                opacity: 1;
            }

            &:disabled {
                cursor: not-allowed;
            }

            &::-webkit-search-decoration,
            &::-webkit-search-cancel-button,
            &::-webkit-search-results-button,
            &::-webkit-search-results-decoration {
                display: none;
            }

            &::-webkit-calendar-picker-indicator {
                display: none;
            }

            &::-webkit-datetime-edit,
            &::-webkit-datetime-edit-fields-wrapper,
            &::-webkit-datetime-edit-hour-field,
            &::-webkit-datetime-edit-minute-field,
            &::-webkit-datetime-edit-text {
                padding: 0;
            }
        }
    }
</style>
