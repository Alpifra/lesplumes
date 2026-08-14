import { ref } from 'vue';

// Shared open/close state for the mobile navigation drawer. Module-level ref
// so the burger button (AppTopBar) and the drawer itself (AppSidebar) — which
// are siblings, not parent/child — read and write the same state.
const isOpen = ref(false);

export function useMobileNav() {
    const open = () => { isOpen.value = true; };
    const close = () => { isOpen.value = false; };
    const toggle = () => { isOpen.value = !isOpen.value; };

    return { isOpen, open, close, toggle };
}
