import './bootstrap';

const autoInitToasts = () => {
    const toast = document.querySelector('.toast');
    if (!toast) {
        return;
    }

    // Mostrar con pequeña animación
    requestAnimationFrame(() => {
        toast.classList.add('is-visible');
    });

    const close = () => {
        toast.classList.remove('is-visible');
        setTimeout(() => {
            toast.remove();
        }, 200);
    };

    const closeBtn = toast.querySelector('[data-toast-close]');
    if (closeBtn) {
        closeBtn.addEventListener('click', close);
    }

    setTimeout(close, 4500);
};

const initConversationBuilder = () => {
    const form = document.getElementById('conversation-form');
    if (!form) {
        return;
    }

    const starterSelect = /** @type {HTMLSelectElement | null} */ (document.getElementById('starter_role'));
    const linesContainer = document.getElementById('conversation-lines');
    if (!starterSelect || !linesContainer) {
        return;
    }

    const getLabels = () => {
        const interviewer = linesContainer.getAttribute('data-interviewer-label') || 'Entrevistadora';
        const interviewee = linesContainer.getAttribute('data-interviewee-label') || 'Entrevistada';
        return { interviewer, interviewee };
    };

    const updateLineLabels = () => {
        const { interviewer, interviewee } = getLabels();
        const starter = starterSelect.value === 'interviewee' ? 'interviewee' : 'interviewer';

        const lines = Array.from(linesContainer.querySelectorAll('.conversation-line'));
        lines.forEach((line, index) => {
            const labelEl = line.querySelector('.conversation-label');
            if (!(labelEl instanceof HTMLElement)) return;

            const isStarterTurn = index % 2 === 0;
            const role = isStarterTurn ? starter : starter === 'interviewer' ? 'interviewee' : 'interviewer';
            labelEl.textContent = role === 'interviewer' ? interviewer : interviewee;
        });
    };

    const addLine = () => {
        const index = linesContainer.querySelectorAll('.conversation-line').length;
        const { interviewer, interviewee } = getLabels();
        const starter = starterSelect.value === 'interviewee' ? 'interviewee' : 'interviewer';
        const isStarterTurn = index % 2 === 0;
        const role = isStarterTurn ? starter : starter === 'interviewer' ? 'interviewee' : 'interviewer';
        const labelText = role === 'interviewer' ? interviewer : interviewee;

        const wrapper = document.createElement('div');
        wrapper.className = 'field-group conversation-line';
        wrapper.dataset.index = String(index);

        const label = document.createElement('label');
        label.className = 'conversation-label';
        label.textContent = labelText;

        const textarea = document.createElement('textarea');
        textarea.name = 'lines[]';
        textarea.className = 'conversation-text';
        textarea.placeholder = 'Escribe el turno y presiona Enter para agregar el siguiente...';

        wrapper.appendChild(label);
        wrapper.appendChild(textarea);
        linesContainer.appendChild(wrapper);

        attachLineHandler(textarea);
        textarea.focus();
    };

    const attachLineHandler = (textarea) => {
        textarea.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                // Ctrl+Enter: salto de línea dentro del mismo turno
                if (event.ctrlKey) {
                    return;
                }
                // Enter "normal": crear siguiente turno (uno y uno)
                event.preventDefault();
                if (textarea.value.trim() === '') {
                    return;
                }
                addLine();
            }
        });
    };

    // Inicializar handler para el primer textarea que viene del servidor
    const initialTextarea = linesContainer.querySelector('.conversation-text');
    if (initialTextarea instanceof HTMLTextAreaElement) {
        attachLineHandler(initialTextarea);
    }

    // Reactualizar etiquetas cuando cambie quién inicia
    starterSelect.addEventListener('change', () => {
        updateLineLabels();
    });

    // Asegurar etiquetas correctas en carga inicial
    updateLineLabels();
};

