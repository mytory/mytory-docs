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

    // View page: heading counter — smart auto-detect
    const body = document.body;
    const saved = localStorage.getItem('heading-numbers');

    if (saved !== null) {
        // User has explicitly toggled — respect their choice
        if (saved === '1') body.classList.add('heading-numbers');
    } else {
        // Auto-detect: scan h2 headings for existing numbering
        const headings = document.querySelectorAll('.prose h2');
        const numbered = [...headings].filter(h => {
            const text = h.textContent || '';
            return /^\d+[.)]\s/.test(text)         // "1. " or "1) "
                || /^\(\d+\)\s/.test(text)         // "(1) "
                || /^[①②③④⑤⑥⑦⑧⑨⑩]/.test(text);    // "①"
        });
        // If most h2 already have numbers, turn counters OFF
        if (numbered.length > headings.length * 0.5 && headings.length > 0) {
            // Already numbered — keep counters off
        } else {
            // Not numbered — turn counters ON
            body.classList.add('heading-numbers');
        }
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
