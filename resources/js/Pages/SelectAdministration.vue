
<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{
    administrations: any[];
}>();

const form = useForm({
    administration_id: null
});

</script>

<template>
    <div class="flex flex-col gap-4 p-4 justify-center items-center h-screen w-full">
        <div class="flex flex-col gap-4 min-w-[300px]">
        <Select class="w-full" v-model="form.administration_id">
            <SelectTrigger class="w-full">
                <SelectValue placeholder="Select an administration" />
                </SelectTrigger>
                <SelectContent>

                    <SelectItem v-for="administration in administrations" :key="administration.id" :value="administration.id">
                    {{ administration.name }}
                    </SelectItem>
                
      
                </SelectContent>
            </Select>
            <Button @click="form.post(route('select-administration.save'))">Save</Button>
        </div>
    </div>
</template>