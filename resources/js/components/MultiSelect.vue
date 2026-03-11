<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { Check, ChevronDown, X, Search } from 'lucide-vue-next';

interface Option {
    id: number | string;
    nome: string;
}

const props = defineProps<{
    modelValue: (number | string)[];
    options: Option[];
    placeholder?: string;
    label?: string;
}>();

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const search = ref('');
const containerRef = ref<HTMLElement | null>(null);

const filteredOptions = ref<Option[]>([]);

watch([search, () => props.options], () => {
    if (!search.value) {
        filteredOptions.value = props.options;
    } else {
        const query = search.value.toLowerCase();
        filteredOptions.value = props.options.filter(opt => 
            opt.nome.toLowerCase().includes(query)
        );
    }
}, { immediate: true });

function toggleOption(id: number | string) {
    const newValue = [...props.modelValue];
    const index = newValue.indexOf(id);
    if (index === -1) {
        newValue.push(id);
    } else {
        newValue.splice(index, 1);
    }
    emit('update:modelValue', newValue);
}

function removeOption(id: number | string) {
    const newValue = props.modelValue.filter(val => val !== id);
    emit('update:modelValue', newValue);
}

const selectedOptions = ref<Option[]>([]);
watch([() => props.modelValue, () => props.options], () => {
    selectedOptions.value = props.options.filter(opt => 
        props.modelValue.includes(opt.id)
    );
}, { immediate: true });

function handleClickOutside(event: MouseEvent) {
    if (containerRef.value && !containerRef.value.contains(event.target as Node)) {
        isOpen.value = false;
    }
}

onMounted(() => {
    document.addEventListener('mousedown', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('mousedown', handleClickOutside);
});
</script>

<template>
    <div ref="containerRef" class="relative w-full">
        <label v-if="label" class="mb-1 block text-sm font-medium text-foreground">
            {{ label }}
        </label>
        
        <div 
            @click="isOpen = !isOpen"
            class="flex min-h-[42px] w-full cursor-pointer flex-wrap gap-1.5 rounded-lg border border-input bg-background px-3 py-1.5 text-sm transition-all focus-within:ring-2 focus-within:ring-primary/20 hover:border-primary/50"
            :class="{ 'ring-2 ring-primary/20 border-primary': isOpen }"
        >
            <div v-if="selectedOptions.length === 0" class="flex h-7 items-center text-muted-foreground">
                {{ placeholder || 'Selecione...' }}
            </div>
            
            <div 
                v-for="opt in selectedOptions" 
                :key="opt.id"
                class="flex h-7 items-center gap-1 rounded-md bg-primary/10 pl-2 pr-1 text-xs font-medium text-primary animate-in fade-in zoom-in duration-200"
            >
                {{ opt.nome }}
                <button 
                    @click.stop="removeOption(opt.id)"
                    class="rounded-sm p-0.5 hover:bg-primary/20"
                >
                    <X class="h-3 w-3" />
                </button>
            </div>
            
            <div class="ml-auto flex h-7 items-center border-l pl-2 text-muted-foreground">
                <ChevronDown class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': isOpen }" />
            </div>
        </div>

        <!-- Dropdown -->
        <div 
            v-if="isOpen"
            class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-lg border border-border bg-popover p-1 shadow-xl animate-in fade-in slide-in-from-top-2 duration-200"
        >
            <div class="sticky top-0 z-10 bg-popover pb-1">
                <div class="relative">
                    <Search class="absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <input 
                        v-model="search"
                        @click.stop
                        type="text" 
                        placeholder="Pesquisar..." 
                        class="h-9 w-full rounded-md border-none bg-muted/50 pl-9 pr-3 text-sm outline-none focus:bg-muted"
                    />
                </div>
            </div>
            
            <div class="mt-1 space-y-0.5">
                <div 
                    v-for="opt in filteredOptions" 
                    :key="opt.id"
                    @click.stop="toggleOption(opt.id)"
                    class="flex cursor-pointer items-center justify-between rounded-md px-2.5 py-2 text-sm transition-colors hover:bg-accent hover:text-accent-foreground"
                    :class="{ 'bg-accent/50': modelValue.includes(opt.id) }"
                >
                    <span>{{ opt.nome }}</span>
                    <Check 
                        v-if="modelValue.includes(opt.id)" 
                        class="h-4 w-4 text-primary" 
                    />
                </div>
                
                <div v-if="filteredOptions.length === 0" class="py-4 text-center text-xs text-muted-foreground">
                    Nenhum resultado encontrado.
                </div>
            </div>
        </div>
    </div>
</template>
