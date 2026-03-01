import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useGetStarted() {
    const page = usePage();
    
    // Get Get Started progress from props
    const progress = computed(() => page.props.getStartedProgress || null);
    const completionPercentage = computed(() => page.props.completionPercentage || 0);
    const isCompleted = computed(() => page.props.isCompleted || false);
    const steps = computed(() => page.props.steps || []);
    
    /**
     * Check if a specific step is completed
     */
    const isStepCompleted = (stepId) => {
        return progress.value?.completed_steps?.includes(stepId) ?? false;
    };
    
    /**
     * Get a step by ID
     */
    const getStep = (stepId) => {
        return steps.value.find(s => s.id === stepId);
    };
    
    /**
     * Get all steps sorted by order
     */
    const sortedSteps = computed(() => {
        return [...steps.value].sort((a, b) => a.order - b.order);
    });
    
    /**
     * Get next incomplete step
     */
    const nextIncompleteStep = computed(() => {
        return sortedSteps.value.find(s => !s.is_completed);
    });
    
    /**
     * Get completed steps count
     */
    const completedStepsCount = computed(() => {
        return steps.value.filter(s => s.is_completed).length;
    });
    
    /**
     * Get total steps count
     */
    const totalStepsCount = computed(() => steps.value.length);
    
    /**
     * Get steps grouped by priority
     */
    const stepsByPriority = computed(() => {
        return {
            high: sortedSteps.value.filter(s => s.priority === 'high'),
            medium: sortedSteps.value.filter(s => s.priority === 'medium'),
            low: sortedSteps.value.filter(s => s.priority === 'low'),
        };
    });
    
    /**
     * Mark step as completed
     */
    const completeStep = async (stepId) => {
        try {
            const response = await fetch('/business/get-started/complete-step', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': page.props.csrf_token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ step_id: stepId }),
            });
            
            if (!response.ok) throw new Error('Failed to complete step');
            
            // Reload props to get updated progress
            await page.reload();
            return true;
        } catch (error) {
            console.error('Error completing step:', error);
            return false;
        }
    };
    
    /**
     * Mark step as incomplete
     */
    const incompleteStep = async (stepId) => {
        try {
            const response = await fetch('/business/get-started/incomplete-step', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': page.props.csrf_token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ step_id: stepId }),
            });
            
            if (!response.ok) throw new Error('Failed to mark step incomplete');
            
            await page.reload();
            return true;
        } catch (error) {
            console.error('Error marking step incomplete:', error);
            return false;
        }
    };
    
    /**
     * Dismiss Get Started guide
     */
    const dismissGuide = async (snoozeMinutes = 0) => {
        try {
            const response = await fetch('/business/get-started/dismiss', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': page.props.csrf_token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ snooze_minutes: snoozeMinutes }),
            });
            
            if (!response.ok) throw new Error('Failed to dismiss');
            return true;
        } catch (error) {
            console.error('Error dismissing guide:', error);
            return false;
        }
    };
    
    /**
     * Undismiss Get Started guide
     */
    const undismissGuide = async () => {
        try {
            const response = await fetch('/business/get-started/undismiss', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': page.props.csrf_token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({}),
            });
            
            if (!response.ok) throw new Error('Failed to undismiss');
            return true;
        } catch (error) {
            console.error('Error undismissing guide:', error);
            return false;
        }
    };
    
    /**
     * Get progress bar color based on percentage
     */
    const progressColor = computed(() => {
        if (completionPercentage.value >= 100) return 'bg-green-500';
        if (completionPercentage.value >= 75) return 'bg-blue-500';
        if (completionPercentage.value >= 50) return 'bg-yellow-500';
        return 'bg-orange-500';
    });
    
    /**
     * Get progress message
     */
    const progressMessage = computed(() => {
        if (isCompleted.value) {
            return "🎉 You're all set! You can now enjoy the full TaxMaster experience.";
        }
        
        const completed = completedStepsCount.value;
        const total = totalStepsCount.value;
        const remaining = total - completed;
        
        return `${completed} of ${total} steps completed. ${remaining} step(s) remaining.`;
    });
    
    return {
        progress,
        completionPercentage,
        isCompleted,
        steps,
        sortedSteps,
        stepsByPriority,
        nextIncompleteStep,
        completedStepsCount,
        totalStepsCount,
        progressColor,
        progressMessage,
        isStepCompleted,
        getStep,
        completeStep,
        incompleteStep,
        dismissGuide,
        undismissGuide,
    };
}
