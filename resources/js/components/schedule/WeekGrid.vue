<template><ScheduleGrid :employees="employees" mode="week" /></template>

<script setup lang="ts">
    import ScheduleGrid from "./ScheduleGrid.vue";
    import type { Employee } from "@/types";
    defineProps<{ employees: Employee[] }>();
</script>
