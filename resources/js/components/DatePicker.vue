<template>
    <div class="relative" ref="rootRef">
        <button
            type="button"
            @click="toggle"
            :disabled="disabled"
            class="flex w-full items-center justify-between rounded-lg border border-border bg-background px-3 py-2 text-sm transition-colors focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary disabled:cursor-not-allowed disabled:opacity-50"
            :class="[
                !modelValue && 'text-muted-foreground',
                error && 'border-red-500 ring-red-500/30',
            ]"
        >
            <span>{{ modelValue ? formatDisplay(modelValue) : placeholder }}</span>
            <svg class="h-4 w-4 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </button>

        <Transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0 translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-1"
        >
            <div
                v-if="isOpen"
                class="absolute z-50 mt-1 w-[280px] select-none rounded-lg border border-border bg-card p-3 shadow-lg"
            >
                <div class="mb-2 flex items-center justify-between">
                    <button type="button" @click="goPrev" class="inline-flex h-8 w-8 items-center justify-center rounded text-muted-foreground transition hover:bg-muted">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    </button>
                    <button type="button" @click="onTitleClick" class="rounded px-3 py-1.5 text-sm font-semibold transition hover:bg-muted">{{ headerTitle }}</button>
                    <button type="button" @click="goNext" class="inline-flex h-8 w-8 items-center justify-center rounded text-muted-foreground transition hover:bg-muted">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </button>
                </div>

                <div v-if="view === 'days'">
                    <div class="mb-1 grid grid-cols-7 gap-1">
                        <div v-for="(d, i) in weekDays" :key="i" class="py-1 text-center text-[10px] font-bold uppercase text-muted-foreground">{{ d }}</div>
                    </div>
                    <div class="grid grid-cols-7 gap-1">
                        <button
                            v-for="(cell, i) in dayCells"
                            :key="i"
                            type="button"
                            @click="cell.day && selectDay(cell.day)"
                            :disabled="!cell.day"
                            :class="[
                                'aspect-square rounded text-sm transition',
                                cell.day ? 'hover:bg-muted' : 'invisible',
                                cell.selected && 'bg-primary font-semibold text-primary-foreground hover:bg-primary',
                                !cell.selected && cell.today && 'bg-muted/60 font-semibold',
                            ]"
                        >
                            {{ cell.day }}
                        </button>
                    </div>
                </div>

                <div v-else-if="view === 'months'" class="grid grid-cols-3 gap-2">
                    <button
                        v-for="(m, idx) in monthNames"
                        :key="idx"
                        type="button"
                        @click="selectMonth(idx)"
                        :class="[
                            'rounded py-2 text-sm transition hover:bg-muted',
                            idx === viewMonth && 'bg-primary font-semibold text-primary-foreground hover:bg-primary',
                        ]"
                    >
                        {{ m }}
                    </button>
                </div>

                <div v-else class="grid grid-cols-3 gap-2">
                    <button
                        v-for="y in yearGrid"
                        :key="y"
                        type="button"
                        @click="selectYear(y)"
                        :class="[
                            'rounded py-2 text-sm transition hover:bg-muted',
                            y === viewYear && 'bg-primary font-semibold text-primary-foreground hover:bg-primary',
                        ]"
                    >
                        {{ y }}
                    </button>
                </div>

                <div class="mt-3 flex justify-center border-t border-border/50 pt-2">
                    <button type="button" @click="goToday" class="text-xs font-medium text-primary hover:underline">Hoje</button>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

const props = withDefaults(defineProps<{
    modelValue: string | null | undefined;
    placeholder?: string;
    error?: boolean;
    disabled?: boolean;
}>(), {
    placeholder: 'Selecione a data',
    error: false,
    disabled: false,
});

const emit = defineEmits<{ (e: 'update:modelValue', v: string): void }>();

const isOpen = ref(false);
const view = ref<'days' | 'months' | 'years'>('days');
const rootRef = ref<HTMLElement | null>(null);

const normalize = (v: any): string => {
    if (!v) return '';
    return v.toString().split('T')[0];
};

