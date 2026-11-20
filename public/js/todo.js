const addSection = document.querySelector('.add-disclosure');
const addForm = addSection.querySelector('.add-form');
const draftKey = 'todo.createDraft';
const draftFields = [addForm.elements.title, addForm.elements.description, addForm.elements.tags];

// Closing <details> keeps its inputs intact. Tab storage also covers a list reload.
try {
    if (addForm.hasAttribute('data-created')) {
        sessionStorage.removeItem(draftKey);
    } else if (!addSection.open) {
        const draft = JSON.parse(sessionStorage.getItem(draftKey)) || {};
        for (const field of draftFields) {
            if (typeof draft[field.name] === 'string') field.value = draft[field.name];
        }
    }
} catch {
    // The same-page form still works if the browser disables tab storage.
}

addForm.addEventListener('input', () => {
    const draft = Object.fromEntries(draftFields.map(field => [field.name, field.value]));
    try {
        if (Object.values(draft).some(Boolean)) {
            sessionStorage.setItem(draftKey, JSON.stringify(draft));
        } else {
            sessionStorage.removeItem(draftKey);
        }
    } catch {
        // Input values stay in the form when tab storage is unavailable.
    }
});

document.addEventListener('click', event => {
    if (addSection.open && !addSection.contains(event.target)) {
        addSection.open = false;
    }
});

const notice = document.querySelector('.status');
notice?.addEventListener('animationend', () => notice.remove(), {once: true});

for (const item of document.querySelectorAll('.todo-item')) {
    const editButton = item.querySelector('[data-edit]');
    const editor = item.querySelector('.edit-panel');

    function closeEditor() {
        for (const field of editor.querySelectorAll('[data-saved]')) {
            field.value = field.dataset.saved;
            field.removeAttribute('aria-invalid');
            field.removeAttribute('aria-describedby');
        }
        for (const error of editor.querySelectorAll('.error')) {
            error.remove();
        }
        editor.hidden = true;
        editButton.setAttribute('aria-expanded', 'false');
        editButton.focus();
    }

    editButton.addEventListener('click', () => {
        if (!editor.hidden) {
            closeEditor();
            return;
        }
        editor.hidden = false;
        editButton.setAttribute('aria-expanded', 'true');
        editor.elements.title.focus();
    });
    editor.querySelector('[data-cancel]').addEventListener('click', closeEditor);

    item.querySelector('[data-delete]').addEventListener('submit', (event) => {
        if (!window.confirm('確定要刪除這筆待辦嗎？')) {
            event.preventDefault();
        }
    });
}
