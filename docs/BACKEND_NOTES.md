# MotOnline Backend Notes

## Konum
- Backend kaynak dizini: `E:\MotOnlineBackend`
- Hedef domain: `https://chat.hpanel.com.tr/`

## Stack
- Laravel 13
- Sanctum token auth
- Character, Realm ve GameSession modelleri

## Mimari Ozeti
- Client register/login icin Laravel ile konusur.
- Client karakter secince backend tek kullanimlik game session token uretir.
- Dedicated server bu tokeni `server/session/consume` ile backend'e sorar.
- Karakter verisi authoritative olarak backend'den server'a gelir.

## Realm Mantigi
- Realm tablosu map, host, port ve faction bazli calisir.
- Bugun `31.223.124.154:7777` aktif olsa da ileride her map farkli host/port alabilir.
- Client sabit IP bilmek zorunda kalmaz; realm datasi backend'den gelir.
