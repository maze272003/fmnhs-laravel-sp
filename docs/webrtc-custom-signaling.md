# Custom WebSocket Signaling for Conference WebRTC

## Architecture

- Laravel HTTP app serves conference pages and generates a signed signaling token per authorized actor.
- A custom PHP WebSocket server (Workerman) handles signaling only, with in-memory room membership.
- Frontend uses the native `WebSocket` API (no PusherJS / Reverb) and WebRTC APIs.

Core files:

- `app/Realtime/ConferenceSignalingServer.php`
- `app/Console/Commands/ConferenceSignalServeCommand.php`
- `app/Support/ConferenceSignalingServerSupervisor.php`
- `app/Support/ConferenceSignalingToken.php`
- `app/Http/Controllers/ConferenceAccessController.php`
- `resources/views/conference/room.blade.php`

## Startup / Shutdown Flow

1. Run `php artisan serve`.
2. `AppServiceProvider` listens for `CommandStarting` on `serve`.
3. Supervisor spawns `php artisan conference:signal-serve --host=... --port=...`.
4. Browser connects to `ws://.../ws/conference` (or `wss://...`).
5. On Laravel server stop (`Ctrl+C`), `CommandFinished` stops the child signaling process.

## Environment Config

```env
CONFERENCE_SIGNALING_ENABLED=true
CONFERENCE_SIGNALING_HOST=
CONFERENCE_SIGNALING_BIND_HOST=127.0.0.1
CONFERENCE_SIGNALING_PORT=6001
CONFERENCE_SIGNALING_SCHEME=
CONFERENCE_SIGNALING_PATH=/ws/conference
CONFERENCE_SIGNALING_TOKEN_TTL=7200
```

`CONFERENCE_SIGNALING_HOST` is optional and used as frontend connect host override.

## JSON Signaling Contract

Client -> Server:

- `join`
```json
{ "type": "join", "roomId": "conference-12", "token": "<signed-token>" }
```

- `offer`
```json
{ "type": "offer", "to": "student-5", "payload": { "type": "offer", "sdp": "..." } }
```

- `answer`
```json
{ "type": "answer", "to": "teacher-2", "payload": { "type": "answer", "sdp": "..." } }
```

- `ice-candidate`
```json
{ "type": "ice-candidate", "to": "student-5", "payload": { "candidate": "...", "sdpMid": "...", "sdpMLineIndex": 0 } }
```

- `chat`
```json
{ "type": "chat", "message": "Hello" }
```

- `meeting-ended`
```json
{ "type": "meeting-ended" }
```

Server -> Client events:

- `joined` (self + room participants)
- `peer-joined`
- `peer-left`
- `offer`
- `answer`
- `ice-candidate`
- `chat`
- `meeting-ended`
- `error`

## WebRTC Integration Notes

The room UI now enforces this sequence:

1. Request media permissions (`getUserMedia`).
2. Connect WebSocket signaling and send `join`.
3. Create `RTCPeerConnection` with configured ICE servers.
4. `addTrack` local tracks before offer/answer flow.
5. Exchange `offer` / `answer` through signaling.
6. Buffer ICE candidates until remote description is set, then flush.
7. Render remote stream in `ontrack`.

Teacher is the primary initiator (calls all peers). Students do not initiate toward teacher to avoid glare.
