# Sport API — Frontend Contract

Contract for the React (Vite) frontend. Source of truth: the Laravel API in this repo.
Machine-readable spec: `docs/openapi.yaml` (viewable at `/docs`). Manual testing: `docs/postman_collection.json`.

## 1. Setup

| Item | Value |
|---|---|
| Base URL (dev) | `http://localhost:8000/api` -> `VITE_API_URL` |
| Frontend origin (dev) | `http://localhost:5173` (must equal backend `FRONTEND_URL`) |
| Auth | Sanctum Personal Access Token stored in an **HttpOnly cookie** `api_token` |
| Token in JS? | Never. Do not store anything in `localStorage`. |

```ts
// src/lib/api.ts
import axios from 'axios';

export const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  withCredentials: true, // required so the browser sends/accepts the cookie
  headers: {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest', // required on POST/PUT/PATCH/DELETE when logged in
  },
});

api.interceptors.response.use(
  (res) => res,
  (err) => {
    if (err.response?.status === 401) {
      // clear auth state in your store and redirect to /login
    }
    return Promise.reject(err);
  },
);
```

Rules:
- Every request: `withCredentials: true`.
- Every POST/DELETE: header `X-Requested-With: XMLHttpRequest`, otherwise the API returns **403**.
- Session lifetime = 7 days (token expiry = cookie lifetime). After that, requests return 401.
- Login state is unknown until you call `GET /me`. Call it on app start.

## 2. Response envelope

Every response from our controllers uses this shape:

```ts
interface ApiResponse<T> {
  success: boolean;
  message: string;
  data: T; // null on most errors; field errors on 422
}
```

| Status | Meaning | `data` | Frontend action |
|---|---|---|---|
| 200 / 201 | Success | payload | use `data` |
| 401 | Not logged in / bad credentials / expired | `null` | clear auth, go to login |
| 403 | Missing `X-Requested-With` header | `null` | fix the client (a bug, not a user error) |
| 404 | Team or favorite not found | `null` | show "not found" |
| 409 | Team already in favorites | `null` | show friendly message |
| 422 | Validation error | `{ [field]: string[] }` | show messages under the fields |
| 502 | TheSportsDB unavailable | `null` | show "try again later" |

Caveat: unknown URLs (404) and wrong HTTP methods (405) return Laravel's default JSON `{ "message": "..." }` without `success`/`data`. Read `err.response?.status` first and don't assume the envelope for those.

## 3. Types

IDs from TheSportsDB are **strings** (`"133604"`). Do not convert to numbers.

```ts
export interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at: string | null;
  created_at: string;
  updated_at: string;
}

export interface Sport {
  idSport: string;
  strSport: string;          // use as ?sport= for /leagues
  strSportThumb?: string;
}

export interface League {
  idLeague: string;
  strLeague: string;
  strSport: string;
  strBadge: string | null;   // logo; null when the free tier has none -> show placeholder
}

export interface Team {      // raw TheSportsDB fields (main ones)
  idTeam: string;
  strTeam: string;
  strTeamShort?: string | null;
  strBadge: string | null;
  strLeague?: string;
  idLeague?: string;
  strSport?: string;
  strCountry?: string | null;
  strStadium?: string | null;
  strLocation?: string | null;
  intFormedYear?: string | null;
  strDescriptionEN?: string | null;
}

export interface Match {     // shaped by our API (clean snake_case)
  id: string | null;
  event: string | null;
  league: string | null;
  home_team: string | null;
  away_team: string | null;
  home_score: string | null;
  away_score: string | null;
  match_time_wib: string | null; // "2026-01-01 19:00:00" (already Asia/Jakarta, no timezone suffix)
  venue: string | null;
}

export interface Standing {  // raw TheSportsDB row (main fields)
  intRank: string;
  idTeam: string;
  strTeam: string;
  strBadge: string | null;
  strSeason: string;
  strForm?: string | null;
  intPlayed?: string;
  intWin?: string;
  intDraw?: string;
  intLoss?: string;
  intGoalDifference?: string;
  intPoints?: string;
}

export interface Favorite {
  id: number;                // favorite record id (use for DELETE)
  team_id: string;           // TheSportsDB idTeam
  team_name: string;
  team_badge: string | null;
  created_at: string;
}
```

