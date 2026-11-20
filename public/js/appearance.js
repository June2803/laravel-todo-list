(() => {
    const storageKey = 'todo.appearance';
    const defaults = {size: 16, color: '#195693'};
    let saved;
    try {
        saved = JSON.parse(localStorage.getItem(storageKey));
    } catch {
        // Default settings also work when browser storage is unavailable.
    }

    const settings = {
        size: Number.isInteger(saved?.size) && saved.size >= 14 && saved.size <= 24 ? saved.size : defaults.size,
        color: typeof saved?.color === 'string' && /^#[0-9a-f]{6}$/i.test(saved.color) ? saved.color : defaults.color,
    };

    function apply() {
        const rgb = settings.color.slice(1).match(/../g).map(hex => {
            const channel = parseInt(hex, 16) / 255;
            return channel <= 0.04045 ? channel / 12.92 : ((channel + 0.055) / 1.055) ** 2.4;
        });
        const luminance = 0.2126 * rgb[0] + 0.7152 * rgb[1] + 0.0722 * rgb[2];
        const light = luminance > 0.179;
        const root = document.documentElement.style;
        root.fontSize = settings.size + 'px';
        root.setProperty('--accent', settings.color);
        root.setProperty('--on-accent', light ? '#000' : '#fff');
        root.setProperty('--hover-mix', light ? '#fff' : '#000');
        root.setProperty('--accent-ink', luminance > 0.14 ? 'color-mix(in srgb, var(--accent) 42%, #101820)' : settings.color);
    }

    apply();
    document.addEventListener('DOMContentLoaded', () => {
        const dialog = document.querySelector('#appearance-dialog');
        const size = dialog.querySelector('#appearance-size');
        const color = dialog.querySelector('#appearance-color');
        const value = dialog.querySelector('#size-value');

        function refreshControls() {
            size.value = settings.size;
            color.value = settings.color;
            value.value = settings.size + ' px';
        }

        function update() {
            apply();
            refreshControls();
            try {
                localStorage.setItem(storageKey, JSON.stringify(settings));
            } catch {
                // The chosen appearance still applies to the current page.
            }
        }

        refreshControls();
        const opener = document.querySelector('#appearance-open');
        opener.addEventListener('click', () => dialog.showModal());
        dialog.addEventListener('close', () => opener.focus());
        size.addEventListener('input', () => {
            settings.size = size.valueAsNumber;
            update();
        });
        color.addEventListener('input', () => {
            settings.color = color.value;
            update();
        });
        dialog.querySelector('[data-appearance-reset]').addEventListener('click', () => {
            Object.assign(settings, defaults);
            update();
        });
    });
})();
