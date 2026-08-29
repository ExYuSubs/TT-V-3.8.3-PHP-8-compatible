<!DOCTYPE html>
<html lang="hr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TorrentTrader v3.8.3 — Install Notes</title>
<style>
  :root {
    --bg: #0d1117;
    --bg-panel: #151b23;
    --bg-panel-alt: #1c2530;
    --border: #2a323d;
    --text: #d7dde5;
    --text-dim: #8b96a5;
    --accent: #4fd1c5;
    --accent-soft: rgba(79, 209, 197, 0.12);
    --warn: #f0b429;
    --warn-soft: rgba(240, 180, 41, 0.1);
    --danger: #f2545b;
    --danger-soft: rgba(242, 84, 91, 0.1);
    --code-bg: #0a0e14;
    --radius: 10px;
  }

  * { box-sizing: border-box; }

  body {
    margin: 0;
    background: radial-gradient(circle at top left, #14202a, var(--bg) 55%);
    color: var(--text);
    font-family: "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    line-height: 1.6;
    padding: 40px 16px 80px;
  }

  .wrap {
    max-width: 880px;
    margin: 0 auto;
  }

  header {
    margin-bottom: 36px;
    text-align: center;
  }

  header h1 {
    font-size: 1.9rem;
    margin: 0 0 6px;
    letter-spacing: 0.5px;
    background: linear-gradient(90deg, var(--accent), #7fe3d8);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
  }

  header .subtitle {
    color: var(--text-dim);
    font-size: 0.9rem;
  }

  .badge {
    display: inline-block;
    margin-top: 10px;
    padding: 4px 12px;
    border-radius: 999px;
    background: var(--accent-soft);
    color: var(--accent);
    font-size: 0.78rem;
    font-weight: 600;
    letter-spacing: 0.4px;
  }

  section {
    background: var(--bg-panel);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 24px 26px;
    margin-bottom: 22px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.25);
  }

  section h2 {
    margin-top: 0;
    font-size: 1.15rem;
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--accent);
  }

  section h2 .num {
    background: var(--accent-soft);
    color: var(--accent);
    border-radius: 6px;
    width: 28px;
    height: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    font-weight: 700;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 14px;
    font-size: 0.92rem;
  }

  th, td {
    padding: 10px 12px;
    text-align: left;
    border-bottom: 1px solid var(--border);
  }

  th {
    color: var(--text-dim);
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
  }

  tr:last-child td { border-bottom: none; }

  tr:hover td { background: var(--bg-panel-alt); }

  code, .path {
    font-family: "Fira Code", Consolas, monospace;
    background: var(--code-bg);
    border: 1px solid var(--border);
    color: var(--accent);
    padding: 2px 7px;
    border-radius: 5px;
    font-size: 0.88rem;
  }

  pre {
    background: var(--code-bg);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 14px 16px;
    overflow-x: auto;
    font-family: "Fira Code", Consolas, monospace;
    font-size: 0.85rem;
    color: #9fe6dc;
    margin: 12px 0 0;
  }

  ol.steps { counter-reset: step; list-style: none; padding: 0; margin: 0; }

  ol.steps > li {
    counter-increment: step;
    position: relative;
    padding: 14px 16px 14px 52px;
    margin-bottom: 10px;
    background: var(--bg-panel-alt);
    border: 1px solid var(--border);
    border-radius: 8px;
  }

  ol.steps > li::before {
    content: counter(step);
    position: absolute;
    left: 14px;
    top: 14px;
    width: 24px;
    height: 24px;
    border-radius: 6px;
    background: var(--accent);
    color: #06201c;
    font-weight: 700;
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  ol.steps ul, ol.steps ol.sub {
    margin: 10px 0 0;
    padding-left: 20px;
  }

  .note {
    margin-top: 10px;
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 0.88rem;
  }

  .note.warn {
    background: var(--warn-soft);
    border-left: 3px solid var(--warn);
    color: #f5d689;
  }

  .note.danger {
    background: var(--danger-soft);
    border-left: 3px solid var(--danger);
    color: #f7a6ab;
  }

  .legend {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    margin-top: 16px;
    font-size: 0.85rem;
  }

  .legend span {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: var(--text-dim);
  }

  .dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
  }

  .dot.warn { background: var(--warn); }
  .dot.danger { background: var(--danger); }
  .dot.accent { background: var(--accent); }

  footer {
    text-align: center;
    color: var(--text-dim);
    font-size: 0.82rem;
    margin-top: 30px;
  }

  footer a { color: var(--accent); text-decoration: none; }
  footer a:hover { text-decoration: underline; }
