# Deployment

## Hazirlik
- PHP 8.3+
- MySQL 8+
- OpenSSL, mbstring, curl, pdo_mysql, fileinfo, intl, zip

## Uretim Adimlari
1. Backend dosyalarini sunucuya yukle.
2. `.env` dosyasini olustur.
3. `APP_ENV=production`
4. `APP_URL=https://chat.hpanel.com.tr`
5. `php artisan key:generate`
6. `php artisan migrate --force`
7. `php artisan db:seed --class=RealmSeeder --force`
8. `php artisan config:cache`
9. `php artisan route:cache`
10. `php artisan optimize`

## Onemli Env Alanlari
- `MOTONLINE_SERVER_SHARED_KEY`
- `MOTONLINE_REALM_GOKBORU_HOST`
- `MOTONLINE_REALM_GOKBORU_PORT`
- `MOTONLINE_REALM_GOKBORU_MAP`
- `MOTONLINE_REALM_BOZKURT_HOST`
- `MOTONLINE_REALM_BOZKURT_PORT`
- `MOTONLINE_REALM_BOZKURT_MAP`
