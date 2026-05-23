# Facebook 2013 — Scraped Archive

Archived from the Wayback Machine (archive.org), captured **June 1, 2013**.

---

## Pages Scraped

| Page | Wayback URL | Saved As |
|------|------------|----------|
| Home / Landing | `https://www.facebook.com/` | `html/home.html` |
| Login | `https://www.facebook.com/login.php` | `html/login.html` |
| Sign Up | `https://www.facebook.com/r.php` | `html/signup.html` |

---

## Directory Structure

```
facebook-2013/
├── html/
│   ├── home.html       — Landing page (login bar top + signup form right column)
│   ├── login.html      — Standalone login page (interstitial style)
│   └── signup.html     — Standalone registration page
├── css/
│   ├── home-base.css   — Main global stylesheet (~268 KB, includes all components)
│   ├── home-main.css   — Home page specific styles (~30 KB)
│   ├── home-layout.css — Layout helpers
│   ├── home-components.css — UI component styles
│   ├── home-extra.css  — Additional styles
│   ├── home-extra2.css — Additional styles 2
│   └── signup.css      — Registration page styles
├── images/
│   ├── favicon.ico             — Facebook favicon
│   ├── fb_icon_325x325.png     — Facebook icon (og:image)
│   ├── fb-logo-blue.png        — Facebook logo (blue variant)
│   ├── fb-logo-white.png       — Facebook logo (white, used in blue header bar)
│   ├── sprite-main.png         — Main UI sprite sheet
│   ├── sprite-icons.png        — Icons sprite sheet
│   ├── sprite-header.png       — Header sprite
│   ├── sprite2.png             — Secondary sprite
│   ├── sprite3.png             — Tertiary sprite
│   ├── btn-blue.png            — Blue button background
│   ├── btn-gray.png            — Gray button background
│   ├── spacer.gif              — Transparent spacer GIF
│   ├── loading.gif             — Loading spinner
│   ├── feature-newsfeed.png    — "See photos and updates" feature icon
│   ├── feature-timeline.png    — "Share what's new" feature icon
│   └── feature-graphsearch.png — "Find more with Graph Search" feature icon
└── README.md
```

---

## Page Structure Notes

### Home Page (`home.html`)
- **Blue top navigation bar** — Facebook logo (white) on left, login form (email, password, Log In button, Keep me logged in, Forgot password?) on right
- **Hero section** (`edf0f5` background, 980px wide):
  - Left column: headline + 3 feature rows (News Feed, Timeline, Graph Search) with icons
  - Right column: "Sign Up" form with First Name, Last Name, Email, Re-enter Email, Password, Birthday (Month/Day/Year selects), Gender (radio buttons), Sign Up CTA button, and terms text
- **Footer**: language selector grid + navigation links (Mobile, Find Friends, Badges, People, Pages, Places, Apps, Games, Music, About, Advertise, Create a Page, Developers, Careers, Privacy, Cookies, Terms, Help)
- **Body class**: `fbIndex UIPage_LoggedOut Locale_en_US`

### Login Page (`login.html`)
- **Interstitial box** style (`uiInterstitialLarge uiBoxWhite`)
- Header: "Log In to Facebook"
- Fields: Email/phone, Password, Keep me logged in checkbox
- Actions: Log In button, "or Register for Facebook" link, "Forgot your password?" link
- Language selector at bottom
- **Body class**: `login_page fbx UIPage_LoggedOut ie7 Locale_nl_NL` (Dutch locale for this capture)

### Sign Up Page (`signup.html`)
- **Full-width registration form**
- Header: Blue bar with logo + login form on right (same as home)
- Left panel: "Sign Up — It's free and always will be."
- Form fields:
  - First Name + Last Name (side by side)
  - Your Email
  - Re-enter Email
  - New Password
  - Birthday (Month / Day / Year dropdowns)
  - I am: Female / Male radio buttons
  - "Sign Up" green CTA button
  - Terms text: "By clicking Sign Up, you agree to our Terms..."
- **Body class**: `registration UIPage_LoggedOut Locale_en_US`

---

## Key CSS Classes & Design Tokens

### Colors
- `#3b5998` — Facebook Blue (navbar, buttons, logo)
- `#edf0f5` — Page background (light blue-gray)
- `#ffffff` — White (content areas, cards)
- `#6d84b4` — Secondary blue (links, secondary elements)
- `#4e69a2` — Darker blue (hover states)
- `#dfe3ee` — Border color (input borders, dividers)
- `#5f7ab8` — Medium blue
- `#dd3b2a` — Error red
- `#3aaf42` — Sign Up green button

### Typography
- **Font family**: `Helvetica, Arial, sans-serif`
- **Base font size**: 11px (11px was Facebook's standard body text in 2013)
- **Heading sizes**: 28px (hero headline), 36px (Sign Up heading), 14px (form labels)

### Layout
- **Max content width**: 980px (centered)
- **Blue bar height**: ~30px
- **Hero section background**: `#edf0f5`
- **White card/box**: `.uiBoxWhite` with border `1px solid #ccc` and box-shadow

### Key Components
- `.uiButton.uiButtonConfirm` — Blue primary button (Log In, Sign Up)
- `.inputtext` — Form text inputs (border `1px solid #ccc`, 4px border-radius)
- `.loggedout_menubar` — Top nav bar when logged out
- `.fb_logo` — Facebook logo element (CSS sprite)
- `.uiInterstitialLarge` — Large centered content box (login page)
- `.registration_redesign` — Registration form wrapper

---

## Wayback Machine Source URLs

- Home: `https://web.archive.org/web/20130601120447/http://www.facebook.com/`
- Login: `https://web.archive.org/web/20130601141023/http://www.facebook.com/login.php`
- Signup: `https://web.archive.org/web/20130601162155/http://www.facebook.com/r.php`
- CSS base: `https://web.archive.org/web/20130601121251/https://fbstatic-a.akamaihd.net/rsrc.php/v2/yG/r/KtbiJ5RDVed.css`

All resources captured on **June 1, 2013**.