</style>
</head>
<body>
<div class="wrap">

  <header>
    <h1>TorrentTrader v3.8.3</h1>
    <div class="subtitle">Install Notes &mdash; Fresh Install Only</div>
    <span class="badge">Zadnja izmjena: 28. kolovoza 2026.</span>
  </header>

  <section>
    <h2><span class="num">R</span> Zahtjevi sustava</h2>
    <table>
      <thead>
        <tr><th>Komponenta</th><th>Napomena</th></tr>
      </thead>
      <tbody>
        <tr><td>PHP</td><td><code>8.0</code></td></tr>
        <tr><td>MySQL</td><td>5+ &mdash; testirano na <code>10.11.19-MariaDB-AlmaLinux 8.10</code></td></tr>
        <tr><td>register_globals</td><td><span class="note danger" style="display:inline;padding:2px 8px;">Ne preporučuje se uključeno</span></td></tr>
        <tr><td>Windows okruženje</td><td>Radi, ali se ne preporučuje &mdash; putanje (paths) možda treba prilagoditi</td></tr>
        <tr><td>MySQL strict mode</td><td>Preporučeno isključiti prije uvoza baze</td></tr>
      </tbody>
    </table>
  </section>

  <section>
    <h2><span class="num">1</span> Kopiranje datoteka</h2>
    <p>Kopirajte <strong>sve</strong> datoteke na svoj webserver.</p>
  </section>

  <section>
    <h2><span class="num">2</span> Uvoz baze podataka</h2>
    <p>Uvezite putem phpMyAdmina datoteku:</p>
    <pre>tt.v.3.8.3.by.tt.forum.sql</pre>
  </section>

  <section>
    <h2><span class="num">3</span> Konfiguracija MySQL veze</h2>
    <p>Uredite <span class="path">backend/mysql.php</span> prema svojim MySQL podacima za spajanje.</p>
  </section>

  <section>
    <h2><span class="num">4</span> Opća konfiguracija</h2>
    <p>Uredite <span class="path">backend/config.php</span> prema svojim potrebama.</p>
    <div class="note warn">Posebnu pažnju obratite na URL-ove, e-mail adrese i putanje (paths). Ako niste sigurni, koristite <span class="path">check.php</span>.</div>
  </section>

  <section>
    <h2><span class="num">5</span> Uklonite sigurnosnu liniju</h2>
    <p>Iz <span class="path">config.php</span> uklonite sljedeći redak:</p>
    <pre>die("You didn't edit your config correctly.");</pre>
    <div class="note danger">Ovaj redak <strong>MORATE</strong> ukloniti, inače instalacija neće raditi.</div>
  </section>

  <section>
    <h2><span class="num">6</span> CHMOD dozvole</h2>
    <table>
      <thead>
        <tr><th>CHMOD</th><th>Putanja / Datoteka</th></tr>
      </thead>
      <tbody>
        <tr><td><code>777</code></td><td class="path">cache/</td></tr>
        <tr><td><code>777</code></td><td class="path">cache/get_row_count/</td></tr>
        <tr><td><code>777</code></td><td class="path">cache/queries/</td></tr>
        <tr><td><code>777</code></td><td class="path">cache/diskcache/</td></tr>
        <tr><td><code>777</code></td><td class="path">backups/</td></tr>
        <tr><td><code>777</code></td><td class="path">uploads/</td></tr>
        <tr><td><code>777</code></td><td class="path">uploads/images/</td></tr>
        <tr><td><code>777</code></td><td class="path">import/</td></tr>
        <tr><td><code>600</code></td><td class="path">censor.txt</td></tr>
        <tr><td><code>440</code></td><td class="path">backend/config.php <em>(tek nakon uređivanja)</em></td></tr>
      </tbody>
    </table>
    <div class="legend">
      <span><span class="dot accent"></span> 777 &mdash; puni pristup (cache/upload direktoriji)</span>
      <span><span class="dot warn"></span> 600 &mdash; samo vlasnik čita/piše</span>
      <span><span class="dot danger"></span> 440 &mdash; samo za čitanje, nakon konfiguracije</span>
    </div>
  </section>

  <section>
    <h2><span class="num">7</span> Provjera instalacije</h2>
    <p>Pokrenite <span class="path">check.php</span> iz preglednika kako biste provjerili je li sve ispravno konfigurirano.</p>
    <div class="note warn">Skripta je dizajnirana za UNIX sustave &mdash; na Windowsu putanje možda neće biti ispravno prikazane.</div>
  </section>

  <section>
    <h2><span class="num">8</span> Registracija administratora</h2>
    <p>Registrirajte se kao novi korisnik na stranici. <strong>Prvi registrirani korisnik automatski postaje administrator.</strong></p>
  </section>

  <section>
    <h2><span class="num">9</span> Uklanjanje check.php</h2>
    <p>Ako <span class="path">check.php</span> i dalje postoji, obrišite ga ili preimenujte.</p>
    <div class="note warn">Dok god postoji, na naslovnici stranice prikazivat će se upozorenje.</div>
  </section>

  <section>
    <h2><span class="num">10</span> Sigurnost backup sustava</h2>
    <p>Zaštitite <span class="path">backup-database.php</span> i direktorij <span class="path">backups/</span> putem <code>.htaccess</code> / <code>.htpasswd</code> autentifikacije.</p>
  </section>

  <section>
    <h2><span class="num">11</span> API ključevi za scraping (IMDb, YouTube, TMDB)</h2>
    <p>Da bi <span class="path">torrent-details.php</span> ispravno dohvaćao podatke, uredite <span class="path">backend/TTIMDB.php</span>:</p>
    <ol class="sub">
      <li>
        Linija <code>#16</code>:
        <pre>private $_nodes = ['https://www.omdbapi.com/?apikey=your_own_omdbapi_key&i=%s'];</pre>
      </li>
      <li>
        Linije <code>#213</code> i <code>#214</code>:
        <pre>$tmdbApiKey    = "your_own_tmdbapi_key";
