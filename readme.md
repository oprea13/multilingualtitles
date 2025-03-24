# Multilingual Forum Titles and Descriptions (phpBB Extension)

This phpBB 3.3.x extension allows you to define forum names and descriptions per language, based on the user’s selected interface language.

---

## 📦 Features

- Adds a new settings block when creating or editing a forum
- Enables per-language title and description for each forum
- Automatically shows the translated forum title/description on the index and viewforum pages
- Fully integrated into the phpBB event system (no core file edits)

---

## 🧩 Installation

1. Download or clone the repository into:
   ```
   ext/iws/multilingualtitles
   ```
2. In the Administration Control Panel (ACP), go to:
   **Customize → Manage extensions**
3. Click **Enable** next to *Multilingual Forum Titles and Descriptions*

---

## 🗂 Database

This extension creates one table:

```
{prefix}forum_translations
```

| Column      | Type         | Description                         |
|-------------|--------------|-------------------------------------|
| forum_id    | UINT         | ID of the forum                     |
| lang_iso    | VCHAR(10)    | Language code (e.g., en, ro)       |
| forum_name  | VCHAR_UNI    | Translated forum name              |
| forum_desc  | TEXT_UNI     | Translated forum description       |

Primary key: `(forum_id, lang_iso)`

---

## 🌐 Language Support

Includes:
- English (`en`)
- Romanian (`ro`)

You can translate and add more in:
```
language/[lang]/info_acp_multilingualtitles.php
```

---

## 👤 Author

**Oprea Cristian**  
💻 [https://itandwebsolutions.ro](https://itandwebsolutions.ro)

---

## 📜 License

GPL-2.0 – Free to use and modify.

---

## ✅ Tested on
- phpBB 3.3.12
- PHP 8.1+
- Works with multiple language packs

---

## 📬 Contributions & Issues

Feel free to open pull requests or report issues on the GitHub page.

> This extension was inspired by older MODs and rethought for modern phpBB usage, with clean structure and ACP/UX integration.

---

## 🚀 To Do (Optional Enhancements)
- Inline translation preview on frontend (dynamic switch)
- Integration with sitemap or SEO modules
- ACP search for untranslated fields
