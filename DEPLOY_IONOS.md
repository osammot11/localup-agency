# Deploy su VPS IONOS: adv.localupitalia.com

Questa guida presuppone una VPS Linux con Nginx, PHP-FPM 8.4, Composer, MySQL/MariaDB e Certbot già installati. I comandi applicativi vanno eseguiti come utente `deploy`; `sudo` serve solo per database, permessi e configurazione di sistema. La guida non esegue alcuna operazione sulla VPS.

Il repository è `https://github.com/osammot11/localup-agency.git`. Il percorso scelto qui è `/var/www/adv.localupitalia.com`. Ogni applicazione sulla VPS deve avere il proprio database, file `.env`, virtual host Nginx e cron Laravel.

## 1. Controlli preliminari

Accedere via SSH come `deploy`. Verificare che il record A di `adv.localupitalia.com` punti all'IP della VPS; se esiste un record AAAA, deve puntare anch'esso alla VPS. Le porte 80 e 443 devono essere raggiungibili.

```bash
dig +short A adv.localupitalia.com
dig +short AAAA adv.localupitalia.com
php8.4 -v
php8.4 -m | grep -Ei 'curl|mbstring|openssl|pdo_mysql|fileinfo|xml'
composer --version
git --version
sudo certbot --version
ls -l /run/php/*8.4*.sock
```

Controllare il nome dell'utente/gruppo del pool PHP-FPM 8.4 che servirà questa app. I comandi seguenti usano `www-data`: sostituirlo ovunque se il pool usa un altro gruppo. Controllare anche che nessun virtual host esistente dichiari già `server_name adv.localupitalia.com`.

```bash
sudo grep -R -E '^(user|group|listen)\s*=' /etc/php/8.4/fpm/pool.d/
sudo nginx -T 2>/dev/null | grep -n 'server_name adv.localupitalia.com' || true
```

## 2. Database dedicato

Per questa installazione si consiglia MySQL/MariaDB, con database e utente separati dalle altre app. Aprire la console SQL con `sudo mysql` oppure con l'accesso amministrativo già in uso sulla VPS. Sostituire la password segnaposto con una password lunga e univoca, conservata nel password manager.

```sql
CREATE DATABASE localup_adv CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'localup_adv'@'127.0.0.1' IDENTIFIED BY 'SOSTITUIRE_CON_PASSWORD_UNIVOCA';
GRANT ALL PRIVILEGES ON localup_adv.* TO 'localup_adv'@'127.0.0.1';
```

La password non va inserita nella riga di comando shell o nel repository. La connessione si può verificare con `mysql -h 127.0.0.1 -u localup_adv -p localup_adv`, che chiederà la password in modo interattivo.

**Alternativa SQLite:** se si preferisce mantenere il motore usato in locale, saltare la creazione MySQL e impostare `DB_CONNECTION=sqlite` e `DB_DATABASE=/var/www/adv.localupitalia.com/database/database.sqlite` nel `.env`. Dopo il clone, creare il file con `touch database/database.sqlite`, dare a `database/` proprietario `deploy` e gruppo PHP-FPM (`sudo chown deploy:www-data database database/database.sqlite`), rendere la directory scrivibile dal gruppo (`chmod 2775 database`) e il file leggibile/scrivibile solo da `deploy` e dal gruppo (`chmod 660 database/database.sqlite`). La directory deve consentire la creazione dei file journal/WAL. Il file SQLite resta escluso da Git e va incluso nei backup. La guida seguente usa MySQL/MariaDB.

## 3. Checkout e configurazione privata

```bash
sudo install -d -o deploy -g deploy /var/www/adv.localupitalia.com
git clone --branch main https://github.com/osammot11/localup-agency.git /var/www/adv.localupitalia.com
cd /var/www/adv.localupitalia.com
cp .env.example .env
```

Modificare `.env` con un editor direttamente sulla VPS. Impostare almeno queste voci; lasciare `APP_KEY` vuota solo fino al comando `key:generate` del passo successivo.

