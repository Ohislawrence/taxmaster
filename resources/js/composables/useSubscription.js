import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useSubscription() {
    const page = usePage();
    
    // Get current subscription and plan details
    const subscription = computed(() => page.props.auth?.user?.ownedBusiness?.active_subscription || null);
    const plan = computed(() => subscription.value?.plan || null);
    const planName = computed(() => plan.value?.name?.toLowerCase() || 'free');
    
    // Plan hierarchy for comparison
    const planHierarchy = {
        'free': 0,
        'basic': 1,
        'professional': 2,
        'enterprise': 3
    };
    
    /**
     * Check if current plan meets minimum requirement
     */
    const hasPlan = (requiredPlan) => {
        const currentLevel = planHierarchy[planName.value] || 0;
        const requiredLevel = planHierarchy[requiredPlan.toLowerCase()] || 0;
        return currentLevel >= requiredLevel;
    };
    
    /**
     * Check if user can perform specific feature action
     */
    const canUseFeature = (featureName) => {
        if (!plan.value?.features) {
            return false;
        }
        
        // Parse features JSON if it's a string
        const features = typeof plan.value.features === 'string' 
            ? JSON.parse(plan.value.features) 
            : plan.value.features;
        
        return features.includes(featureName);
    };
    
    /**
     * Feature-specific checks
     */
    const can = {
        // Tax returns
        filePAYE: computed(() => hasPlan('free')),
        fileWHT: computed(() => hasPlan('free')),
        fileCIT: computed(() => hasPlan('basic')),
        fileVAT: computed(() => hasPlan('basic')),
        fileCGT: computed(() => hasPlan('professional')),
        
        // AI features
        useAiAnalysis: computed(() => hasPlan('professional') && canUseFeature('use_ai_analysis')),
        useAiChat: computed(() => hasPlan('professional') && canUseFeature('use_ai_chat')),
        useAiOptimization: computed(() => hasPlan('professional') && canUseFeature('use_ai_optimization')),
        
        // Banking
        linkBankAccount: computed(() => hasPlan('basic') && canUseFeature('link_bank_account')),
        autoSyncTransactions: computed(() => hasPlan('basic') && canUseFeature('auto_sync_transactions')),
        
        // Reporting
        generateFinancialStatements: computed(() => hasPlan('professional') && canUseFeature('generate_financial_statements')),
        generateCacForms: computed(() => hasPlan('professional') && canUseFeature('generate_cac_forms')),
        exportPdf: computed(() => hasPlan('basic') && canUseFeature('export_pdf')),
        advancedReporting: computed(() => hasPlan('professional') && canUseFeature('advanced_reporting')),
        
        // API & Integration
        useApi: computed(() => hasPlan('professional') && canUseFeature('use_api')),
        
        // Enterprise features
        customBranding: computed(() => hasPlan('enterprise') && canUseFeature('custom_branding')),
        whiteLabel: computed(() => hasPlan('enterprise') && canUseFeature('white_label')),
        multiBusiness: computed(() => hasPlan('enterprise') && canUseFeature('multi_business')),
        dedicatedAccountManager: computed(() => hasPlan('enterprise') && canUseFeature('dedicated_account_manager')),
    };
    
    /**
     * Usage stats and limits
     */
    const usage = computed(() => {
        const business = page.props.auth?.user?.ownedBusiness;
        if (!business) return null;
        
        return {
            staff_count: business.staff_count || 0,
            returns_this_year: business.returns_this_year || 0,
            storage_used_mb: business.storage_used_mb || 0,
        };
    });
    
    const limits = computed(() => {
        if (!plan.value) {
            return {
                staff_limit: 1,
                returns_per_year: 12,
                storage_gb: 1,
            };
        }
        
        return {
            staff_limit: plan.value.staff_limit || 1,
            returns_per_year: plan.value.returns_per_year || 12,
            storage_gb: plan.value.storage_gb || 1,
        };
    });
    
    /**
     * Check if usage is near limit (>80%)
     */
    const isNearLimit = (metric) => {
        if (!usage.value || !limits.value) return false;
        
        switch(metric) {
            case 'staff':
                return usage.value.staff_count >= (limits.value.staff_limit * 0.8);
            case 'returns':
                return usage.value.returns_this_year >= (limits.value.returns_per_year * 0.8);
            case 'storage':
                return (usage.value.storage_used_mb / 1024) >= (limits.value.storage_gb * 0.8);
            default:
                return false;
        }
    };
    
    /**
     * Check if usage exceeded limit
     */
    const hasExceededLimit = (metric) => {
        if (!usage.value || !limits.value) return false;
        
        switch(metric) {
            case 'staff':
                return usage.value.staff_count >= limits.value.staff_limit;
            case 'returns':
                return usage.value.returns_this_year >= limits.value.returns_per_year;
            case 'storage':
                return (usage.value.storage_used_mb / 1024) >= limits.value.storage_gb;
            default:
                return false;
        }
    };
    
    /**
     * Get upgrade message for feature
     */
    const getUpgradeMessage = (feature) => {
        const messages = {
            'file_cit': 'Upgrade to Basic or higher to file CIT returns',
            'file_vat': 'Upgrade to Basic or higher to file VAT returns',
            'file_cgt': 'Upgrade to Professional or higher to file CGT returns',
            'use_ai_chat': 'Upgrade to Professional or higher to use AI Tax Advisor',
            'use_ai_optimization': 'Upgrade to Professional or higher to use AI optimization',
            'link_bank_account': 'Upgrade to Basic or higher to link bank accounts',
            'generate_financial_statements': 'Upgrade to Professional or higher to generate financial statements',
            'use_api': 'Upgrade to Professional or higher to use API access',
            'custom_branding': 'Upgrade to Enterprise for custom branding',
            'white_label': 'Upgrade to Enterprise for white-label solution',
        };
        
        return messages[feature] || 'Upgrade your plan to access this feature';
    };
    
    /**
     * Get required plan name for a feature
     */
    const getRequiredPlan = (feature) => {
        const featurePlans = {
            'file_paye': 'free',
            'file_wht': 'free',
            'file_cit': 'basic',
            'file_vat': 'basic',
            'link_bank_account': 'basic',
            'export_pdf': 'basic',
            'file_cgt': 'professional',
            'use_ai_analysis': 'professional',
            'use_ai_chat': 'professional',
            'use_ai_optimization': 'professional',
            'generate_financial_statements': 'professional',
            'generate_cac_forms': 'professional',
            'use_api': 'professional',
            'custom_branding': 'enterprise',
            'white_label': 'enterprise',
            'multi_business': 'enterprise',
        };
        
        return featurePlans[feature] || 'professional';
    };
    
    return {
        subscription,
        plan,
        planName,
        hasPlan,
        canUseFeature,
        can,
        usage,
        limits,
        isNearLimit,
        hasExceededLimit,
        getUpgradeMessage,
        getRequiredPlan,
    };
}
