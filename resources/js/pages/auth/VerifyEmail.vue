<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import AppHeader from '@/components/AppHeader.vue';

const props = defineProps<{
    status?: string;
    email?: string;
}>();

const resendCooldown = ref(0);
let cooldownTimer: number | undefined;

function startCooldown(seconds = 30) {
    resendCooldown.value = seconds;
    stopCooldown();
    cooldownTimer = window.setInterval(() => {
        if (resendCooldown.value > 0) resendCooldown.value -= 1;
        if (resendCooldown.value <= 0) stopCooldown();
    }, 1000);
}
function stopCooldown() {
    if (cooldownTimer) {
        clearInterval(cooldownTimer);
        cooldownTimer = undefined;
    }
}

// Poll for verification status
let pollTimer: number | undefined;
async function checkVerified() {
    try {
        const url = (window as any).route ? (window as any).route('verification.status') : '/verify-email/status';
        const resp = await fetch(url, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });
        if (!resp.ok) return;
        const data = await resp.json();
        if (data?.verified && data?.redirect) {
            window.location.href = data.redirect + '?verified=1';
        }
    } catch {
        // ignore transient errors
    }
}

onMounted(() => {
    // Start polling every 5s for up to ~5 minutes
    pollTimer = window.setInterval(checkVerified, 5000);
});
onUnmounted(() => {
    if (pollTimer) clearInterval(pollTimer);
    stopCooldown();
});

const isGmail = computed(() => (props.email ?? '').toLowerCase().endsWith('@gmail.com'));
const inboxHref = computed(() => (isGmail.value ? 'https://mail.google.com/' : ''));

function onResend() {
    // Start a visual cooldown so users don't spam the button
    startCooldown(60);
}
 </script>

<template>
    <div style="background: url('/images/homepage/hero.png') center/cover no-repeat fixed; min-height: 100vh; width: 100vw;">
        <Head title="Verify email" />
        <AppHeader :user="null" active="verify" />

        <main class="main-content flex items-center justify-center min-h-screen" style="background: rgba(0,0,0,0.55);">
            <div class="signup-card bg-white bg-opacity-90 rounded-2xl shadow-lg p-6 w-full max-w-sm flex flex-col items-center">
                <h1 class="text-3xl font-bold mb-4 text-center">Verify email</h1>

                <div v-if="status === 'verification-link-sent'" class="w-full rounded-md bg-emerald-50 px-4 py-3 text-sm text-emerald-700 border border-emerald-200 mb-3">
                    A new verification link has been sent to <strong>{{ email }}</strong>.
                </div>

                <div class="w-full text-center mb-4">
                    <div class="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">✉️</div>
                    <p class="text-sm text-neutral-700">We sent a link to <strong>{{ email }}</strong>. Click it to verify. This page will auto-redirect once verified.</p>
                </div>

                <ul class="w-full list-disc space-y-1 text-sm text-neutral-700 pl-6 mb-4">
                    <li>It may take a few seconds to arrive. Check Spam/Promotions tabs too.</li>
                    <li>You can request another link if you don’t see it.</li>
                </ul>

                <Form method="post" :action="route('verification.send')" v-slot="{ processing }" @submit="onResend" class="w-full flex flex-col items-center gap-3">
                    <button type="submit" :disabled="processing || resendCooldown > 0" class="w-full py-2 rounded bg-black text-white font-semibold text-base hover:bg-neutral-800 transition disabled:opacity-60 disabled:cursor-not-allowed">
                        <span class="inline-flex items-center justify-center gap-2">
                            <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
                            <span v-else>Resend verification email</span>
                            <span v-if="resendCooldown > 0" class="text-xs opacity-80">({{ resendCooldown }}s)</span>
                        </span>
                    </button>
                </Form>

                <div v-if="inboxHref" class="text-sm mt-2">
                    <a :href="inboxHref" target="_blank" rel="noopener" class="text-blue-700 hover:underline">Open inbox</a>
                </div>

                <Form :action="route('logout')" method="post" class="mt-2">
                    <button type="submit" class="text-sm text-neutral-800 hover:underline">Log out</button>
                </Form>
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
.signup-card {
    box-shadow: 0 8px 32px rgba(0,0,0,0.13), 0 2px 8px rgba(0,0,0,0.10);
}
</style>
