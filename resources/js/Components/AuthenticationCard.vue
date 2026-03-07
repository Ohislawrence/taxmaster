<template>
    <div class="min-h-screen flex flex-col justify-center items-center bg-white relative overflow-hidden">
        <!-- Subtle grid background -->
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#f0f0f0_1px,transparent_1px),linear-gradient(to_bottom,#f0f0f0_1px,transparent_1px)] bg-[size:4rem_4rem] pointer-events-none"></div>
        <!-- Blue accent circle -->
        <div class="absolute -top-24 -left-24 w-80 h-80 rounded-full bg-blue-100/60 blur-3xl"></div>
        <div class="absolute bottom-0 right-0 w-64 h-64 rounded-full bg-blue-200/40 blur-2xl"></div>

        <div class="relative w-full max-w-md mx-auto px-6 py-8 bg-white/90 shadow-2xl rounded-2xl border border-blue-50 z-10">
            <div class="flex justify-center mb-6">
                <slot name="logo" />
            </div>
            <slot />
        </div>
    </div>
</template>