```dotenv
APP_NAME="Local Up ADV"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://adv.localupitalia.com
APP_LOCALE=it
APP_FALLBACK_LOCALE=it

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=localup_adv
DB_USERNAME=localup_adv
DB_PASSWORD=SOSTITUIRE_CON_PASSWORD_UNIVOCA

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=database

META_PIXEL_ID=1612527090508674
META_CAPI_ACCESS_TOKEN=SOSTITUIRE_CON_TOKEN_META
META_GRAPH_VERSION=v26.0
META_TEST_EVENT_CODE=
```

Il token Meta CAPI va ottenuto nel pannello Meta e inserito solo nel `.env` della VPS. `META_TEST_EVENT_CODE` deve restare vuoto in produzione. Google Sheets si configura in seguito dal pannello admin: le credenziali vengono cifrate nel database con `APP_KEY`.

La `.env` non è tracciata da Git. Deve essere leggibile da `deploy` e dall'utente PHP-FPM, ma non da altri utenti:

```bash
sudo chown deploy:www-data .env
chmod 640 .env
```

## 4. Dipendenze, permessi e migrazioni

Da `/var/www/adv.localupitalia.com`, come `deploy`:

```bash
umask 0002
sudo chown -R deploy:www-data storage bootstrap/cache
sudo find storage bootstrap/cache -type d -exec chmod 2775 {} +
sudo find storage bootstrap/cache -type f -exec chmod 664 {} +
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
php8.4 artisan key:generate --force --no-interaction
php8.4 artisan migrate --force --no-interaction
php8.4 artisan optimize --no-interaction
php8.4 artisan migrate:status --no-interaction
```

Verificare prima che `composer` usi PHP 8.4 anche da CLI; se usa un'altra versione, eseguire il file Composer con `php8.4` secondo la sua installazione sulla VPS. `key:generate` si esegue **solo per un database nuovo**: cambiare `APP_KEY` in seguito rende illeggibili i dati cifrati, comprese le credenziali Google Sheets e gli eventi Meta memorizzati. Se si trasferisce il database SQLite locale, trasferire in modo privato anche la sua `APP_KEY` originale e **saltare** `key:generate`; una copia del solo file SQLite non basta per decifrare quei dati.

Il progetto usa CSS e JavaScript già presenti in `public/`; le pagine attuali non richiamano Vite. Non è necessario eseguire `npm install` o `npm run build` per questo deploy.

## 5. Account amministratore

Questo comando è intenzionalmente interattivo e chiede nome, email e una password di almeno 12 caratteri:

```bash
php8.4 artisan admin:create
```

L'accesso sarà su `/admin/login`. Il database nuovo è vuoto: gli utenti admin e i contatti del database SQLite locale **non vengono trasferiti** dal push GitHub. Se occorre conservarli, preparare una migrazione dati separata prima di aprire il sito al pubblico.

## 6. Virtual host Nginx dedicato

