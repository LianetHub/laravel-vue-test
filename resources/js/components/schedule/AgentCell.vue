<template>
    <div class="agent-cell">
        <div class="avatar">
            <img
                v-if="employee.avatar_url && !failed"
                :src="employee.avatar_url"
                :alt="employee.full_name"
                class="avatar__photo"
                @error="failed = true"
            />
            <IconAvatarPlaceholder v-else class="avatar__placeholder" aria-hidden="true" />
        </div>
        <div class="agent-cell__info">
            <div class="agent-cell__name" :title="employee.full_name">
                {{ employee.full_name }}
            </div>
            <div class="agent-cell__meta">
                {{ employee.position }}
                <span>·</span>
                {{ formatHours(hours) }} ч
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
    import { ref, watch } from "vue";
    import type { Employee } from "@/types";
    import IconAvatarPlaceholder from "@/icons/avatar-placeholder.svg";
    import { formatHours } from "@/utils/dates";

    const props = defineProps<{ employee: Employee; hours: number }>();
    const failed = ref(false);
    watch(
        () => props.employee.avatar_url,
        () => {
            failed.value = false;
        },
    );
</script>

<style lang="scss" scoped>
    .agent-cell {
        position: sticky;
        left: 0;
        z-index: 3;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 12px 12px 8px;
        height: 72px;
        background: inherit;
        min-width: 0;

        &__info {
            min-width: 0;
        }

        &__name {
            font-size: 16px;
            line-height: 24px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        &__meta {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            line-height: 16px;
            color: var(--text-secondary);
            white-space: nowrap;
        }
    }

    .avatar {
        position: relative;
        width: 40px;
        height: 40px;
        flex-shrink: 0;
        border: 0.5px solid var(--bg-muted);
        background: var(--bg-hover);
        border-radius: 4px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);

        :deep(.avatar__placeholder) {
            width: 19px;
            height: 21px;
        }

        &__photo {
            position: absolute;
            top: -0.5px;
            left: -0.5px;
            width: 40px;
            height: 40px;
            object-fit: cover;

            &[src="/assets/img/avatar-artem-sokolov.png"] {
                width: 133.48%;
                height: 132.7%;
                left: -17.19%;
                top: -4.17%;
            }

            &[src="/assets/img/avatar-polina-lebedeva.png"] {
                width: 154.07%;
                height: 161.67%;
                left: -26.52%;
                top: -16.78%;
            }

            &[src="/assets/img/avatar-kirill-novikov.png"] {
                width: 130%;
                height: 133.35%;
                left: -10.6%;
                top: -7.34%;
            }

            &[src="/assets/img/avatar-darya-erokhina.png"] {
                width: 127.5%;
                height: 139.82%;
                left: -14.76%;
                top: -4.83%;
            }
        }
    }
</style>
