# Security

## Hedef
- Hileye acik client mantigindan kacinmak
- Login, session ve karakter akisini authoritative hale getirmek
- Horizontal scaling icin realm bazli yonlendirme yapisini korumak

## Uygulananlar
- Auth icin Sanctum
- Login/register/game-session/server endpointleri icin rate limit
- Tek kullanimlik game session token
- Dedicated server icin `X-Server-Key`
- API tarafinda standart JSON error cevabi
- Karakter sahiplik kontrolu
- Faction alaninda one-way lock

## Production Onerileri
- HTTPS zorunlu olsun
- DB kullanicisi minimum yetkili olsun
- `APP_DEBUG=false`
- Rate limit loglari ve reverse proxy loglari saklansin
- WAF ve fail2ban benzeri katman dusunulsun
- Server key kod deposunda degil `.env` icinde tutulsun
- Periyodik token temizligi ve audit log tablolari eklenebilsin