Creare `/etc/nginx/sites-available/adv.localupitalia.com` con `sudoedit`. Usare il socket PHP-FPM 8.4 trovato al passo 1 se diverso da quello dell'esempio. La `root` deve terminare in `/public`, mai nella radice del repository.

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name adv.localupitalia.com;
    root /var/www/adv.localupitalia.com/public;

    index index.php;
    charset utf-8;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ \.php$ { return 404; }
    location ~ /\.(?!well-known).* { deny all; }
}
```

Attivare solo questo virtual host e testare la configurazione prima del reload. Il reload di Nginx è globale, ma applica la nuova configurazione in modo graduale anche alle altre app.

```bash
sudo ln -s /etc/nginx/sites-available/adv.localupitalia.com /etc/nginx/sites-enabled/adv.localupitalia.com
sudo nginx -t
sudo systemctl reload nginx
```

Se la distribuzione non usa `sites-available`/`sites-enabled`, inserire il blocco in un file dedicato nella directory `conf.d` già inclusa da Nginx. Non modificare i virtual host delle altre app.

## 7. HTTPS e simulazione del rinnovo del solo dominio

Se il certificato per questo dominio non esiste, crearlo **una volta**. Questa è un'emissione reale, distinta dalla simulazione del rinnovo. Il plugin Nginx di Certbot aggiornerà il virtual host del dominio:

```bash
sudo certbot --nginx -d adv.localupitalia.com --cert-name adv.localupitalia.com --redirect
```

Se un certificato esiste già, saltare l'emissione. In entrambi i casi, controllare con `sudo certbot certificates` il valore **Certificate Name** e verificare che nella sua riga **Domains** compaia soltanto `adv.localupitalia.com`. Se Certbot ha assegnato un nome diverso, usare quel nome esatto nel comando successivo. Se il certificato contiene anche altri domini, la simulazione riguarderebbe quel certificato intero: creare prima un certificato dedicato al solo sottodominio.

La simulazione richiesta è:

```bash
sudo certbot renew --cert-name adv.localupitalia.com --dry-run
```

`--cert-name` limita la prova a quel certificato; `--dry-run` usa l'ambiente di test e non sostituisce il certificato di produzione. **Non** usare `sudo certbot renew --dry-run` senza `--cert-name` su questa VPS: selezionerebbe tutti i certificati gestiti da Certbot. Il timer automatico di Certbot già presente sulla VPS resta invariato. Verificare poi `sudo nginx -t` e `curl -I https://adv.localupitalia.com/up`.

## 8. Scheduler Laravel di questa sola app

Il progetto esegue ogni cinque minuti il recupero degli eventi Meta CAPI in attesa e la sincronizzazione Google Sheets. Aggiungere **una sola riga** al crontab di `deploy` con `crontab -e`, senza sostituire le righe delle altre applicazioni. Usare il percorso assoluto di `php8.4` restituito da `command -v php8.4` se diverso da `/usr/bin/php8.4`.

```cron
* * * * * cd /var/www/adv.localupitalia.com && /usr/bin/php8.4 artisan schedule:run >> /dev/null 2>&1
```

Controllare con `crontab -l` e `php8.4 artisan schedule:list --no-interaction`. Il codice attuale non mette lavori in coda, quindi non richiede ancora un processo `queue:work`; la connessione `database` è già predisposta se in futuro verranno aggiunti job.

## 9. Verifica finale e aggiornamenti

Aprire `https://adv.localupitalia.com/`, le cinque landing e `/admin/login`. Controllare `/up`, il login, l'invio di un contatto autorizzato, il suo arrivo nell'admin, gli eventi Meta e, dopo la configurazione in admin, la sincronizzazione Google Sheets. In caso di errore consultare `storage/logs/laravel.log` e il log errori Nginx del server.

Per aggiornare il sito, prima salvare un backup del **solo** database `localup_adv` e del file `.env` in una posizione privata. Poi, come `deploy`:

```bash
cd /var/www/adv.localupitalia.com
umask 0002
git pull --ff-only origin main
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
php8.4 artisan migrate --force --no-interaction
php8.4 artisan optimize --no-interaction
```

Non ripetere `key:generate`, non usare `migrate:fresh` e non lanciare la suite di test contro il database di produzione. Gli asset attuali sono già nel repository. Se una futura modifica introducesse `@vite`, aggiungere allora una build frontend al processo di deploy.

## Riferimenti ufficiali

- [Laravel 13: deployment](https://laravel.com/framework/docs/deployment)
- [Laravel 13: scheduler](https://laravel.com/framework/docs/scheduling)
- [Laravel 13: database](https://laravel.com/framework/docs/database/config)
- [Nginx: test e reload](https://nginx.org/en/docs/switches.html)
- [Certbot: rinnovo mirato e dry run](https://eff-certbot.readthedocs.io/en/stable/using.html#renewal)
