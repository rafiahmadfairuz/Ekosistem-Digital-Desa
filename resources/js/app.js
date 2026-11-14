//
import '@fontsource-variable/instrument-sans';

document.querySelectorAll('[data-password-toggle]').forEach((toggleButton) => {
    if (!(toggleButton instanceof HTMLButtonElement)) return;

    const passwordField = document.getElementById(toggleButton.dataset.passwordToggle ?? '');
    if (!(passwordField instanceof HTMLInputElement)) return;

    toggleButton.addEventListener('click', () => {
        const isPasswordVisible = passwordField.type === 'text';
        passwordField.type = isPasswordVisible ? 'password' : 'text';
        toggleButton.textContent = isPasswordVisible ? 'Lihat' : 'Sembunyikan';
        toggleButton.setAttribute('aria-pressed', String(!isPasswordVisible));
    });
});

const cartAddedToast = document.querySelector('[data-cart-added-toast]');

const showCartToast = (title, description, success = true) => {
    if (!(cartAddedToast instanceof HTMLElement)) return;

    const titleElement = cartAddedToast.querySelector('[data-cart-added-title]');
    const descriptionElement = cartAddedToast.querySelector('[data-cart-added-description]');
    const checkElement = cartAddedToast.querySelector('[data-cart-added-check]');
    if (titleElement) titleElement.textContent = title;
    if (descriptionElement) descriptionElement.textContent = description;
    if (checkElement) {
        checkElement.textContent = success ? '✓' : '!';
        checkElement.classList.toggle('bg-success-bg', success);
        checkElement.classList.toggle('text-success-ink', success);
        checkElement.classList.toggle('bg-danger-bg', !success);
        checkElement.classList.toggle('text-danger-ink', !success);
    }

    cartAddedToast.hidden = false;
    cartAddedToast.removeAttribute('data-cart-added-animated');
    void cartAddedToast.offsetWidth;
    cartAddedToast.setAttribute('data-cart-added-animated', '');
};

cartAddedToast?.querySelectorAll('[data-cart-added-close]').forEach((button) => {
    button.addEventListener('click', () => {
        cartAddedToast.hidden = true;
        cartAddedToast.removeAttribute('data-cart-added-animated');
    });
});

document.querySelectorAll('form[data-cart-add-form]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!(form instanceof HTMLFormElement) || form.dataset.submitting === 'true') return;

        const submitButton = form.querySelector('button[type="submit"]');
        form.dataset.submitting = 'true';
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.setAttribute('aria-busy', 'true');
            const spinner = document.createElement('span');
            spinner.className = 'ml-2 inline-block size-4 animate-spin rounded-full border-2 border-current border-r-transparent';
            spinner.setAttribute('aria-hidden', 'true');
            submitButton.append(spinner);
        }

        try {
            const response = await fetch(form.action, {
                method: form.method || 'POST',
                body: new FormData(form),
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });
            const result = await response.json().catch(() => ({}));

            if (!response.ok) {
                const validationMessage = Object.values(result.errors ?? {}).flat()[0];
                throw new Error(validationMessage || result.message || 'Produk belum dapat ditambahkan. Periksa jumlah dan stok, lalu coba lagi.');
            }

            showCartToast('Ditambahkan ke keranjang', `${result.product} · ${result.quantity} item`);
        } catch (error) {
            showCartToast('Belum dapat ditambahkan', error instanceof Error ? error.message : 'Koneksi bermasalah. Coba lagi.', false);
        } finally {
            form.dataset.submitting = 'false';
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.removeAttribute('aria-busy');
                submitButton.querySelector('.animate-spin')?.remove();
            }
        }
    });
});

const mobileMenu = document.getElementById('mobile-navigation');
const openMobileMenu = document.querySelector('[data-mobile-menu-open]');
const closeMobileMenu = document.querySelector('[data-mobile-menu-close]');

if (mobileMenu instanceof HTMLDialogElement && openMobileMenu instanceof HTMLButtonElement) {
    openMobileMenu.addEventListener('click', () => {
        mobileMenu.showModal();
    });

    closeMobileMenu?.addEventListener('click', () => {
        mobileMenu.close();
    });

    mobileMenu.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            mobileMenu.close();
        });
    });

    mobileMenu.addEventListener('click', (event) => {
        if (event.target === mobileMenu) {
            mobileMenu.close();
        }
    });
}

document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm)) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    });
});

