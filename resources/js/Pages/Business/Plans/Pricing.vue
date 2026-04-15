<template>
  <BusinessLayout>
    <Head title="Pricing" />
    <div class="bg-gradient-to-b from-blue-50 to-white py-6 sm:py-12 px-3 sm:px-4">
      <div class="max-w-7xl mx-auto space-y-8 sm:space-y-12">
      <!-- Header -->
      <div class="text-center space-y-4">
        <h1 class="text-4xl font-bold text-gray-900">
          Simple, Transparent Pricing
        </h1>
        <p class="text-xl text-gray-600 max-w-2xl mx-auto">
          Choose the perfect plan for your business. Always flexible, always fair.
        </p>

        <!-- Billing Toggle -->
        <div class="flex items-center justify-center gap-4 mt-8">
          <span :class="['text-sm font-medium', billingCycle === 'monthly' ? 'text-gray-900' : 'text-gray-600']">
            Monthly
          </span>
          <button
            @click="billingCycle = billingCycle === 'monthly' ? 'annual' : 'monthly'"
            class="relative inline-flex h-8 w-16 items-center rounded-full bg-gray-200 transition-colors"
            :class="billingCycle === 'annual' && 'bg-blue-600'"
          >
            <span
              class="inline-block h-6 w-6 transform rounded-full bg-white transition-transform"
              :class="billingCycle === 'annual' && 'translate-x-9'"
            />
          </button>
          <span :class="['text-sm font-medium', billingCycle === 'annual' ? 'text-gray-900' : 'text-gray-600']">
            Annual <span class="text-green-600 text-xs font-bold">(Save 17%)</span>
          </span>
        </div>
      </div>

      <!-- Plans Grid -->
      <div class="grid md:grid-cols-3 gap-6">
        <div
          v-for="plan in plans.filter(p => p.slug !== 'free')"
          :key="plan.id"
          :class="[
            'relative bg-white rounded-lg shadow-lg overflow-hidden transition-all hover:shadow-xl',
            currentSubscription?.plan_id === plan.id && 'ring-2 ring-blue-600 transform scale-105 md:scale-110',
          ]"
        >
          <!-- Current Plan Badge -->
          <div
            v-if="currentSubscription?.plan_id === plan.id"
            class="absolute top-0 right-0 bg-blue-600 text-white px-4 py-2 rounded-bl-lg text-xs font-bold"
          >
            CURRENT PLAN
          </div>

          <!-- Plan Content -->
          <div class="p-6 space-y-6">
            <!-- Plan Title -->
            <div>
              <h3 class="text-2xl font-bold text-gray-900">{{ plan.name }}</h3>
              <p class="text-sm text-gray-600 mt-2">{{ plan.description }}</p>
            </div>

            <!-- Price -->
            <div>
              <div class="flex items-baseline gap-2">
                <span class="text-4xl font-bold text-gray-900">
                  ₦{{ formatPrice(billingCycle === 'annual' && plan.annual_price ? plan.annual_price : plan.monthly_price) }}
                </span>
                <span class="text-gray-600">
                  /{{ billingCycle === 'annual' ? 'year' : 'month' }}
                </span>
              </div>
              <p v-if="billingCycle === 'annual' && plan.annual_price" class="text-sm text-green-600 mt-2">
                Save ₦{{ formatPrice((plan.monthly_price * 12) - plan.annual_price) }} per year
              </p>
            </div>

            <!-- CTA Button -->
            <button
              v-if="currentSubscription?.plan_id !== plan.id"
              @click="selectPlan(plan)"
              :disabled="processing"
              class="w-full px-6 py-3 rounded-lg font-semibold transition text-white"
              :class="[
                plan.monthly_price === 0
                  ? 'bg-gray-600 hover:bg-gray-700'
                  : 'bg-blue-600 hover:bg-blue-700',
                processing && 'opacity-50 cursor-not-allowed',
              ]"
            >
              {{ plan.monthly_price === 0 ? 'Get Started Free' : 'Subscribe Now' }}
            </button>
            <div v-else class="w-full px-6 py-3 rounded-lg font-semibold text-center bg-gray-100 text-gray-700">
              Active
            </div>

            <!-- Features -->
            <div class="space-y-3 pt-6 border-t">
              <div v-for="(feature, index) in (plan.features || [])" :key="index" class="flex items-start gap-3">
                <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                <p class="text-sm text-gray-700">{{ feature }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- FAQ Section -->
      <div class="max-w-3xl mx-auto space-y-6">
        <h2 class="text-2xl font-bold text-gray-900 text-center">Frequently Asked Questions</h2>

        <div class="space-y-4">
          <div class="bg-white rounded-lg p-6">
            <button
              @click="faqOpen = faqOpen === 0 ? -1 : 0"
              class="w-full flex items-center justify-between"
            >
              <h3 class="font-semibold text-gray-900">Can I change plans anytime?</h3>
              <svg class="w-5 h-5 transition" :class="faqOpen === 0 && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
              </svg>
            </button>
            <p v-if="faqOpen === 0" class="text-gray-600 mt-4">
              Yes! You can upgrade or downgrade your plan at any time. Changes take effect immediately for upgrades and at the next billing cycle for downgrades.
            </p>
          </div>

          <div class="bg-white rounded-lg p-6">
            <button
              @click="faqOpen = faqOpen === 1 ? -1 : 1"
              class="w-full flex items-center justify-between"
            >
              <h3 class="font-semibold text-gray-900">Is there a long-term contract?</h3>
              <svg class="w-5 h-5 transition" :class="faqOpen === 1 && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
              </svg>
            </button>
            <p v-if="faqOpen === 1" class="text-gray-600 mt-4">
              No, there's no long-term contract. All plans are billed monthly or annually, and you can cancel anytime.
            </p>
          </div>

          <div class="bg-white rounded-lg p-6">
            <button
              @click="faqOpen = faqOpen === 2 ? -1 : 2"
              class="w-full flex items-center justify-between"
            >
              <h3 class="font-semibold text-gray-900">Do you offer refunds?</h3>
              <svg class="w-5 h-5 transition" :class="faqOpen === 2 && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
              </svg>
            </button>
            <p v-if="faqOpen === 2" class="text-gray-600 mt-4">
              We offer a 14-day money-back guarantee. If you're not satisfied, we'll refund your payment in full.
            </p>
          </div>
        </div>
      </div>
    </div>
    </div>
  </BusinessLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';

const page = usePage();
const plans = ref(page.props.plans);
const currentSubscription = ref(page.props.currentSubscription);
const business = ref(page.props.business);

const billingCycle = ref('monthly');
const processing = ref(false);
const faqOpen = ref(-1);

const formatPrice = (price) => {
  return parseFloat(price).toLocaleString('en-NG', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  });
};

const selectPlan = (plan) => {
  if (!business.value) {
    // If no business exists, redirect to business setup
    window.location.href = '/business/setup';
    return;
  }

  // Redirect to plan selection page
  window.location.href = `/business/plans/${plan.id}`;
};
</script>




