# MOPA Timetable System - Queue Worker Setup

## Problema

Kai paspaudžiate "Generuoti tvarkaraštį" mygtuką, **job'as patenka į queue (`jobs` lentelę DB), bet nevykdomas automatiškai**.

Laravel naudoja **queue** sistemą - tai reiškia, kad **reikia paleisti queue worker procesą**, kuris klausysis ir vykdys job'us.

## Sprendimas

### Windows

**Būdas 1: Automatinis launcher (rekomenduojamas)**

1. Du kartus spustelėkite:
   ```
   c:\xampp\htdocs\mopa\start-queue-worker.bat
   ```

2. Palikite šį langą atidarytą, kol dirbate su sistema

3. Dabar "Generuoti" mygtukas veiks!

**Būdas 2: Rankiniu būdu PowerShell**

```powershell
cd c:\xampp\htdocs\mopa
php artisan queue:work --queue=timetables --tries=1 --timeout=120
```

Palikite terminalą atidarytą.

### Linux/Mac

```bash
cd /path/to/mopa
php artisan queue:work --queue=timetables --tries=1 --timeout=120
```

## Kas vyksta?

1. **Paspaudžiate "Generuoti"** → Job'as sukuriamas `jobs` lentelėje
2. **Queue worker** paima job'ą iš lentelės
3. **Job'as vykdomas** → Kviečia Python solver
4. **Rezultatai išsaugomi** → UI atsinaujina

## Production Setup

Production serveryje turėtumėte naudoti **Supervisor** arba **systemd** paleisti queue worker kaip background procesą:

### Supervisor konfigūracija

```ini
[program:mopa-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/mopa/artisan queue:work --queue=timetables --tries=1 --timeout=120 --sleep=3 --max-jobs=1000
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/mopa/storage/logs/worker.log
stopwaitsecs=3600
```

### Systemd konfigūracija

```ini
[Unit]
Description=MOPA Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /path/to/mopa/artisan queue:work --queue=timetables --tries=1 --timeout=120

[Install]
WantedBy=multi-user.target
```

## Patikrinti Queue būseną

```powershell
# Kiek job'ų laukia eilėje?
php artisan tinker --execute="echo DB::table('jobs')->count();"

# Paskutiniai 5 job'ai
php artisan tinker --execute="DB::table('jobs')->latest('id')->limit(5)->get();"

# Išvalyti visus job'us (atsargiai!)
php artisan tinker --execute="DB::table('jobs')->delete();"
```

## Troubleshooting

**Problema**: Worker sustoja su klaida

**Sprendimas**: Patikrinkite `storage/logs/laravel.log` arba paleiskite su `--verbose`:
```powershell
php artisan queue:work --queue=timetables --verbose
```

**Problema**: Python solver neveikia

**Sprendimas**: Patikrinkite ar įdiegtas `ortools`:
```powershell
python -m pip install ortools
```

**Problema**: Worker vykdo senas job'as

**Sprendimas**: Išvalykite queue:
```powershell
php artisan queue:flush
```
