document.addEventListener('DOMContentLoaded', function () {

    /*
     * ============================================================
     * SCEditor - TorrentTrader
     * Advanced BBCode Editor
     * ============================================================
     */


    /*
     * ------------------------------------------------------------
     * SCEditor layout + Light/Dark theme
     * ------------------------------------------------------------
     */

    var sceditorThemeFix = document.createElement('style');

    sceditorThemeFix.textContent = `

        /* ========================================================
         * SCEDITOR WIDTH
         * ======================================================== */

        .sceditor-container {
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
        }

        .sceditor-container iframe {
            width: 100% !important;
            box-sizing: border-box !important;
        }

        textarea.sceditor-bbcode {
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
        }


        /* ========================================================
         * DARK THEME - CONTAINER
         * ======================================================== */

        :root[data-theme="dark"] .sceditor-container {
            background: #121f38 !important;
            border-color: rgba(255,255,255,0.12) !important;
        }


        /* ========================================================
         * DARK THEME - TOOLBAR
         * ======================================================== */

        :root[data-theme="dark"] .sceditor-toolbar {
            background: #0d1830 !important;
            border-color: rgba(255,255,255,0.12) !important;
        }


        /*
         * VAŽNO:
         *
         * Ne diramo originalne SCEditor ikone.
         * Nema filtera.
         * Nema invert().
         * Nema promjene njihovih boja.
         */

        :root[data-theme="dark"] .sceditor-button {
            background-color: transparent !important;
            border-color: transparent !important;
        }


        /*
         * Originalne ikone ostaju potpuno netaknute.
         */

        :root[data-theme="dark"] .sceditor-button > div {
            opacity: 1 !important;
            filter: none !important;
        }


        /*
         * Hover
         *
         * Samo pozadina dugmeta se mijenja.
         * Ikona ostaje originalna.
         */

        :root[data-theme="dark"] .sceditor-button:hover {
            background-color: rgba(240,184,106,0.12) !important;
            border-color: rgba(240,184,106,0.25) !important;
        }

        :root[data-theme="dark"] .sceditor-button:hover > div {
            filter: none !important;
        }


        /*
         * Active
         */

        :root[data-theme="dark"] .sceditor-button.active {
            background-color: rgba(240,184,106,0.16) !important;
            border-color: rgba(240,184,106,0.30) !important;
        }

        :root[data-theme="dark"] .sceditor-button.active > div {
            filter: none !important;
        }


        /*
         * Disabled
         */

        :root[data-theme="dark"] .sceditor-button.disabled {
            opacity: 0.45 !important;
        }


        /* ========================================================
         * DARK THEME - TOOLBAR GROUPS
         * ======================================================== */

        :root[data-theme="dark"] .sceditor-group {
            background: transparent !important;
            border-color: rgba(255,255,255,0.10) !important;
        }


        /* ========================================================
         * DARK THEME - DROPDOWNS
         * ======================================================== */

        :root[data-theme="dark"] .sceditor-dropdown {
            background: #121f38 !important;
            color: #e7edf7 !important;
            border-color: rgba(255,255,255,0.14) !important;
        }

        :root[data-theme="dark"] .sceditor-dropdown label,
        :root[data-theme="dark"] .sceditor-dropdown a,
        :root[data-theme="dark"] .sceditor-dropdown button {
            color: #e7edf7 !important;
        }

        :root[data-theme="dark"] .sceditor-dropdown input,
        :root[data-theme="dark"] .sceditor-dropdown select,
        :root[data-theme="dark"] .sceditor-dropdown textarea {
            background: #16274a !important;
            color: #e7edf7 !important;
            border-color: rgba(255,255,255,0.14) !important;
        }


        /* ========================================================
         * DARK THEME - EDITOR
         * ======================================================== */

        :root[data-theme="dark"] .sceditor-container iframe {
            background: #121f38 !important;
        }


        /* ========================================================
         * LIGHT THEME
         * ======================================================== */

        :root[data-theme="light"] .sceditor-button > div {
            opacity: 1 !important;
            filter: none !important;
        }

        :root[data-theme="light"] .sceditor-button:hover > div {
            filter: none !important;
        }

    `;

    document.head.appendChild(sceditorThemeFix);


    /*
     * ------------------------------------------------------------
     * Theme detection
     * ------------------------------------------------------------
     */

    var themeLink = document.getElementById('sceditor-theme');


    function isDarkTheme() {

        var theme = document.documentElement.getAttribute('data-theme');

        if (theme === 'dark') {
            return true;
        }

        if (theme === 'light') {
            return false;
        }

        return window.matchMedia &&
               window.matchMedia('(prefers-color-scheme: dark)').matches;
    }


    /*
     * ------------------------------------------------------------
     * Update editor theme
     * ------------------------------------------------------------
     */

    function updateEditorTheme(instance) {

        var dark = isDarkTheme();


        /*
         * Toolbar theme
         */

        if (themeLink) {

            themeLink.href = dark
                ? '/assets/sceditor/minified/themes/defaultdark.min.css'
                : '/assets/sceditor/minified/themes/default.min.css';

        }


        /*
         * Editor content
         */

        if (instance) {

            if (dark) {

                instance.css(`

                    html,
                    body {
                        background: #121f38 !important;
                        color: #e7edf7 !important;
                    }

                    body {
                        font-family: Arial, Helvetica, sans-serif;
                        font-size: 14px;
                    }

                    p,
                    div,
                    span,
                    li,
                    td,
                    th,
                    ul,
                    ol {
                        color: #e7edf7 !important;
                    }

                    a {
                        color: #7fb2f0 !important;
                    }

                    a:hover {
                        color: #a8cdf7 !important;
                    }

                    blockquote {
                        border-left: 4px solid #f0b86a !important;
                        background: rgba(240,184,106,0.10) !important;
                        color: #e7edf7 !important;
                        padding: 8px 12px !important;
                    }

                    code,
                    pre {
                        color: #e7edf7 !important;
                        background: #0f1d33 !important;
                    }

                `);

            } else {

                instance.css(`

                    html,
                    body {
                        background: #ffffff !important;
                        color: #222222 !important;
                    }

                    body {
                        font-family: Arial, Helvetica, sans-serif;
                        font-size: 14px;
                    }

                    p,
                    div,
                    span,
                    li,
                    td,
                    th,
                    ul,
                    ol {
                        color: #222222 !important;
                    }

                    a {
                        color: #2d6ca2 !important;
                    }

                    a:hover {
                        color: #1f4f78 !important;
                    }

                    blockquote {
                        border-left: 4px solid #cccccc !important;
                        background: #f5f5f5 !important;
                        color: #222222 !important;
                        padding: 8px 12px !important;
                    }

                    code,
                    pre {
                        color: #222222 !important;
                        background: #f5f5f5 !important;
                    }

                `);

            }

        }

    }


    /*
     * ------------------------------------------------------------
     * Create SCEditor
     * ------------------------------------------------------------
     */

    document.querySelectorAll('textarea.sceditor-bbcode').forEach(function (el) {

        /*
         * Prevent double initialization
         */

        if (el.dataset.sceditorInitialized === '1') {
            return;
        }


        sceditor.create(el, {

            /*
             * BBCode
             */

            format: 'bbcode',


            /*
             * Content CSS
             */

            style:
                '/assets/sceditor/minified/themes/content/default.min.css',


            /*
             * Advanced toolbar
             */

            toolbar:

                'bold,italic,underline,strike|' +

                'font,size,color|' +

                'left,center,right,justify|' +

                'bulletlist,orderedlist|' +

                'link,unlink,image,email|' +

                'quote,code,horizontalrule|' +

                'subscript,superscript,removeformat|' +

                'undo,redo|' +

                'emoticon|' +

                'youtube,table|' +

                'maximize,source',


            /*
             * Smilies
             */

            emoticonsRoot: '/',

            emoticons: {

                dropdown: {

                    ':)': 'images/smilies/smile.gif',

                    ':(': 'images/smilies/sad.gif',

                    ':D': 'images/smilies/grin.gif',

                    ':P': 'images/smilies/tongue.gif',

                    ':]': 'images/smilies/smiley.gif',

                    ':cool:': 'images/smilies/cool.gif',

                    ':geek:': 'images/smilies/geek.gif',

                    ':-/': 'images/smilies/confused.gif',

                    ':lol:': 'images/smilies/lol.gif',

                    ':w00t:': 'images/smilies/w00t.gif',

                    ':ilv:': 'images/smilies/in-love.gif',

                    ':cry:': 'images/smilies/cry.gif',

                    ':|': 'images/smilies/noexpression.gif',

                    ':happy:': 'images/smilies/happy.gif',

                    ':evil:': 'images/smilies/evil.gif',

                    ':angry:': 'images/smilies/angry.gif',

                    ':wave:': 'images/smilies/wave.gif',

                    ':warn:': 'images/smilies/warn.gif',

                    ':helpme:': 'images/smilies/help-me.gif',

                    ':bad:': 'images/smilies/bad.gif',

                    ':love:': 'images/smilies/love.gif',

                    ':idea:': 'images/smilies/idea.gif',

                    ':bomb:': 'images/smilies/bomb.gif',

                    ':!:': 'images/smilies/important.gif'

                }

            }

        });


        /*
         * Get SCEditor instance
         */

        var instance = sceditor.instance(el);


        /*
         * Initial theme
         */

        updateEditorTheme(instance);


        /*
         * Store instance
         */

        el._ttSceditor = instance;


        /*
         * Mark initialized
         */

        el.dataset.sceditorInitialized = '1';

    });


    /*
     * ------------------------------------------------------------
     * Existing TorrentTrader Light/Dark theme watcher
     * ------------------------------------------------------------
     */

    if (window.MutationObserver) {

        var themeObserver = new MutationObserver(function (mutations) {

            var changed = false;

            mutations.forEach(function (mutation) {

                if (
                    mutation.type === 'attributes' &&
                    mutation.attributeName === 'data-theme'
                ) {

                    changed = true;

                }

            });


            if (!changed) {
                return;
            }


            document
                .querySelectorAll('textarea.sceditor-bbcode')
                .forEach(function (el) {

                    if (el._ttSceditor) {

                        updateEditorTheme(el._ttSceditor);

                    }

                });

        });


        themeObserver.observe(
            document.documentElement,
            {
                attributes: true,
                attributeFilter: ['data-theme']
            }
        );

    }

});