const initConversationEditor = () => {
    const toggleBtn = document.getElementById('toggle-edit-conversation');
    const form = document.getElementById('conversation-edit-form');
    const actions = document.getElementById('conversation-edit-actions');
    const cancelBtn = document.getElementById('cancel-edit-conversation');
    const extraContainer = document.getElementById('conversation-extra-lines');
    const deletesContainer = document.getElementById('conversation-deletes');

    if (!toggleBtn || !form || !actions || !extraContainer || !deletesContainer) {
        return;
    }

    const textareas = Array.from(
        form.querySelectorAll('.conversation-edit-text')
    );

    const setEditing = (editing) => {
        form.dataset.editing = editing ? 'true' : 'false';
        actions.style.display = editing ? 'flex' : 'none';
        toggleBtn.textContent = editing ? 'Bloquear edición' : 'Editar conversación';

        textareas.forEach((el) => {
            if (!(el instanceof HTMLTextAreaElement)) return;
            el.disabled = !editing;
            el.style.borderColor = editing ? 'rgba(148,163,184,0.55)' : 'transparent';
            el.style.background = editing ? 'rgba(255,255,255,0.9)' : 'transparent';
            el.style.cursor = editing ? 'text' : 'default';
        });

        // Tres puntitos solo visibles en modo edición
        menuTriggers.forEach((btn) => {
            if (btn instanceof HTMLElement) {
                btn.style.display = editing ? '' : 'none';
            }
        });
    };

    const getNextRole = (role) =>
        role === 'interviewer' ? 'interviewee' : 'interviewer';

    const createExtraLineElement = (role) => {
        const labelText =
            role === 'interviewer'
                ? extraContainer.getAttribute('data-interviewer-label') || 'Entrevistadora'
                : extraContainer.getAttribute('data-interviewee-label') || 'Entrevistada';

        const wrapper = document.createElement('div');
        wrapper.className = 'field-group conversation-line-extra';

        const label = document.createElement('label');
        label.className = 'conversation-label-extra';
        label.textContent = labelText;

        const textarea = document.createElement('textarea');
        textarea.name = 'extra_lines[]';
        textarea.className = 'conversation-text-extra';
        textarea.placeholder = 'Escribe el turno y presiona Enter para el siguiente...';

        const roleInput = document.createElement('input');
        roleInput.type = 'hidden';
        roleInput.name = 'extra_roles[]';
        roleInput.value = role;

        wrapper.appendChild(label);
        wrapper.appendChild(textarea);
        wrapper.appendChild(roleInput);
        return { wrapper, textarea };
    };

    const createExtraLine = (role, afterMessageId = null) => {
        const { wrapper, textarea } = createExtraLineElement(role);

        if (afterMessageId) {
            const targetWrapper = form.querySelector(
                `.conversation-message-wrapper[data-id="${afterMessageId}"]`
            );
            if (targetWrapper && targetWrapper.parentElement) {
                targetWrapper.parentElement.insertBefore(
                    wrapper,
                    targetWrapper.nextSibling
                );
            } else {
                extraContainer.appendChild(wrapper);
            }
        } else {
            extraContainer.appendChild(wrapper);
        }

        attachEditEnterHandler(textarea);
        textarea.focus();
    };

    const createExtraLineAfterElement = (afterElement, role) => {
        const { wrapper, textarea } = createExtraLineElement(role);
        const parent = afterElement.parentElement;
        if (parent) {
            parent.insertBefore(wrapper, afterElement.nextSibling);
        } else {
            extraContainer.appendChild(wrapper);
        }
        attachEditEnterHandler(textarea);
        textarea.focus();
    };

    const attachEditEnterHandler = (textarea) => {
        textarea.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter') return;
            if (event.ctrlKey) return;

            event.preventDefault();
            if (textarea.value.trim() === '') return;

            const wrapper = textarea.closest('.conversation-message-wrapper') ||
                textarea.closest('.conversation-line-extra');
            const currentRole = textarea.getAttribute('data-role') ||
                wrapper?.querySelector('input[name="extra_roles[]"]')?.value ||
                'interviewer';
            const nextRole = getNextRole(currentRole);

            if (wrapper?.classList.contains('conversation-message-wrapper')) {
                const id = wrapper.getAttribute('data-id');
                createExtraLine(nextRole, id);
            } else if (wrapper?.classList.contains('conversation-line-extra')) {
                createExtraLineAfterElement(wrapper, nextRole);
            } else {
                createExtraLine(nextRole);
            }
        });
    };

    // Enter en modo edición: salto al siguiente turno (una persona tras otra)
    form.querySelectorAll('.conversation-edit-text').forEach((el) => {
        if (el instanceof HTMLTextAreaElement) attachEditEnterHandler(el);
    });

    // Menú de 3 puntitos por mensaje
    const menuTriggers = Array.from(
        form.querySelectorAll('.message-menu-trigger')
    );
    const menus = Array.from(form.querySelectorAll('.message-menu'));

    const closeAllMenus = () => {
        menus.forEach((m) => {
            if (m instanceof HTMLElement) m.style.display = 'none';
        });
    };

    menuTriggers.forEach((btn) => {
        btn.addEventListener('click', (event) => {
            if (form.dataset.editing !== 'true') return;
            event.stopPropagation();
            const id = btn.getAttribute('data-message-id');
            closeAllMenus();
            const menu = form.querySelector(
                `.message-menu[data-message-id="${id}"]`
            );
            if (menu instanceof HTMLElement) {
                menu.style.display =
                    menu.style.display === 'block' ? 'none' : 'block';
            }
        });
    });

    document.addEventListener('click', () => {
        closeAllMenus();
    });

    menus.forEach((menu) => {
        menu.addEventListener('click', (event) => {
            event.stopPropagation();
            const target = event.target;
            if (!(target instanceof HTMLElement)) return;
            const action = target.getAttribute('data-action');
            const messageId = target.getAttribute('data-message-id');
            if (!action || !messageId) return;

            if (action === 'add-interviewer') {
                createExtraLine('interviewer', messageId);
            } else if (action === 'add-interviewee') {
                createExtraLine('interviewee', messageId);
            } else if (action === 'delete-message') {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'messages_to_delete[]';
                hidden.value = messageId;
                deletesContainer.appendChild(hidden);

                const wrapper = form.querySelector(
                    `.conversation-message-wrapper[data-id="${messageId}"]`
                );
                if (wrapper instanceof HTMLElement) {
                    wrapper.style.opacity = '0.4';
                }
            }

            closeAllMenus();
        });
    });

    toggleBtn.addEventListener('click', () => {
        const currentlyEditing = form.dataset.editing === 'true';
        if (currentlyEditing) {
            // Restaurar textos originales y limpiar cambios no guardados
            textareas.forEach((el) => {
                if (!(el instanceof HTMLTextAreaElement)) return;
                const original = el.getAttribute('data-original');
                if (original !== null) {
                    el.value = original;
                }
            });
            extraContainer.innerHTML = '';
            deletesContainer.innerHTML = '';
        }
        setEditing(!currentlyEditing);
    });

    if (cancelBtn) {
        cancelBtn.addEventListener('click', (event) => {
            event.preventDefault();
            textareas.forEach((el) => {
                if (!(el instanceof HTMLTextAreaElement)) return;
                const original = el.getAttribute('data-original');
                if (original !== null) {
                    el.value = original;
                }
            });
            extraContainer.innerHTML = '';
            deletesContainer.innerHTML = '';
            setEditing(false);
        });
    }

    // Iniciar en modo solo lectura
    setEditing(false);
};

const initApp = () => {
    autoInitToasts();
    initConversationBuilder();
    initConversationEditor();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initApp);
} else {
    initApp();
}
