// Theme toggle script: toggles 'dark-theme' on documentElement and saves preference.
(function(){
  const STORAGE_KEY = 'site-theme';
  const CLASS = 'dark-theme';
  function applyTheme(theme){
    if(theme === 'dark'){
      document.documentElement.classList.add(CLASS);
    } else {
      document.documentElement.classList.remove(CLASS);
    }
  }
  function toggleTheme(){
    const isDark = document.documentElement.classList.toggle(CLASS);
    const newTheme = isDark ? 'dark' : 'light';
    try { localStorage.setItem(STORAGE_KEY, newTheme); } catch(e){}
    updateToggleButton(isDark);
  }
  function updateToggleButton(isDark){
    const btn = document.querySelector('[data-theme-toggle]');
    if(!btn) return;
    // change icon classes if using Font Awesome 5/6
    if(isDark){
      btn.innerHTML = '<i class=\"fa fa-sun\" aria-hidden=\"true\"></i>';
      btn.setAttribute('aria-pressed','true');
      btn.title = 'Light';
    } else {
      btn.innerHTML = '<i class=\"fa fa-moon\" aria-hidden=\"true\"></i>';
      btn.setAttribute('aria-pressed','false');
      btn.title = 'Dark';
    }
  }
  // Init: read preference
  const saved = (function(){
    try { return localStorage.getItem(STORAGE_KEY); } catch(e){ return null; }
  })();
  if(saved){
    applyTheme(saved);
  } else {
    // respect system preference if no saved value
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    applyTheme(prefersDark ? 'dark' : 'light');
  }
  // Wire up toggle buttons
  document.addEventListener('DOMContentLoaded', function(){
    const btn = document.querySelector('[data-theme-toggle]');
    if(btn){
      btn.addEventListener('click', function(e){
        e.preventDefault();
        toggleTheme();
      });
      // initialize icon
      updateToggleButton(document.documentElement.classList.contains('dark-theme'));
    }
  });
})();
