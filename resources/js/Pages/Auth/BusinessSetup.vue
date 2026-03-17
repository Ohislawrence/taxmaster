<template>
    <GuestLayout>
        <Head title="Complete Business Setup" />

        <div class="min-h-screen bg-[#F9FAFB] flex flex-col items-center py-12 px-4 sm:px-6 lg:px-8 font-sans">
            
            <div class="w-full max-w-2xl mb-8 flex justify-between items-center">
                <Link href="/" class="transition-transform hover:scale-105">
                    <img src="/taxmaster-one.png" alt="TaxMaster" class="h-8 w-auto" />
                </Link>
                <div class="flex items-center space-x-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Step 1 of 1</span>
                    <div class="w-16 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                        <div class="w-full h-full bg-blue-600 animate-pulse"></div>
                    </div>
                </div>
            </div>

            <div class="w-full max-w-2xl bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden transition-all duration-500">
                
                <div class="p-8 border-b border-gray-50 bg-white">
                    <h1 class="text-2xl font-bold text-[#011b33]">Create your business profile</h1>
                    <p class="text-gray-500 mt-1">Setup your tax identity to start managing compliance in Nigeria.</p>
                </div>

                <form @submit.prevent="submitForm" class="p-8 space-y-8">
                    
                    <div v-if="hasErrors" class="p-4 bg-red-50 rounded-lg flex items-start space-x-3 border border-red-100 animate-shake">
                        <svg class="w-5 h-5 text-red-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-red-800">Check the information provided</p>
                            <p class="text-xs text-red-600 mt-1">There are {{ Object.keys(pageErrors).length + Object.keys(clientErrors).length }} fields that need your attention.</p>
                        </div>
                    </div>

                    <section class="space-y-5">
                        <h2 class="text-xs font-bold uppercase tracking-widest text-blue-600 mb-4">Legal Identity</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium text-gray-700">Business Name</label>
                                <input v-model="form.name" type="text" placeholder="e.g. TaxMaster Inc." 
                                    :class="inputClass('name')" />
                                <span v-if="pageErrors.name || clientErrors.name" class="text-xs text-red-500">{{ pageErrors.name || clientErrors.name }}</span>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-sm font-medium text-gray-700">Business Type</label>
                                <select v-model="form.business_type" :class="inputClass('business_type')">
                                    <option value="">Select Type</option>
                                    <option value="limited_liability">Limited Liability Company</option>
                                    <option value="sole_proprietor">Sole Proprietorship</option>
                                    <option value="partnership">Partnership</option>
                                    <option value="corporation">Corporation</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium text-gray-700">TIN (Tax ID)</label>
                                <input v-model="form.tax_identification_number" type="text" placeholder="00000000-0001" 
                                    :class="inputClass('tax_identification_number')" />
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium text-gray-700">CAC Registration No.</label>
                                <input v-model="form.registration_number" type="text" placeholder="RC123456" 
                                    :class="inputClass('registration_number')" />
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-sm font-medium text-gray-700">Business Description</label>
                            <textarea v-model="form.description" rows="3" placeholder="Describe your business (optional)" :class="inputClass('description')"></textarea>
                        </div>
                    </section>

                    <section class="space-y-5 pt-4 border-t border-gray-50">
                        <h2 class="text-xs font-bold uppercase tracking-widest text-blue-600 mb-4">Contact & Location</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium text-gray-700">Work Email</label>
                                <input v-model="form.email" type="email" placeholder="admin@company.com" 
                                    :class="inputClass('email')" />
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium text-gray-700">Phone Number</label>
                                <input v-model="form.phone" type="tel" placeholder="08012345678" 
                                    :class="inputClass('phone')" />
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-sm font-medium text-gray-700">Street Address</label>
                            <input v-model="form.address" type="text" placeholder="House No, Street Name" 
                                :class="inputClass('address')" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium text-gray-700">City</label>
                                <input v-model="form.city" type="text" placeholder="Lagos" :class="inputClass('city')" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium text-gray-700">State</label>
                                <select v-model="form.state" :class="inputClass('state')">
                                    <option value="">Select State</option>
                                    <option v-for="s in states" :key="s" :value="s">{{ s }}</option>
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium text-gray-700">Industry</label>
                                <select v-model="form.industry" :class="inputClass('industry')">
                                    <option value="">Select Industry</option>
                                    <option value="technology">Technology</option>
                                    <option value="healthcare">Healthcare</option>
                                    <option value="retail">Retail</option>
                                    <option value="manufacturing">Manufacturing</option>
                                    <option value="finance">Finance</option>
                                    <option value="education">Education</option>
                                    <option value="hospitality">Hospitality</option>
                                    <option value="transportation">Transportation</option>
                                    <option value="real_estate">Real Estate</option>
                                    <option value="entertainment">Entertainment</option>
                                    <option value="consulting">Consulting</option>
                                    <option value="agriculture">Agriculture</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                    </section>

                    <!-- Accounts & Tax fields removed to match BusinessSetupController validation -->

                    <div class="pt-6">
                        <button 
                            type="submit" 
                            :disabled="processing"
                            class="w-full flex items-center justify-center py-4 px-6 border border-transparent rounded-lg text-base font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100 transition-all duration-200 disabled:opacity-70 disabled:cursor-not-allowed"
                        >
                            <template v-if="processing">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Authenticating...
                            </template>
                            <template v-else>
                                Create Business Profile
                            </template>
                        </button>
                        <p class="text-center text-xs text-gray-400 mt-4">
                            By clicking, you agree to our 
                            <a href="#" class="text-blue-600 hover:underline">Compliance Standards</a>
                        </p>
                    </div>
                </form>
            </div>
            
            <div class="mt-8 flex items-center space-x-6 opacity-50 grayscale hover:grayscale-0 transition-all">
                 <img src="https://upload.wikimedia.org/wikipedia/commons/b/b5/NDPR_Logo.png" alt="NDPR" class="h-6" />
                 <span class="text-xs font-semibold text-gray-500 italic">Secured by NRS Protocols</span>
            </div>
        </div>
    </GuestLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, usePage, router, Link } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'