$youtubeApiKey = "your_own_youtube_key";</pre>
      </li>
    </ol>
  </section>

  <section>
    <h2><span class="num">12</span> Zaštita od bota (Cloudflare Turnstile)</h2>
    <p>Ugrađena je Cloudflare zaštita za prijavu, registraciju i oporavak računa, ali <strong>nije aktivirana</strong> po defaultu.</p>
    <p>Za aktivaciju unesite vlastite ključeve u <span class="path">config.php</span>, linije <code>#33</code> i <code>#34</code>:</p>
    <pre>$site_config['CLOUDSITEKEY'] = 'YOUR_OWN_CLOUDSITE_KEY';
$site_config['CLOUDSECRET'] = 'YOUR_OWN_CLOUDSECRET_KEY';</pre>
  </section>

  <section>
    <h2><span class="num">13</span> Alternativna verzija torrent-details.php</h2>
    <p>Ako <span class="path">torrent-details.php</span> ne radi kako treba, preimenujte trenutnu datoteku, a zatim <span class="path">torrent-details-5.php</span> preimenujte u <span class="path">torrent-details.php</span>.</p>
  </section>

  <footer>
    Za sva pitanja posjetite <a href="https://torrenttrader.uk" target="_blank" rel="noopener">torrenttrader.uk</a>
  </footer>

</div>
</body>
</html>