document.querySelectorAll('[data-unsaved-form]').forEach((form) => {
    let hasChanges = false;

    form.addEventListener('input', () => {
        hasChanges = true;
    });

    form.addEventListener('change', () => {
        hasChanges = true;
    });

    form.addEventListener('submit', (event) => {
        hasChanges = false;
        form.dataset.submitting = 'true';

        const submitButton = form.querySelector('[data-submit-button]')
            || form.querySelector('button[type="submit"], button:not([type]), input[type="submit"]');
        const loadingIndicator = form.querySelector('[data-loading-indicator]');
        let loadingStatus = form.querySelector('[data-loading-status]');

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.setAttribute('aria-busy', 'true');
        }

        if (loadingIndicator) {
            loadingIndicator.removeAttribute('hidden');
        } else if (submitButton instanceof HTMLButtonElement) {
            const spinner = document.createElement('span');
            spinner.className = 'inline-block size-4 animate-spin rounded-full border-2 border-current border-r-transparent';
            spinner.setAttribute('aria-hidden', 'true');
            submitButton.append(spinner);
        }

        if (!loadingStatus) {
            loadingStatus = document.createElement('span');
            loadingStatus.className = 'sr-only';
            loadingStatus.dataset.loadingStatus = '';
            loadingStatus.setAttribute('role', 'status');
            loadingStatus.setAttribute('aria-live', 'polite');
            form.append(loadingStatus);
        }

        loadingStatus.textContent = 'Mengirim…';
    });

    window.addEventListener('beforeunload', (event) => {
        if (hasChanges && form.dataset.submitting !== 'true') {
            event.preventDefault();
            event.returnValue = '';
        }
    });
});

window.addEventListener('pageshow', () => {
    document.querySelectorAll('form[data-unsaved-form]').forEach((form) => {
        form.dataset.submitting = 'false';
        const submitButton = form.querySelector('[data-submit-button]')
            || form.querySelector('button[type="submit"], button:not([type]), input[type="submit"]');
        const loadingIndicator = form.querySelector('[data-loading-indicator]');

        if (submitButton) {
            submitButton.disabled = false;
            submitButton.removeAttribute('aria-busy');
            submitButton.querySelector('span.animate-spin[aria-hidden="true"]')?.remove();
        }

        loadingIndicator?.setAttribute('hidden', '');
    });
});

const firstInvalid = document.querySelector('[aria-invalid="true"]');
if (firstInvalid) {
    firstInvalid.focus();
} else {
    document.querySelector('[role="alert"][tabindex="-1"]')?.focus();
}

document.querySelectorAll('[data-config-builder]').forEach((builder) => {
    const list = builder.querySelector('[data-config-list]');
    const template = builder.querySelector('[data-config-template]');
    const addButton = builder.querySelector('[data-config-add]');
    const countMessage = builder.querySelector('[data-config-count]');

    if (!(list instanceof HTMLElement) || !(template instanceof HTMLTemplateElement) || !(addButton instanceof HTMLButtonElement)) {
        return;
    }

    const isFields = builder.dataset.configBuilder === 'fields';
    const itemName = isFields ? 'pertanyaan' : 'dokumen';
    const limit = Number.parseInt(builder.dataset.limit ?? '0', 10);

    const updateRow = (row) => {
        const typeSelect = row.querySelector('[data-field-type]');
        if (!(typeSelect instanceof HTMLSelectElement)) return;

        const isSelect = typeSelect.value === 'select';
        const hasLengthLimit = ['text', 'textarea'].includes(typeSelect.value);
        const optionsControl = row.querySelector('[data-options-control]');
        const maxControl = row.querySelector('[data-max-control]');

        if (optionsControl instanceof HTMLElement) {
            optionsControl.classList.toggle('hidden', !isSelect);
            optionsControl.querySelectorAll('textarea').forEach((input) => { input.disabled = !isSelect; });
        }

        if (maxControl instanceof HTMLElement) {
            maxControl.classList.toggle('hidden', !hasLengthLimit);
            maxControl.querySelectorAll('input').forEach((input) => { input.disabled = !hasLengthLimit; });
        }
    };

    const refresh = () => {
        const count = list.querySelectorAll('[data-config-row]').length;
        countMessage.textContent = `Ditambahkan ${count} ${itemName} · maksimal ${limit}`;
        addButton.disabled = count >= limit;
        addButton.setAttribute('aria-disabled', count >= limit ? 'true' : 'false');
    };

    list.querySelectorAll('[data-config-row]').forEach((row) => {
        updateRow(row);
        row.querySelector('[data-field-type]')?.addEventListener('change', () => updateRow(row));
    });

    addButton.addEventListener('click', () => {
        const count = list.querySelectorAll('[data-config-row]').length;
        if (count >= limit) return;

        const index = Number.parseInt(builder.dataset.nextIndex ?? '0', 10);
        builder.dataset.nextIndex = String(index + 1);
        const html = template.innerHTML.replaceAll('__INDEX__', String(index));
        list.insertAdjacentHTML('beforeend', html);
        const newRow = list.lastElementChild;
        if (newRow instanceof HTMLElement) {
            updateRow(newRow);
            newRow.querySelector('[data-field-type]')?.addEventListener('change', () => updateRow(newRow));
            newRow.querySelector('input:not([type="hidden"])')?.focus();
        }
        refresh();
    });

    list.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof Element)) return;

        const removeButton = target.closest('[data-config-remove]');
        if (!(removeButton instanceof HTMLButtonElement)) return;

        const row = removeButton.closest('[data-config-row]');
        if (!row) return;
        const nextRow = row.nextElementSibling ?? row.previousElementSibling;
        row.remove();
        refresh();

        if (nextRow instanceof HTMLElement) {
            nextRow.querySelector('input:not([type="hidden"])')?.focus();
        } else {
            addButton.focus();
        }
    });

    refresh();
});
