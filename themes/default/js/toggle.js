/*

#================================#
#       TorrentTrader 3.8.3      #
#  http://torrenttrader.uk       #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by MicroMonkey, #
#       Coco, Botanicar          #
#       Theme: default           #
#================================#
 */

(function () {
	var STORAGE_KEY = 'tt-theme';
	var root = document.documentElement;

	function currentTheme() {
		var attr = root.getAttribute('data-theme');
		if (attr === 'dark' || attr === 'light') return attr;
		var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
		return prefersDark ? 'dark' : 'light';
	}

	function applyTheme(theme) {
		root.setAttribute('data-theme', theme);
		var buttons = document.querySelectorAll('[data-theme-toggle]');
		for (var i = 0; i < buttons.length; i++) {
			buttons[i].setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
		}
	}

	// Primijeni odmah (inline skripta u <head> je vec postavila data-theme
	// ako postoji sacuvan izbor - ovo samo uskladi aria-pressed i pokrije
	// slucaj kad korisnik prvi put dolazi na sajt).
	applyTheme(currentTheme());

	document.addEventListener('DOMContentLoaded', function () {
		applyTheme(currentTheme());

		var buttons = document.querySelectorAll('[data-theme-toggle]');
		for (var i = 0; i < buttons.length; i++) {
			buttons[i].addEventListener('click', function (e) {
				e.preventDefault();
				var next = currentTheme() === 'dark' ? 'light' : 'dark';
				try { localStorage.setItem(STORAGE_KEY, next); } catch (err) {}
				applyTheme(next);
			});
		}
	});
})();
