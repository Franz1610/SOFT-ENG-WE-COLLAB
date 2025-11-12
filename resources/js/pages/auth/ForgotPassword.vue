<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { computed, ref } from 'vue';

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
    <AuthLayout title="Forgot password" description="Enter your email to receive a password reset link">
        <Head title="Forgot password" />

        <div v-if="status" class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-center text-sm font-medium text-emerald-700">
            {{ status }}
        </div>

        <div class="space-y-6">
            <Form method="post" :action="route('password.email')" v-slot="{ errors, processing }" @submit="onSubmit">
                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input id="email" type="email" name="email" v-model="emailInput" autocomplete="off" autofocus placeholder="email@example.com" />
                    <InputError :message="errors.email" />
                </div>

                <div class="my-6 flex items-center justify-start">
                    <Button class="w-full" :disabled="processing || resendCooldown > 0">
                        <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
                        <span v-else>Email password reset link</span>
                        <span v-if="resendCooldown > 0" class="ml-2 text-xs opacity-80">({{ resendCooldown }}s)</span>
                    </Button>
                </div>
            </Form>

            <div v-if="inboxHref" class="text-center text-sm mb-3">
                <a :href="inboxHref" target="_blank" rel="noopener" class="underline">Open inbox</a>
            </div>

            <div class="space-x-1 text-center text-sm text-muted-foreground">
                <span>Or, return to</span>
                <TextLink :href="route('login')">log in</TextLink>
            </div>
        </div>
    </AuthLayout>
</template>
