// Mytory Docs — Frontend JS
// CodeMirror is loaded inline in edit.php (ES module, CDN)

document.addEventListener('DOMContentLoaded', () => {
    // New file dialog: focus input
    const newFileDialog = document.getElementById('new-file-dialog');
    if (newFileDialog) {
        newFileDialog.addEventListener('close', () => {
            newFileDialog.querySelector('input[name="filename"]').value = '';
        });
    }

    // Delete dialog: bind from list page
    // (handled inline in list.php deleteFile function)

    // View page: heading counter — default OFF, remember in localStorage
    const body = document.body;
    if (localStorage.getItem('heading-numbers') === '1') {
        body.classList.add('heading-numbers');
    }
    document.getElementById('toggle-heading-numbers')?.addEventListener('click', () => {
        const has = body.classList.toggle('heading-numbers');
        localStorage.setItem('heading-numbers', has ? '1' : '0');
    });

    // Any table in view mode: add basic styling
    document.querySelectorAll('.prose table').forEach(t => {
        t.classList.add('w-full', 'border-collapse', 'text-sm');
    });
});

// Search page: keyboard shortcut "/"
document.addEventListener('keydown', (e) => {
    if (e.key === '/' && !e.target.closest('input,textarea,[contenteditable]') && !document.querySelector('.cm-editor')) {
        e.preventDefault();
        document.querySelector('input[name="q"]')?.focus();
    }
});