const page = usePage()
const props = defineProps({
    states: { type: Array, default: () => ['Lagos','Abuja','Rivers','Ogun','Oyo','Kano'] }
})

const states = props.states

const pageErrors = computed(() => page.props.errors || {})
const hasErrors = computed(() => Object.keys(pageErrors.value).length + Object.keys(clientErrors.value).length > 0)
const processing = ref(false)

const clientErrors = ref({})

const form = ref({
    name: '',
    business_type: '',
    tax_identification_number: '',
    registration_number: '',
    email: '',
    phone: '',
    address: '',
    city: '',
    state: '',
    description: '',
    industry: '',
    // keep only fields the controller accepts
})

// logo upload removed — controller does not accept it

const inputClass = (errorKey) => {
    const hasError = (pageErrors.value && pageErrors.value[errorKey]) || clientErrors.value[errorKey]
    return [
        'block w-full px-4 py-3 rounded-lg border text-gray-900 text-sm transition-all duration-200 outline-none focus:ring-2',
        hasError
            ? 'border-red-300 bg-red-50 focus:ring-red-100 focus:border-red-400'
            : 'border-gray-200 bg-white focus:ring-blue-50 focus:border-blue-500 hover:border-gray-300'
    ]
}

// handleLogo removed

const validateClient = () => {
    clientErrors.value = {}
    if (!form.value.name || !form.value.name.trim()) clientErrors.value.name = 'Business name is required.'
    if (!form.value.email || !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(form.value.email)) clientErrors.value.email = 'A valid email is required.'
    if (!form.value.phone || form.value.phone.replace(/\D/g, '').length < 7) clientErrors.value.phone = 'Please enter a valid phone number.'
    if (!form.value.state) clientErrors.value.state = 'Please select a state.'
    if (!form.value.business_type) clientErrors.value.business_type = 'Please select a business type.'
    if (!form.value.industry) clientErrors.value.industry = 'Please select an industry.'

    return Object.keys(clientErrors.value).length === 0
}

const submitForm = () => {
    processing.value = true
    clientErrors.value = {}

    if (!validateClient()) {
        processing.value = false
        return
    }

    // sanitize string inputs
    const payloadObj = {}
    Object.entries(form.value).forEach(([k, v]) => {
        payloadObj[k] = (typeof v === 'string') ? v.trim() : v
    })

    router.post('/business-setup', payloadObj, {
        preserveState: false,
        onFinish: () => processing.value = false,
    })
}
</script>

<style scoped>
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-4px); }
    75% { transform: translateX(4px); }
}
.animate-shake {
    animation: shake 0.4s ease-in-out;
}
</style>