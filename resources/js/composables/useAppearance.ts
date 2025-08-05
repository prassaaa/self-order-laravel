import { onMounted, ref } from 'vue';

type Appearance = 'light';

export function updateTheme(value: Appearance) {
    if (typeof window === 'undefined') {
        return;
    }

    // Always ensure light theme by removing dark class
    document.documentElement.classList.remove('dark');
}

export function initializeTheme() {
    if (typeof window === 'undefined') {
        return;
    }

    // Always initialize to light theme
    updateTheme('light');
}

const appearance = ref<Appearance>('light');

export function useAppearance() {
    onMounted(() => {
        // Always set to light theme
        appearance.value = 'light';
        updateTheme('light');
    });

    function updateAppearance(value: Appearance) {
        // Always force light theme regardless of input
        appearance.value = 'light';
        updateTheme('light');
    }

    return {
        appearance,
        updateAppearance,
    };
}
