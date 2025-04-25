# Aplikácia na verejnom hostovaní

Aplikácia je dostupná na adrese:  
`https://builder.dev-dataset.online/`

**Prihlasovacie údaje:**
- Email: `admin@hcportal.eu`
- Heslo: `hcportal`
# Príručka na lokálne spustenie
V prípade, že chcete projekt rozbehať lokálne, postupujte podľa inštrukcií nižšie.
## Požiadavky

- PHP 8.3  
- MySQL 8  
- Composer

## Inštalácia na Windows (Laragon)
Táto časť je nepovinná. V prípade, že máte potrebné požiadavky spomenuté vyššie, môžte preskočiť na krok `Nastavenie projektu`
1. Stiahnite a nainštalujte Laragon:  
   [Laragon 8.1.0](https://github.com/leokhoa/laragon/releases/download/8.1.0/laragon-wamp.exe)

2. Skontrolujte PHP 8.3 a MySQL 8 v nastaveniach Laragonu.
3. Spustie Laragon a naštartujte služby cez `Start All`
## Nastavenie projektu
Všetky príkazy nižšie je nutné vykonať v koreňovom adresári projektu.  
1. Skopírujte projekt do ľubovoľného priečinka.  
2. Otvorte terminál a prejdite do priečinka:
```bash
    cd C:\cesta\k\projektu
```
3. Skopírujte .env súbor:
```bash
   copy .env.example .env
```
4. Nainštalujte závislosti:
```bash
   composer install --optimize-autoloader --no-dev
```
6. Vygenerujte aplikačný kľúč:
```bash
   php artisan key:generate
```
7. Upraviť .env (príklad):
Názov databázy sa musí zhodovat s hodnotou v `DB_DATABASE`
```bash
    DB_DATABASE=nazov_db
    DB_USERNAME=root
    DB_PASSWORD=
```
8. Spustite migrácie a seedery:
```bash
   php artisan migrate:fresh --seed --force
```
9. Optimalizácia
```bash
   php artisan optimize:clear && php artisan optimize && php artisan config:clear
```
## Spustenie aplikácie
1. Spustite server:
```bash
   php artisan serve
```
2. Otvorte aplikáciu:
```bash
   http://localhost:8000
```
3. Prihlasovacie údaje do aplikácie
- Email: `admin@hcportal.eu`
- Heslo: `hcportal`


## Nastavenie emailov
Tento krok je potrebný len na spojazdnenie posielanie emailov pre pozvanie nových použivateľov.  
1. Vytvorte si účet na [Mailtrap](https://mailtrap.io/).  
2. Po prihlásení choďte na **Email testing** -> **Inboxes** -> **My inbox** -> **Integration** -> **Smtp**.  
3. Skopírujte nastavenia pre SMTP (MAIL_USERNAME a MAIL_PASSWORD).

4. Upravte `.env` súbor a pridajte nasledujúce hodnoty:
```bash
    MAIL_USERNAME=
    MAIL_PASSWORD=
```



