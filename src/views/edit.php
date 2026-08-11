<!-- Edit: CodeMirror 6 editor -->

<style>
.editor-container { height: calc(100vh - 180px); min-height: 400px; }
.cm-editor { height: 100%; }
.cm-editor .cm-scroller { font-family: 'D2Coding', 'Fira Code', 'JetBrains Mono', monospace; font-size: 14px; line-height: 1.7; }
.editor-status { display: flex; gap: 1rem; font-size: 0.75rem; color: #999; padding: 4px 0; }
</style>

<div id="editor-container" class="editor-container border dark:border-gray-700 rounded overflow-hidden"></div>

<div class="editor-status mt-2">
    <span id="save-status">ready</span>
    <span id="backup-status"></span>
</div>

<textarea id="initial-content" class="hidden"><?= htmlspecialchars($content) ?></textarea>

<script type="module">
import {EditorView, basicSetup} from "https://esm.sh/codemirror@6.0.1";
import {markdown} from "https://esm.sh/@codemirror/lang-markdown@6.3.0";
import {oneDark} from "https://esm.sh/@codemirror/theme-one-dark@6.1.2";

const initialContent = document.getElementById('initial-content').value;
const saveUrl = '/save/<?= rawurlencode($parsed['root_name']) ?>/<?= PathParser::urlPath($parsed['relative_path'] . '/' . $parsed['file']) ?>';
const backupUrl = '/backup/<?= rawurlencode($parsed['root_name']) ?>/<?= PathParser::urlPath($parsed['relative_path'] . '/' . $parsed['file']) ?>';

const editor = new EditorView({
    doc: initialContent,
    extensions: [
        basicSetup,
        markdown(),
        oneDark,
        EditorView.updateListener.of(() => {
            // Mark dirty
            document.getElementById('save-status').dataset.dirty = '1';
        }),
        EditorView.theme({
            "&": {backgroundColor: "rgb(41,41,41)", color: "#eee"},
            ".cm-gutters": {backgroundColor: "rgb(31,31,31)", color: "#888", border: "none"},
            ".cm-activeLineGutter": {backgroundColor: "rgb(35,35,35)"},
            ".cm-activeLine": {backgroundColor: "rgba(255,255,255,0.05)"},
            ".cm-cursor": {borderLeftColor: "#fff"},
            ".cm-selectionBackground": {backgroundColor: "rgba(100,150,255,0.3)"},
            "&.cm-focused .cm-selectionBackground": {backgroundColor: "rgba(100,150,255,0.4)"},
            ".cm-matchingBracket": {backgroundColor: "rgba(255,255,255,0.15)", outline: "1px solid #888"},
        }),
        EditorView.lineWrapping,
    ],
    parent: document.getElementById('editor-container'),
});

let currentFilemtime = <?= filemtime($realFile) ?>;
let prevContent = initialContent;
let saveTimeout = null;
let backupInterval = null;

const statusEl = document.getElementById('save-status');
const backupStatusEl = document.getElementById('backup-status');

function formatTime() {
    return new Date().toLocaleTimeString('ko-KR', {hour12: false});
}

async function save() {
    const content = editor.state.doc.toString();
    if (content === prevContent) {
        statusEl.textContent = 'saved';
        return;
    }
    prevContent = content;
    
    try {
        const resp = await fetch(saveUrl, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({content, current_filemtime: currentFilemtime}),
        });
        const data = await resp.json();
        
        if (data.code === 'success') {
            currentFilemtime = data.real_filemtime;
            statusEl.textContent = 'saved · ' + formatTime();
            statusEl.style.color = '#6b6';
            statusEl.dataset.dirty = '0';
        } else if (data.code === 'file_changed') {
            currentFilemtime = parseInt(data.real_filemtime) + 1;
            statusEl.textContent = '⚠ modified externally · ' + formatTime();
            statusEl.style.color = '#eb4';
            if (confirm('File was changed externally. Reload?')) {
                location.reload();
            }
        } else {
            statusEl.textContent = '✗ ' + (data.msg || 'save failed');
            statusEl.style.color = '#e66';
        }
    } catch(e) {
        statusEl.textContent = '✗ network error';
        statusEl.style.color = '#e66';
    }
}

function scheduleSave() {
    clearTimeout(saveTimeout);
    saveTimeout = setTimeout(save, 1000);
}

async function backup() {
    const content = editor.state.doc.toString();
    try {
        const resp = await fetch(backupUrl, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({content}),
        });
        const data = await resp.json();
        if (data.code === 'success') {
            backupStatusEl.textContent = 'backup · ' + formatTime();
        } else {
            backupStatusEl.textContent = 'backup failed';
        }
    } catch(e) {
        backupStatusEl.textContent = 'backup failed';
    }
}

// Auto-save on changes (debounced)
EditorView.updateListener.of(() => scheduleSave());

// Trigger save on every change (the listener above handles it)
editor.dispatch = new Proxy(editor.dispatch, {
    apply(target, thisArg, args) {
        const result = Reflect.apply(target, thisArg, args);
        scheduleSave();
        return result;
    }
});

// Initial backup + every 5 minutes
backup();
backupInterval = setInterval(backup, 5 * 60 * 1000);

// Keyboard shortcuts
editor.dom.addEventListener('keydown', (e) => {
    // Ctrl/Cmd+S: force save
    if ((e.metaKey || e.ctrlKey) && e.key === 's') {
        e.preventDefault();
        save();
    }
});

// Resize editor on window resize
function resizeEditor() {
    const navHeight = document.querySelector('nav')?.offsetHeight || 0;
    const footerHeight = document.querySelector('footer')?.offsetHeight || 0;
    const statusHeight = document.querySelector('.editor-status')?.offsetHeight || 0;
    const container = document.getElementById('editor-container');
    container.style.height = (window.innerHeight - navHeight - footerHeight - statusHeight - 40) + 'px';
}
window.addEventListener('resize', resizeEditor);
resizeEditor();
</script>
