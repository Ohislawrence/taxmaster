<template>
    <BusinessLayout>
        <Head title="CAC Annual Return Forms" />

        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-6xl mx-auto space-y-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">CAC Annual Return Forms</h1>
                <p class="text-gray-600">Generate Form AR and Notice of Situation</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6 space-y-6">
                <div class="flex justify-end">
                    <button
                        @click="downloadPdf"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
                        :disabled="downloading"
                    >
                        {{ downloading ? 'Preparing...' : 'Download PDF' }}
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm text-gray-600">Company Name</label>
                        <input v-model="form.company_name" type="text" class="border-gray-300 rounded-lg w-full" />
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">RC Number</label>
                        <input v-model="form.rc_number" type="text" class="border-gray-300 rounded-lg w-full" />
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Incorporation Date</label>
                        <input v-model="form.incorporation_date" type="date" class="border-gray-300 rounded-lg w-full" />
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Registered Address</label>
                        <input v-model="form.registered_address" type="text" class="border-gray-300 rounded-lg w-full" />
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Business Address</label>
                        <input v-model="form.business_address" type="text" class="border-gray-300 rounded-lg w-full" />
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Email</label>
                        <input v-model="form.email" type="email" class="border-gray-300 rounded-lg w-full" />
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Phone</label>
                        <input v-model="form.phone" type="text" class="border-gray-300 rounded-lg w-full" />
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Nature of Business</label>
                        <input v-model="form.nature_of_business" type="text" class="border-gray-300 rounded-lg w-full" />
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Share Capital</label>
                        <input v-model="form.share_capital" type="text" class="border-gray-300 rounded-lg w-full" />
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Secretary Name</label>
                        <input v-model="form.secretary_name" type="text" class="border-gray-300 rounded-lg w-full" />
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Secretary Address</label>
                        <input v-model="form.secretary_address" type="text" class="border-gray-300 rounded-lg w-full" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <h2 class="text-lg font-semibold text-gray-900">Directors</h2>
                            <button @click="addDirector" class="text-blue-600 hover:text-blue-800 text-sm">Add</button>
                        </div>
                        <div v-for="(director, index) in form.directors" :key="index" class="border rounded-lg p-3 mb-3">
                            <input v-model="director.name" type="text" placeholder="Name" class="border-gray-300 rounded-lg w-full mb-2" />
                            <input v-model="director.address" type="text" placeholder="Address" class="border-gray-300 rounded-lg w-full" />
                            <button @click="removeDirector(index)" class="text-xs text-red-600 mt-2">Remove</button>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <h2 class="text-lg font-semibold text-gray-900">Shareholders</h2>
                            <button @click="addShareholder" class="text-blue-600 hover:text-blue-800 text-sm">Add</button>
                        </div>
                        <div v-for="(shareholder, index) in form.shareholders" :key="index" class="border rounded-lg p-3 mb-3">
                            <input v-model="shareholder.name" type="text" placeholder="Name" class="border-gray-300 rounded-lg w-full mb-2" />
                            <input v-model="shareholder.shares" type="text" placeholder="Shareholding" class="border-gray-300 rounded-lg w-full" />
                            <button @click="removeShareholder(index)" class="text-xs text-red-600 mt-2">Remove</button>
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">Notice of Situation</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm text-gray-600">Registered Office Address</label>
                            <input v-model="form.notice_registered_address" type="text" class="border-gray-300 rounded-lg w-full" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600">Effective Date</label>
                            <input v-model="form.notice_effective_date" type="date" class="border-gray-300 rounded-lg w-full" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import axios from 'axios';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';

const props = defineProps({
    business: Object,
    defaults: Object,
});

const downloading = ref(false);

const form = reactive({
    company_name: props.defaults.company_name,
    rc_number: props.defaults.rc_number,
    incorporation_date: props.defaults.incorporation_date,
    registered_address: props.defaults.registered_address,
    business_address: props.defaults.business_address,
    email: props.defaults.email,
    phone: props.defaults.phone,
    nature_of_business: props.defaults.nature_of_business,
    share_capital: props.defaults.share_capital,
    secretary_name: props.defaults.secretary_name,
    secretary_address: props.defaults.secretary_address,
    directors: props.defaults.directors.length ? props.defaults.directors : [{ name: '', address: '' }],
    shareholders: props.defaults.shareholders.length ? props.defaults.shareholders : [{ name: '', shares: '' }],
    notice_registered_address: props.defaults.notice_registered_address,
    notice_effective_date: props.defaults.notice_effective_date,
});

const addDirector = () => {
    form.directors.push({ name: '', address: '' });
};

const removeDirector = (index) => {
    form.directors.splice(index, 1);
};

const addShareholder = () => {
    form.shareholders.push({ name: '', shares: '' });
};

const removeShareholder = (index) => {
    form.shareholders.splice(index, 1);
};

const downloadPdf = async () => {
    downloading.value = true;
    try {
        const response = await axios.post(route('business.reports.cac-forms.pdf'), form, {
            responseType: 'blob',
        });
        const blob = new Blob([response.data], { type: 'application/pdf' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = 'cac-forms.pdf';
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
    } finally {
        downloading.value = false;
    }
};
</script>
