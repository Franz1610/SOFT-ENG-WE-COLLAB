<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';

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
    <AuthLayout title="Verify email" description="We've emailed a verification link to confirm your account.">
        <Head title="Email verification" />

        <div class="space-y-6">
            <div v-if="status === 'verification-link-sent'" class="rounded-md bg-emerald-50 px-4 py-3 text-sm text-emerald-700 border border-emerald-200">
                A new verification link has been sent to <strong>{{ email }}</strong>.
            </div>

            <div class="rounded-xl border px-6 py-6">
                <div class="mb-4 text-center">
                    <div class="mx-auto mb-2 flex size-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">✉️</div>
                    <h2 class="text-lg font-semibold">Check your inbox</h2>
                    <p class="mt-1 text-sm text-muted-foreground">We sent a link to <strong>{{ email }}</strong>. Click it to verify. This page will auto-redirect once verified.</p>
                </div>

                <ul class="mx-auto mb-4 list-disc space-y-1 text-sm text-muted-foreground pl-6">
                    <li>It may take a few seconds to arrive. Check Spam/Promotions tabs too.</li>
                    <li>You can request another link if you don’t see it.</li>
                </ul>

                <div class="flex flex-col items-center gap-3">
                    <Form method="post" :action="route('verification.send')" v-slot="{ processing }" @submit="onResend">
                        <Button :disabled="processing || resendCooldown > 0" variant="secondary">
                            <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
                            <span v-else>Resend verification email</span>
                            <span v-if="resendCooldown > 0" class="ml-2 text-xs opacity-80">({{ resendCooldown }}s)</span>
                        </Button>
                    </Form>

                    <div v-if="inboxHref" class="text-sm">
                        <a :href="inboxHref" target="_blank" rel="noopener" class="underline">Open inbox</a>
                    </div>

                    <TextLink :href="route('logout')" method="post" as="button" class="text-sm"> Log out </TextLink>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>
