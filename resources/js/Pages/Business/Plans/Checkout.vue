<template>
  <BusinessLayout>
    <div class="bg-gray-50 py-6 sm:py-12 px-3 sm:px-4">
      <BillingGuardNotice />
      <div class="max-w-4xl mx-auto space-y-6 sm:space-y-8">
      <!-- Header -->
      <div class="text-center">
        <h1 class="text-3xl font-bold text-gray-900">Complete Your Subscription</h1>
        <p class="text-gray-600 mt-2">Review your details and proceed to payment</p>
      </div>

      <div class="grid md:grid-cols-3 gap-8">
        <!-- Order Summary -->
        <div class="md:col-span-2 space-y-6">
          <!-- Business Details -->
          <div class="bg-white rounded-lg shadow-sm p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Business Details</h2>
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div>
                <p class="text-gray-600">Business Name</p>
                <p class="font-semibold text-gray-900">{{ business.name }}</p>
              </div>
              <div>
                <p class="text-gray-600">Registration Number</p>
                <p class="font-semibold text-gray-900">{{ business.registration_number || 'N/A' }}</p>
              </div>
              <div>
                <p class="text-gray-600">Business Type</p>
                <p class="font-semibold text-gray-900 capitalize">{{ business.business_type }}</p>
              </div>
              <div>
                <p class="text-gray-600">Email</p>
                <p class="font-semibold text-gray-900 break-all">{{ business.email }}</p>
              </div>
            </div>
          </div>

          <!-- Plan Selection -->
          <div class="bg-white rounded-lg shadow-sm p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Plan Details</h2>
            <div class="space-y-3">
              <div>
                <p class="text-gray-600 text-sm">Selected Plan</p>
                <p class="text-2xl font-bold text-gray-900">{{ plan.name }}</p>
              </div>
              <p class="text-gray-600">{{ plan.description }}</p>
            </div>
          </div>

          <!-- Billing Cycle Selection -->
          <div class="bg-white rounded-lg shadow-sm p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Billing Cycle</h2>
            <div class="space-y-3">
              <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition" :class="billingCycle === 'monthly' ? 'border-blue-600 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                <input
                  v-model="billingCycle"
                  type="radio"
                  value="monthly"
                  class="w-4 h-4 accent-blue-600"
                />
                <div class="ml-4">
                  <p class="font-semibold text-gray-900">Monthly Billing</p>
                  <p class="text-sm text-gray-600">Billed every month</p>
                </div>
                <div class="ml-auto font-semibold text-gray-900">
                  ₦{{ formatPrice(plan.monthly_price) }}
                </div>
              </label>

              <label v-if="plan.annual_price" class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition" :class="billingCycle === 'annual' ? 'border-blue-600 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                <input
                  v-model="billingCycle"
                  type="radio"
                  value="annual"
                  class="w-4 h-4 accent-blue-600"
                />
                <div class="ml-4">
                  <p class="font-semibold text-gray-900">Annual Billing</p>
                  <p class="text-sm text-gray-600">Billed once per year</p>
                  <span class="inline-block mt-1 px-2 py-1 bg-green-100 text-green-800 text-xs font-bold rounded">
                    Save ₦{{ formatPrice((plan.monthly_price * 12) - plan.annual_price) }}
                  </span>
                </div>
                <div class="ml-auto font-semibold text-gray-900">
                  ₦{{ formatPrice(plan.annual_price) }}
                </div>
              </label>
            </div>
          </div>

          <!-- Plan Features -->
          <div class="bg-white rounded-lg shadow-sm p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">What's Included</h2>
            <div class="space-y-3">
              <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                <p class="text-gray-700">{{ plan.max_returns_per_year }} Tax Returns per Year</p>
              </div>
              <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                <p class="text-gray-700">{{ plan.max_staff_members }} Staff Members</p>
              </div>
              <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                <p class="text-gray-700">{{ plan.storage_gb }} GB Storage</p>
              </div>
              <div v-if="plan.ai_analysis_included" class="flex items-center gap-3">
                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                <p class="text-gray-700">AI Tax Analysis & Insights</p>
              </div>
              <div v-if="plan.payment_automation" class="flex items-center gap-3">
                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                <p class="text-gray-700">Automated Payment Processing</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Pricing Card -->
        <div class="bg-white rounded-lg shadow-sm p-6 h-fit sticky top-8 space-y-6">
          <div class="space-y-4">
            <div>
              <p class="text-gray-600 text-sm">Plan:</p>
              <p class="text-lg font-semibold text-gray-900">{{ plan.name }}</p>
            </div>

            <div class="border-t pt-4 space-y-3">
              <div class="flex justify-between text-gray-700">
                <p>Subtotal:</p>
                <p class="font-semibold">
                  ₦{{ formatPrice(billingCycle === 'annual' && plan.annual_price ? plan.annual_price : plan.monthly_price) }}
                </p>
              </div>
              <div class="flex justify-between text-gray-700">
                <p>Tax (VAT):</p>
                <p class="font-semibold">Calculated at checkout</p>
              </div>
            </div>

            <div class="border-t pt-4">
              <div class="flex justify-between">
                <p class="font-bold text-gray-900 text-lg">Total:</p>
                <p class="font-bold text-lg text-blue-600">
                  ₦{{ formatPrice(billingCycle === 'annual' && plan.annual_price ? plan.annual_price : plan.monthly_price) }}
                </p>
              </div>
            </div>

            <div class="text-xs text-gray-600 p-3 bg-gray-50 rounded">
              <p v-if="billingCycle === 'annual'">You will be billed ₦{{ formatPrice(plan.annual_price) }} once per year.</p>
              <p v-else>You will be billed ₦{{ formatPrice(plan.monthly_price) }} every month.</p>
            </div>
          </div>

          <!-- Payment Button -->
          <button
            @click="proceedToPayment"
            :disabled="processing"
            class="w-full px-6 py-3 rounded-lg font-semibold text-white transition"
            :class="[
              plan.monthly_price === 0
                ? 'bg-green-600 hover:bg-green-700'
                : 'bg-blue-600 hover:bg-blue-700',
              processing && 'opacity-50 cursor-not-allowed',
            ]"
          >
            {{ processing ? 'Processing...' : (plan.monthly_price === 0 ? 'Activate Free Plan' : 'Proceed to Payment') }}
          </button>

          <p class="text-xs text-gray-600 text-center">
            {{ plan.monthly_price === 0 ? 'No payment required' : 'Payment processed securely by Paystack' }}
          </p>

          <!-- Back Link -->
          <a href="/business/plans" class="block text-center text-sm text-blue-600 hover:text-blue-700 font-medium">
            Back to Plans
          </a>
        </div>
      </div>

      <!-- Terms -->
      <div class="max-w-4xl mx-auto p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <p class="text-sm text-gray-700">
          By clicking "{{ plan.monthly_price === 0 ? 'Activate Free Plan' : 'Proceed to Payment' }}", you agree to our
          <a href="#" class="text-blue-600 hover:underline">Terms of Service</a> and
          <a href="#" class="text-blue-600 hover:underline">Privacy Policy</a>.
          {{ plan.monthly_price === 0 ? 'Your account will be activated immediately.' : 'Your billing will start after successful payment.' }}
        </p>
      </div>
    </div>
    </div>
  </BusinessLayout>
</template>

<script setup>
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import BillingGuardNotice from '@/Components/BillingGuardNotice.vue';

const page = usePage();
const plan = ref(page.props.plan);
const business = ref(page.props.business);
const currentSubscription = ref(page.props.currentSubscription);

const billingCycle = ref('monthly');
const processing = ref(false);

const formatPrice = (price) => {
  return parseFloat(price).toLocaleString('en-NG', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  });
};

const proceedToPayment = async () => {
  processing.value = true;

  try {
    const response = await axios.post(`/business/plans/${plan.value.id}/checkout`, {
      billing_cycle: billingCycle.value,
    });

    if (response.data.success) {
      if (response.data.payment_url) {
        // Redirect to Paystack payment
        window.location.href = response.data.payment_url;
      } else if (response.data.redirect) {
        // Free plan activated
        window.location.href = response.data.redirect;
      } else {
        processing.value = false;
        alert('Checkout response incomplete. Please try again.');
      }
    }
  } catch (error) {
    processing.value = false;
    alert(error.response?.data?.error || 'An error occurred. Please try again.');
  }
};
</script>