const parseInitial = (): { y: number; m: number } => {
    const v = normalize(props.modelValue);
    if (v) {
        const [y, m] = v.split('-').map(Number);
        if (y && m) return { y, m: m - 1 };
    }
    const now = new Date();
    return { y: now.getFullYear(), m: now.getMonth() };
};

const viewYear = ref(parseInitial().y);
const viewMonth = ref(parseInitial().m);

watch(() => props.modelValue, (v) => {
    const s = normalize(v);
    if (s) {
        const [y, m] = s.split('-').map(Number);
        if (y && m) { viewYear.value = y; viewMonth.value = m - 1; }
    }
});

watch(isOpen, (open) => { if (open) view.value = 'days'; });

const monthNames = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
const monthNamesFull = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
const weekDays = ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'];

const headerTitle = computed(() => {
    if (view.value === 'days') return `${monthNamesFull[viewMonth.value]} de ${viewYear.value}`;
    if (view.value === 'months') return `${viewYear.value}`;
    const start = Math.floor(viewYear.value / 12) * 12;
    return `${start} – ${start + 11}`;
});

const dayCells = computed(() => {
    const firstDay = new Date(viewYear.value, viewMonth.value, 1).getDay();
    const daysInMonth = new Date(viewYear.value, viewMonth.value + 1, 0).getDate();
    const cells: Array<{ day: number | null; selected?: boolean; today?: boolean }> = [];
    for (let i = 0; i < firstDay; i++) cells.push({ day: null });
    const now = new Date();
    const todayY = now.getFullYear(), todayM = now.getMonth(), todayD = now.getDate();
    const [selY, selM, selD] = normalize(props.modelValue).split('-').map(Number);
    for (let d = 1; d <= daysInMonth; d++) {
        cells.push({
            day: d,
            selected: selY === viewYear.value && (selM - 1) === viewMonth.value && selD === d,
            today: todayY === viewYear.value && todayM === viewMonth.value && todayD === d,
        });
    }
    while (cells.length % 7 !== 0) cells.push({ day: null });
    return cells;
});

const yearGrid = computed(() => {
    const start = Math.floor(viewYear.value / 12) * 12;
    return Array.from({ length: 12 }, (_, i) => start + i);
});

function toggle() {
    if (props.disabled) return;
    isOpen.value = !isOpen.value;
}
function close() { isOpen.value = false; }

function onTitleClick() {
    if (view.value === 'days') view.value = 'months';
    else if (view.value === 'months') view.value = 'years';
}

function goPrev() {
    if (view.value === 'days') {
        if (viewMonth.value === 0) { viewMonth.value = 11; viewYear.value--; }
        else viewMonth.value--;
    } else if (view.value === 'months') viewYear.value--;
    else viewYear.value -= 12;
}

function goNext() {
    if (view.value === 'days') {
        if (viewMonth.value === 11) { viewMonth.value = 0; viewYear.value++; }
        else viewMonth.value++;
    } else if (view.value === 'months') viewYear.value++;
    else viewYear.value += 12;
}

function selectDay(d: number) {
    const mm = String(viewMonth.value + 1).padStart(2, '0');
    const dd = String(d).padStart(2, '0');
    emit('update:modelValue', `${viewYear.value}-${mm}-${dd}`);
    close();
}

function selectMonth(idx: number) { viewMonth.value = idx; view.value = 'days'; }
function selectYear(y: number) { viewYear.value = y; view.value = 'months'; }

function goToday() {
    const now = new Date();
    viewYear.value = now.getFullYear();
    viewMonth.value = now.getMonth();
    view.value = 'days';
    const mm = String(now.getMonth() + 1).padStart(2, '0');
    const dd = String(now.getDate()).padStart(2, '0');
    emit('update:modelValue', `${now.getFullYear()}-${mm}-${dd}`);
    close();
}

function formatDisplay(val: string) {
    const s = normalize(val);
    if (!s) return '';
    const [y, m, d] = s.split('-');
    if (!y || !m || !d) return val;
    return `${d}/${m}/${y}`;
}

function handleClickOutside(e: MouseEvent) {
    if (rootRef.value && !rootRef.value.contains(e.target as Node)) close();
}
onMounted(() => document.addEventListener('mousedown', handleClickOutside));
onUnmounted(() => document.removeEventListener('mousedown', handleClickOutside));
</script>
