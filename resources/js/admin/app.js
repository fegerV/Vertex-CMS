const storageKey = 'vertexcms.admin.theme';

function applyTheme(theme) {
    const root = document.documentElement;
    root.dataset.theme = theme;

    document.querySelectorAll('[data-theme-label]').forEach((node) => {
        node.textContent = theme === 'dark' ? 'Тёмная тема' : 'Светлая тема';
    });

    document.querySelectorAll('[data-theme-icon]').forEach((node) => {
        node.innerHTML = theme === 'dark'
            ? '<path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 1 0 21.752 15.002Z" />'
            : '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1.5m0 15V21m9-9h-1.5M4.5 12H3m15.364 6.364-1.06-1.06M6.696 6.696 5.636 5.636m12.728 0-1.06 1.06M6.696 17.304l-1.06 1.06M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />';
    });
}

function preferredTheme() {
    const stored = window.localStorage.getItem(storageKey);
    if (stored === 'light' || stored === 'dark') {
        return stored;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

function toggleTheme() {
    const nextTheme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
    window.localStorage.setItem(storageKey, nextTheme);
    applyTheme(nextTheme);
}

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');

    if (!sidebar || !backdrop) {
        return;
    }

    sidebar.classList.toggle('-translate-x-full');
    backdrop.classList.toggle('hidden');
}

document.addEventListener('DOMContentLoaded', () => {
    applyTheme(preferredTheme());

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', toggleTheme);
    });

    document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
        button.addEventListener('click', toggleSidebar);
    });

    document.querySelectorAll('[data-sidebar-backdrop]').forEach((button) => {
        button.addEventListener('click', toggleSidebar);
    });
});

