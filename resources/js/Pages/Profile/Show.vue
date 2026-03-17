<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AccountantLayout from '@/Layouts/AccountantLayout.vue';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue';
import LogoutOtherBrowserSessionsForm from '@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue';
import SectionBorder from '@/Components/SectionBorder.vue';
import TwoFactorAuthenticationForm from '@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';

defineProps({
    confirmsTwoFactorAuthentication: Boolean,
    sessions: Array,
});

const page = usePage();

const LayoutComponent = computed(() => {
    // Prefer explicit `user` prop passed by controller, fall back to auth user
    const candidate = page.props.user || page.props.auth?.user || {};

    const extractRoleNames = (u) => {
        if (!u) return [];

        // If roles is a direct array of strings or objects
        const roles = u.roles;
        if (Array.isArray(roles) && roles.length > 0) {
            return roles.map(r => typeof r === 'string' ? r : (r.name || r)).filter(Boolean);
        }

        // If roles is an object with a `data` array (e.g., paginated/collection shape)
        if (roles && Array.isArray(roles.data) && roles.data.length > 0) {
            return roles.data.map(r => typeof r === 'string' ? r : (r.name || r)).filter(Boolean);
        }

        // If there's a role_names or roleNames property provided by backend
        if (Array.isArray(u.role_names) && u.role_names.length > 0) return u.role_names.map(String);
        if (Array.isArray(u.roleNames) && u.roleNames.length > 0) return u.roleNames.map(String);

        // If single role string
        if (typeof u.role === 'string' && u.role) return [u.role];

        // As a last resort, try to read roles as plain string
        if (typeof roles === 'string' && roles) return [roles];

        return [];
    };

    const roleNames = extractRoleNames(candidate).map(r => String(r).toLowerCase());

    if (roleNames.includes('admin')) return AdminLayout;
    if (roleNames.includes('accountant')) return AccountantLayout;
    if (roleNames.includes('business')) return BusinessLayout;

    return AdminLayout;
});
</script>

<template>
    <component :is="LayoutComponent" title="Profile">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Profile
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <div v-if="$page.props.jetstream.canUpdateProfileInformation">
                    <UpdateProfileInformationForm :user="$page.props.auth.user" />

                    <SectionBorder />
                </div>

                <div v-if="$page.props.jetstream.canUpdatePassword">
                    <UpdatePasswordForm class="mt-10 sm:mt-0" />

                    <SectionBorder />
                </div>

                <div v-if="$page.props.jetstream.canManageTwoFactorAuthentication">
                    <TwoFactorAuthenticationForm
                        :requires-confirmation="confirmsTwoFactorAuthentication"
                        class="mt-10 sm:mt-0"
                    />

                    <SectionBorder />
                </div>

                <LogoutOtherBrowserSessionsForm :sessions="sessions" class="mt-10 sm:mt-0" />

                <template v-if="$page.props.jetstream.hasAccountDeletionFeatures">
                    <SectionBorder />

                    <DeleteUserForm class="mt-10 sm:mt-0" />
                </template>
            </div>
        </div>
    </component>
</template>
