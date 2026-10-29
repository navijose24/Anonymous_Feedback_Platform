<<<<<<< HEAD
# 💬 Anonymous Feedback Platform

> "Say what you really think. Without revealing who you are."

A full-stack web application for receiving completely anonymous feedback through a personal unique link.  
Built with **PHP · MySQL · Apache · XAMPP** as a college web-security project.

---

## ✨ Features

| Feature | Status |
|---|---|
| User registration & login | ✅ |
| Unique `/f/username` feedback links | ✅ |
| Anonymous submission (no account needed) | ✅ |
| Category selection (Appreciation / Criticism / Suggestion / Improvement) | ✅ |
| Private inbox with read / unread / saved / delete | ✅ |
| Dashboard stats + category breakdown | ✅ |
| CSRF protection on all forms | ✅ |
| Prepared statements (PDO) throughout | ✅ |
| Session-based rate limiting | ✅ |

---

## 🛠 Tech Stack

- **Frontend**: HTML5 · CSS3 (custom design system) · Vanilla JS
- **Backend**: Core PHP 8.x
- **Database**: MySQL 8 via PDO
- **Web Server**: Apache (XAMPP)
- **Local Dev**: XAMPP on Windows

---

## 🚀 Local Setup

### 1. Prerequisites
- [XAMPP](https://www.apachefriends.org/) installed at `C:\xampp`

### 2. Serve the project
Create a symlink from XAMPP's htdocs to your repo (run PowerShell as Administrator):
```powershell
New-Item -ItemType SymbolicLink `
  -Path "C:\xampp\htdocs\anon" `
  -Target "D:\git\Network Eng\s1\web security\Anonymous_Feedback_Platform"
```
Then access the app at: **http://localhost/anon/**

### 3. Database
1. Start MySQL in XAMPP Control Panel
2. Open **phpMyAdmin** → http://localhost/phpmyadmin/
3. Create a database named `anon_feedback`
4. Import `database/schema.sql`

### 4. Config
```bash
# Copy the example and fill in your credentials
cp config/database.example.php config/database.php
```
Edit `config/database.php`:
```php
define('DB_USER', 'root');
define('DB_PASS', '');      // blank by default in XAMPP
define('DB_NAME', 'anon_feedback');
```

### 5. Enable mod_rewrite (for clean /f/username URLs)
Edit `C:\xampp\apache\conf\httpd.conf`:
- Find `AllowOverride None` under your htdocs block and change to `AllowOverride All`
- Uncomment `LoadModule rewrite_module modules/mod_rewrite.so`
- Restart Apache

---

## 📁 Project Structure

```
anonymous-feedback/
├── index.php           Landing page
├── register.php        User registration
├── login.php           Authentication
├── logout.php          Session destroy
├── f.php               Public feedback page (/f?u=username)
├── dashboard.php       Private stats + overview
├── inbox.php           Feedback inbox (filters: all/unread/saved/category)
├── update-feedback.php Action handler (read/save/delete)
├── profile.php         Profile & link management
├── .htaccess           Apache: clean URLs + security headers
├── config/
│   ├── database.php         ← NOT committed (add your creds here)
│   └── database.example.php ← Committed safe template
├── includes/
│   ├── auth.php        Session bootstrap
│   ├── functions.php   CSRF, validation, helpers
│   ├── header.php      Shared HTML head + navbar
│   └── footer.php      Shared footer + JS
├── assets/
│   ├── css/style.css   Full design system (dark navy + indigo)
│   └── js/app.js       Clipboard copy, char counter, UI helpers
└── database/
    └── schema.sql      MySQL DDL — run once to initialize
```

---

## 🔐 Security Implementation

- `password_hash()` / `password_verify()` — bcrypt password storage
- PDO prepared statements — all user input parameterized
- CSRF tokens — every state-changing form is protected
- Session hardening — `httponly`, `strict_mode`, `samesite=Lax`
- Ownership checks — users can only modify their own feedback (prevents IDOR)
- Generic error messages — prevents user enumeration on login
- Output escaping — `htmlspecialchars()` on all displayed data
- Rate limiting — session-based submission throttle
- `.htaccess` — blocks directory listing and direct access to `.sql`/`.env` files

---

## ⚠️ Anonymity Disclaimer

This application does **not** store sender identity (name, email, IP) in the database.  
However, your hosting provider's web server (Apache) and infrastructure may retain technical access logs (IP addresses, timestamps). True network-level anonymity is not guaranteed.

---

## 📄 License

MIT — free to use for academic and personal projects.
..
=======

>>>>>>> 0a6202aad2ee5dc11e414bbf88c20d7abaf07751
