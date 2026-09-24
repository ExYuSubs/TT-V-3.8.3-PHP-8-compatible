/*
 * TorrentTrader theme toggle
 * Prebacuje body.dark-mode klasu, pamti izbor u localStorage
 * i mijenja ikonicu dugmeta: zuto sunce (light) / tamniji mjesec (dark)
 */
(function () {
	var STORAGE_KEY = 'tt-theme';

	function getPreferredTheme() {
		var saved = localStorage.getItem(STORAGE_KEY);
		if (saved === 'dark' || saved === 'light') {
			return saved;
		}
		if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
			return 'dark';
		}
		return 'light';
	}

	function applyTheme(theme) {
		document.body.classList.toggle('dark-mode', theme === 'dark');

		var buttons = document.querySelectorAll('[data-theme-toggle]');
		for (var i = 0; i < buttons.length; i++) {
			var btn = buttons[i];
			btn.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
			var icon = btn.querySelector('i');
			if (icon) {
				icon.classList.remove('fa-sun', 'fa-moon');
				icon.classList.add(theme === 'dark' ? 'fa-moon' : 'fa-sun');
			}
		}
	}

	function toggleTheme() {
		var current = document.body.classList.contains('dark-mode') ? 'dark' : 'light';
		var next = current === 'dark' ? 'light' : 'dark';
		localStorage.setItem(STORAGE_KEY, next);
		applyTheme(next);
	}

	/* Primijeni temu sto ranije moguce, da izbjegnemo "flash" pogresne teme */
	applyTheme(getPreferredTheme());

	document.addEventListener('DOMContentLoaded', function () {
		applyTheme(getPreferredTheme());

		var buttons = document.querySelectorAll('[data-theme-toggle]');
		for (var i = 0; i < buttons.length; i++) {
			buttons[i].addEventListener('click', function (e) {
				e.preventDefault();
				toggleTheme();
			});
		}
	});
})();
