# Deploy su VPS IONOS: adv.localupitalia.com

Questa guida presuppone una VPS Linux con Nginx, PHP-FPM 8.4, Composer, SQLite e Certbot già installati. Il database di questa app sarà **SQLite**, in un file dedicato. I comandi applicativi vanno eseguiti come utente `deploy`; `sudo` serve solo per permessi e configurazione di sistema. La guida non esegue alcuna operazione sulla VPS.

Il repository è `https://github.com/osammot11/localup-agency.git`. Il percorso scelto qui è `/var/www/adv.localupitalia.com`. Ogni applicazione sulla VPS deve avere il proprio database, file `.env`, virtual host Nginx e cron Laravel.

## 1. Controlli preliminari

Accedere via SSH come `deploy`. Verificare che il record A di `adv.localupitalia.com` punti all'IP della VPS; se esiste un record AAAA, deve puntare anch'esso alla VPS. Le porte 80 e 443 devono essere raggiungibili.

```bash
dig +short A adv.localupitalia.com
dig +short AAAA adv.localupitalia.com
php8.4 -v
php8.4 -m | grep -Ei 'curl|mbstring|openssl|pdo_sqlite|sqlite3|fileinfo|xml'
sqlite3 --version
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

## 2. Checkout e file SQLite dedicato

```bash
sudo install -d -o deploy -g deploy /var/www/adv.localupitalia.com
git clone --branch main https://github.com/osammot11/localup-agency.git /var/www/adv.localupitalia.com
cd /var/www/adv.localupitalia.com
```

Per una **nuova installazione**, creare il file database vuoto come `deploy`:

```bash
touch database/database.sqlite
```

Se invece si vogliono conservare contatti e admin del database locale, **non** creare un database vuoto: trasferire una copia coerente del database esistente. Sul computer locale, fermare eventuali scritture durante il trasferimento ed eseguire, dalla radice del progetto:

```bash
umask 077
mkdir -p "$HOME/localup-adv-transfer"
chmod 700 "$HOME/localup-adv-transfer"
sqlite3 database/database.sqlite ".backup '$HOME/localup-adv-transfer/database.sqlite'"
scp "$HOME/localup-adv-transfer/database.sqlite" deploy@IP_DELLA_VPS:/var/www/adv.localupitalia.com/database/database.sqlite
```

La copia SQLite può contenere dati personali e credenziali cifrate: usare solo SSH/SCP, eliminare la copia temporanea locale dopo averne verificato l'importazione e non inserirla in Git. In questo caso bisogna riportare nel `.env` della VPS **la stessa `APP_KEY` del database locale**; non generarne una nuova.

Assegnare al file e alla directory il gruppo usato da PHP-FPM. La directory deve essere scrivibile dal gruppo per consentire a SQLite di creare journal e file WAL; gli altri utenti della VPS non devono poter leggere il database:

```bash
sudo chown deploy:www-data database database/database.sqlite
chmod 2770 database
chmod 660 database/database.sqlite
```

`database/database.sqlite` è escluso da Git. Questo percorso appartiene solo a questa app; non usare il database SQLite di un'altra applicazione.

```bash
sqlite3 database/database.sqlite 'PRAGMA quick_check;'
```

Il risultato atteso è `ok`. Se le altre app PHP usano lo stesso utente/gruppo `www-data`, quel pool condiviso può tecnicamente accedere anche al database di questa app: per un isolamento completo tra applicazioni serve un pool PHP-FPM dedicato con un proprio utente e gruppo.

## 3. Configurazione privata

```bash
cd /var/www/adv.localupitalia.com
cp .env.example .env
```

Modificare `.env` con un editor direttamente sulla VPS. Per un database nuovo lasciare `APP_KEY` vuota fino al comando `key:generate` del passo successivo; per un database importato inserire qui l'`APP_KEY` originale.

```dotenv
APP_NAME="Local Up ADV"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://adv.localupitalia.com
APP_LOCALE=it
APP_FALLBACK_LOCALE=it

DB_CONNECTION=sqlite
DB_DATABASE=/var/www/adv.localupitalia.com/database/database.sqlite

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
```

**Solo con un database nuovo**, generare una chiave applicativa. Con un database importato saltare questo comando: la chiave originale deve già essere nel `.env`.

```bash
php8.4 artisan key:generate --force --no-interaction
```

In entrambi i casi, completare l'installazione:

```bash
php8.4 artisan migrate --force --no-interaction
php8.4 artisan optimize --no-interaction
php8.4 artisan migrate:status --no-interaction
```

La chiave non va mai rigenerata in seguito: Google Sheets e gli eventi Meta memorizzati contengono dati cifrati con essa. Verificare la leggibilità del database con `php8.4 artisan migrate:status --no-interaction`; se SQLite segnala `attempt to write a readonly database`, controllare i permessi **sia** del file **sia** della directory `database/`.

Verificare prima che `composer` usi PHP 8.4 anche da CLI; se usa un'altra versione, eseguire il file Composer con `php8.4` secondo la sua installazione sulla VPS.

Il progetto usa CSS e JavaScript già presenti in `public/`; le pagine attuali non richiamano Vite. Non è necessario eseguire `npm install` o `npm run build` per questo deploy.

## 5. Account amministratore

Questo comando è intenzionalmente interattivo e chiede nome, email e una password di almeno 12 caratteri:

```bash
php8.4 artisan admin:create
```

L'accesso sarà su `/admin/login`. Eseguire `admin:create` **solo per un database nuovo**. Se si è importato il file SQLite locale, gli utenti admin e i contatti sono già presenti: accedere con l'admin esistente. Il push GitHub non include alcun dato del database.

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

Per aggiornare il sito, salvare prima una copia **coerente** del file SQLite con il comando `.backup` di SQLite e una copia del `.env`, in una directory privata fuori dalla root web. Una normale copia del file database mentre l'app scrive può essere incompleta, soprattutto se SQLite usa WAL. Esempio come `deploy`:

```bash
mkdir -p "$HOME/backups/localup-adv"
chmod 700 "$HOME/backups/localup-adv"
sqlite3 /var/www/adv.localupitalia.com/database/database.sqlite ".backup '$HOME/backups/localup-adv/adv-$(date +%F-%H%M%S).sqlite'"
cp /var/www/adv.localupitalia.com/.env "$HOME/backups/localup-adv/env-$(date +%F-%H%M%S)"
chmod 600 "$HOME"/backups/localup-adv/*
```

Poi, come `deploy`:

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
- [SQLite: backup coerenti](https://sqlite.org/backup.html)
- [SQLite: file WAL e permessi](https://www.sqlite.org/wal.html)
- [Nginx: test e reload](https://nginx.org/en/docs/switches.html)
- [Certbot: rinnovo mirato e dry run](https://eff-certbot.readthedocs.io/en/stable/using.html#renewal)
