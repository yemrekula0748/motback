# API Contract

## Public / Client
- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout`
- `GET /api/v1/me`
- `PATCH /api/v1/me/faction`
- `GET /api/v1/public/realms`
- `GET /api/v1/characters`
- `POST /api/v1/characters`
- `GET /api/v1/characters/{character}`
- `DELETE /api/v1/characters/{character}`
- `POST /api/v1/game/session`

## Server Only
- `POST /api/v1/server/session/consume`
- `PATCH /api/v1/server/characters/{character}/progress`

## Response Shape
```json
{
  "success": true,
  "data": {}
}
```

## Error Shape
```json
{
  "success": false,
  "error": {
    "code": "STRING_CODE",
    "message": "Human readable message",
    "fields": {}
  }
}
```
