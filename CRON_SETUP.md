# Cron Job Setup Guide

## Cron Job Command

Weka hii kwenye crontab yako:

```bash
* * * * * /usr/local/bin/php /home/opticedg/public_html/artisan schedule:run >> /dev/null 2>&1
```

**AU kwa PHP 8.3 maalum:**

```bash
* * * * * /usr/local/bin/ea-php83 /home/opticedg/public_html/artisan schedule:run >> /dev/null 2>&1
```

## Jinsi ya ku-setup:

### Njia 1: Kupitia cPanel

1. Fungua **cPanel**
2. Nenda kwenye **Cron Jobs**
3. Chagua **Standard (cPanel v1.0)**
4. Weka:
   - **Minute**: `*`
   - **Hour**: `*`
   - **Day**: `*`
   - **Month**: `*`
   - **Weekday**: `*`
   - **Command**: 
     ```
     /usr/local/bin/php /home/opticedg/public_html/artisan schedule:run >> /dev/null 2>&1
     ```
5. Bofya **Add New Cron Job**

### Njia 2: Kupitia SSH/Terminal

1. Ingia kwenye server kupitia SSH
2. Andika:
   ```bash
   crontab -e
   ```
3. Ongeza line hii mwishoni:
   ```
   * * * * * /usr/local/bin/php /home/opticedg/public_html/artisan schedule:run >> /dev/null 2>&1
   ```
4. Hifadhi na toka (Esc, :wq kwa vim)

### Njia 3: Kwa kutumia path kamili ya PHP 8.3

Ikiwa unahitaji kutumia PHP 8.3 maalum:

```bash
* * * * * /usr/local/bin/ea-php83 /home/opticedg/public_html/artisan schedule:run >> /dev/null 2>&1
```

## Kuthibitisha Cron Job Inafanya Kazi

1. Fungua log file (kama ume-setup):
   ```bash
   tail -f /home/opticedg/public_html/storage/logs/laravel.log
   ```

2. Au test manually:
   ```bash
   /usr/local/bin/php /home/opticedg/public_html/artisan schedule:run
   ```

3. Au test command ya opening balance moja kwa moja:
   ```bash
   /usr/local/bin/php /home/opticedg/public_html/artisan payment-options:update-opening-balance
   ```

## Maelezo:

- `* * * * *` - Inaendesha kila dakika (Laravel scheduler ina-check internally kama task inapaswa kuendesha)
- `/usr/local/bin/php` - Path ya PHP binary
- `/home/opticedg/public_html/artisan` - Path ya artisan command
- `schedule:run` - Laravel command inayocheck scheduled tasks
- `>> /dev/null 2>&1` - Ina-hide output (unaweza kuweka log file badala yake)

## Kama ungependa kuona output:

Badilisha command kuwa:
```bash
* * * * * /usr/local/bin/php /home/opticedg/public_html/artisan schedule:run >> /home/opticedg/public_html/storage/logs/cron.log 2>&1
```

## Scheduled Tasks Zinazofanya Kazi:

1. **Update Opening Balance** - Kila siku saa 6:00 PM (18:00)
   - Command: `payment-options:update-opening-balance`
   - Ina-update opening balance ya kila payment option kuwa sawa na current balance

## Kuthibitisha Task Inaendesha:

Baada ya ku-setup cron job, subiri hadi saa 6:00 PM au test manually:

```bash
/usr/local/bin/php /home/opticedg/public_html/artisan payment-options:update-opening-balance
```

Opening balance zote zitakuwa updated!
