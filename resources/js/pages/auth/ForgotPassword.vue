<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import AppHeader from '@/components/AppHeader.vue';

defineProps<{
    status?: string;
}>();

const emailInput = ref('');
const isGmail = computed(() => emailInput.value.toLowerCase().endsWith('@gmail.com'));
const inboxHref = computed(() => (isGmail.value ? 'https://mail.google.com/' : ''));

const resendCooldown = ref(0);
let timer: number | undefined;
function startCooldown(seconds = 30) {
    resendCooldown.value = seconds;
    if (timer) clearInterval(timer);
    timer = window.setInterval(() => {
        if (resendCooldown.value > 0) resendCooldown.value -= 1;
        if (resendCooldown.value <= 0 && timer) {
            clearInterval(timer);
            timer = undefined;
        }
    }, 1000);
}

function onSubmit() {
    startCooldown(60);
}
</script>

<template>
    <div style="background: url('/images/homepage/hero.png') center/cover no-repeat fixed; min-height: 100vh; width: 100vw;">
        <Head title="Forgot password" />
        <AppHeader :user="null" active="forgot" />

        <main class="main-content flex items-center justify-center min-h-screen" style="background: rgba(0,0,0,0.55);">
            <div class="fp-card bg-white bg-opacity-90 rounded-2xl shadow-lg p-6 w-full max-w-sm flex flex-col items-center">
                <h1 class="text-3xl font-bold mb-2 text-center">Forgot password</h1>
                <p class="text-sm text-neutral-700 mb-4 text-center">Enter your email to receive a password reset link</p>

                <div v-if="status" class="w-full mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-center text-sm font-medium text-emerald-700">
                    {{ status }}
                </div>

                <Form method="post" :action="route('password.email')" v-slot="{ errors, processing }" @submit="onSubmit" class="w-full flex flex-col gap-3">
                    <div>
                        <label for="email" class="block font-semibold mb-1">Email address</label>
                        <input id="email" type="email" name="email" v-model="emailInput" autofocus autocomplete="off" placeholder="email@example.com" class="w-full border rounded px-3 py-2" />
                        <div v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email }}</div>
                    </div>

                    <button type="submit" :disabled="processing || resendCooldown > 0" class="w-full py-2 rounded bg-black text-white font-semibold text-base hover:bg-neutral-800 transition disabled:opacity-60 disabled:cursor-not-allowed">
                        <span class="inline-flex items-center justify-center gap-2">
                            <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
                            <span v-else>Email password reset link</span>
                            <span v-if="resendCooldown > 0" class="text-xs opacity-80">({{ resendCooldown }}s)</span>
                        </span>
                    </button>
                </Form>

                <div v-if="inboxHref" class="text-center text-sm mt-2">
                    <a :href="inboxHref" target="_blank" rel="noopener" class="text-blue-700 hover:underline">Open inbox</a>
                </div>

                <div class="text-center text-sm text-neutral-800 mt-2">
                    Or, return to <a :href="route('login')" class="hover:underline">log in</a>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
main.main-content {
    min-height: 100vh;
    width: 100%;
    max-width: 100vw;
    overflow-x: hidden;
    margin-top: 54px;
}
.fp-card {
    box-shadow: 0 8px 32px rgba(0,0,0,0.13), 0 2px 8px rgba(0,0,0,0.10);
}
</style>
