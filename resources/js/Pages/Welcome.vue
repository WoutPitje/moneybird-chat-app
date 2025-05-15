<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import { router } from '@inertiajs/vue3';

const loginWithMoneybird = () => {
    window.location.href= route('moneybird.login');
}

</script>

<template>
    <div class="flex flex-col items-center justify-center h-screen gap-8">
        <img src="https://images.prismic.io/moneybird/ZkXWrCol0Zci9Mhe_logo_birdblue.png?auto=format,compress" alt="Moneybird" class="w-[200px]" />
        <Button class=" text-white" @click="loginWithMoneybird">Login with Moneybird</Button>
    </div>
</template>