## 4. Endpoints

`Auth` column: **public** or **cookie** (must be logged in).

### Auth

| Method | Path | Auth | Body | Success `data` |
|---|---|---|---|---|
| POST | `/register` | public | `{ name, email, password, password_confirmation }` | `{ user: User }` + sets `api_token` cookie (201) |
| POST | `/login` | public | `{ email, password }` | `{ user: User }` + sets `api_token` cookie |
| GET | `/me` | cookie | - | `User` |
| POST | `/logout` | cookie | - | `null`, cookie expired |

Validation: `name` required (max 255); `email` required, valid, unique; `password` required, min 8, must match `password_confirmation`. Login failure: 401 `"Invalid credentials."`.

### Sports and leagues (public, cached 10 min on the server)

| Method | Path | Query | Success `data` |
|---|---|---|---|
| GET | `/sports` | - | `Sport[]` |
| GET | `/leagues` | `sport` (optional, e.g. `Soccer`) | `League[]` |
| GET | `/leagues/{idLeague}/teams` | - | `Team[]` (`[]` if league unknown) |
| GET | `/leagues/{idLeague}/table` | `season` (optional, e.g. `2024-2025`) | `Standing[]` |

### Teams (public, cached 10 min)

| Method | Path | Success `data` |
|---|---|---|
| GET | `/teams/{idTeam}` | `Team` (404 if not found) |
| GET | `/teams/{idTeam}/previous-matches` | `Match[]` |

### Favorites (cookie required; always scoped to the logged-in user)

| Method | Path | Body | Success `data` |
|---|---|---|---|
| GET | `/favorites` | - | `Favorite[]` (newest first) |
| POST | `/favorites` | `{ team_id, team_name, team_badge? }` | `Favorite` (201); 409 if already added |
| DELETE | `/favorites/{favorite.id}` | - | `null`; 404 if not yours |

Never send a `user_id`; ownership comes from the cookie.

## 5. Page flows

```
Sport picker      GET /sports                        -> pick strSport
League list       GET /leagues?sport={strSport}      -> name + strBadge (logo)
Team list         GET /leagues/{idLeague}/teams
Team detail page  GET /teams/{idTeam}
                  GET /teams/{idTeam}/previous-matches
                  GET /leagues/{Team.idLeague}/table   -> highlight the current team
Add favorite      POST /favorites { team_id: Team.idTeam, team_name: Team.strTeam, team_badge: Team.strBadge }
Favorites page    GET /favorites; remove with DELETE /favorites/{Favorite.id}
Profile page      GET /me
Auth guard        GET /me on app start; 401 -> logged out
```

## 6. Gotchas

- `match_time_wib` is a plain string already in WIB. Show it with a "WIB" label. Do not pass it to `new Date()` (the browser would shift the timezone).
- League/team/standing objects use TheSportsDB's raw keys (`idTeam`, `strTeam`, ...). Match and favorite objects use our snake_case keys.
- `strBadge` on leagues is often `null` on the free tier: use a placeholder.
- Team badges support size suffixes: append `/small` or `/tiny` to the URL.
- Free-tier limits (TheSportsDB): previous matches only include home games; standings only work for featured soccer leagues; results may be limited. The server caches for 10 minutes, so avoid firing requests on every keystroke.
- No pagination: lists come back whole.
- "Remove favorite" uses the favorite's own `id`, not the TheSportsDB `team_id`.
- After changing `FRONTEND_URL` or `.env`, restart the backend; after changing Vite's `.env`, restart Vite.
