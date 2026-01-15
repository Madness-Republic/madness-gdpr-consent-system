# Madness GDPR Consent System
![Version](https://img.shields.io/badge/version-1.3.2-orange.svg)
![Last Update](https://img.shields.io/github/last-commit/Madness-Republic/madness-gdpr-consent-system)

Lightweight, dependency-free, and modular GDPR Cookie Consent system with multi-language support, Admin Panel, and Proof of Consent.

> [!IMPORTANT]
> **LEGAL DISCLAIMER**: Use of this module does not automatically guarantee GDPR compliance. Compliance depends on the entire site configuration, privacy policy, and data handling practices. The author assumes NO responsibility for fines, damages, or disputes arising from the use of this software. Always consult with a legal expert.

## Features
- **Strict Compliance (Basic Mode)**: Blocks Google Analytics and third-party scripts totally until explicit consent is given (satisfies Cookiebot/Iubenda scanners).
- **Proof of Consent Logging**: Server-side CSV logging of consent actions (Timestamp, Masked IP, Consent ID) for legal compliance.
- **Auto-Installation**: One-click banner injection for non-technical users.
- **Equal Prominence UI**: Compliant design with identical "Accept" and "Reject" buttons to avoid "dark patterns".
- **Google Consent Mode v2**: Full support for both Basic and Advanced GCM v2 signals.
- **Generic Script Blocking**: Easily block any 3rd party script (Pixel, LinkedIn, etc.) using `type="text/plain"`.
- **Dynamic Language Support**: Multi-language support out of the box (IT, EN, ES, etc.).
- **Admin Panel**: Full control over settings, styles, and policy content without touching code.
- **System Self-Test**: Built-in tool to verify server environment, folder permissions, and security settings with one-click "Auto-Fix" for missing protections.
- **Security Hardening**: Admin passwords are encrypted using Bcrypt (password_hash), and security warnings alert admins if they are using default credentials.
- **Apache License 2.0**: Open-source license that provides stronger legal protection and explicit indemnity for the author.
- **Universal Integration**: Automatically handles paths to work from root or subdirectories (e.g., `/pixelwall/`). Supports custom Privacy Policy paths via configuration.

📄 **[Read Technical & Compliance Specs](TECHNICAL_SPECS.md)**


## Installation

1. Create a folder named `gdpr` in your website's root directory and upload all the repository files into it.
   *Note: The system is designed to run from within the `/gdpr/` directory.*
2. Ensure `gdpr/logs/` has write permissions (chmod 755/775).
3. Access the Admin Panel at yourdomain.com/gdpr/dashboard/index.php (Default password: `password`).
    *   *Security Note: Change the password immediately in settings.*
4. Configure your settings (Company info, GA4 ID, Enabled Languages).
5. **Integrate the Banner**:
   *   **Option A (Automatic)**: In the Admin Dashboard, look for the "Auto Installation" card. Click **Scan Site**, choose the file where you want to inject the banner (usually `footer.php`), and click **Install**.
   *   **Option B (Manual)**: Manually include the banner in your main layout file (e.g., `footer.php` or `index.php`) right before the closing `</body>` tag:
       ```php
       <?php include_once 'gdpr/banner.php'; ?>
       ```
   ```
6. **Run System Check**: Login to the dashboard, go to the top bar, and click **🔍 System Check**. Fix any reported errors (red marks) to ensure logs are protected and writable.

### WordPress Integration
The module works perfectly with WordPress. Since it is not a plugin, you need to install it manually:

1.  Upload the `gdpr` folder to your site root (where `wp-config.php` is located).
2.  Edit your theme's `footer.php` file (preferably in a Child Theme).
3.  Add this code before the closing `</body>` tag:
    ```php
    <?php
    if (file_exists(ABSPATH . 'gdpr/banner.php')) {
        include_once ABSPATH . 'gdpr/banner.php';
    }
    ?>
    ```
4.  **Note**: Do NOT use the Auto-Installer on WordPress, as theme updates might overwrite the changes. Always edit the Child Theme.

## Usage

### Third-Party Script Blocking
For scripts that don't support Consent Mode (like Facebook Pixel), modify the tag:
```html
<script type="text/plain" data-category="marketing">
  // Your tracking code
</script>
```

### Admin Panel
The admin panel (`gdpr/dashboard/index.php`) allows you to:
- Manage **Company Data** (automatically injected into policies).
- Toggle **Google Analytics 4** integration.
- Configure **Custom Privacy Policy URL** for dynamic linking.
- Enable/Disable supported languages.
- Customize **Banner Texts** for each language.
- Edit **Privacy & Cookie Policy** templates.
- Adjust **Styles & Colors** with live preview and **Equal Prominence** enforcement.
   
### How to Add a New Language
1.  Navigate to `gdpr/languages/` and duplicate an existing JSON file (e.g., `en.json`).
2.  Rename the new file with the desired language code (e.g., `fr.json`).
3.  Edit the file and translate the content.
4.  The system will automatically detect the new language. Go to the **Admin Dashboard** and select it in the "Enabled Languages" section.

### Proof of Consent
Consent logs are stored daily in `gdpr/logs/` as CSV files. These logs are protected by an `.htaccess` file and contain anonymized data to prove compliance during audits.

### Policy Templates Warning
> [!WARNING]
> The HTML templates provided in the `gdpr/content/` folder (Privacy & Cookie Policy) are **generic examples** only. You **must** review, edit, and validate them with your legal counsel to ensure they accurately reflect your specific data treatments and local regulations.

## CSS Customization
The system uses CSS variables. You can override them in your main CSS:
```css
:root {
    --gdpr-primary: #f09100;
    --gdpr-btn-accept-1: #f09100;
    --gdpr-btn-accept-2: #ff4d4d;
}
```

## Update Log

### v1.3.2 (Latest) - Reliability & Security Hardening
- **System Self-Test Tool**: Added `check_system.php` dashboard utility to verify PHP version, folder permissions, and security.
- **Security Fixes**: Implemented Bcrypt password hashing for the admin dashboard.
- **One-Click Repair**: Added "Auto-Fix" functionality to the system check for missing `.htaccess` files.
- **Apache License 2.0**: Migrated to Apache License 2.0 for better legal clarity and liability protection.
- **Full Localization**: Localized the entire admin toolset (System Check, Security Warnings, and Manuals) into IT, EN, and ES.

### v1.3.1 - Community & Donations
- **Donation Block**: Added a support/donation block in the Dashboard.
- **Dynamic Documentation**: `install_guide.php` and `technical_compliance.php` now show dynamic versioning.
- **Branding Freedom**: Refactored branding logic to be internal.

### v1.3.0 - Auto-Installation
- **One-Click Installer**: Added an auto-install card in the Dashboard.
- **Smart Restore**: Ability to restore original files from backup.

### v1.2.1 - Transparency & Hardening
- **Dynamic Privacy Linking**: Added support for `{{privacy_url}}` placeholder in translation files and a configurable privacy URL in the Admin.
- **Prior Consent Hardening**: Ensured zero-tracking on landing pages; GCM and dataLayer are initialized only after explicit consent.
- **Rich Text Banner**: Modified `consent_manager.js` to support HTML in banner title and description for policy links.
- **Transparency**: Added direct links to the Privacy Policy within the main banner and the Preferences Modal.
- **Optimization**: Reduced non-essential storage writes to minimize the digital footprint.

### v1.2.1 - Compliance Pack
- **Total Script Blocking**: Switched to "Basic Consent Mode" for GA4. Tracking scripts are now injected only POST-consent.
- **Proof of Consent**: Implemented server-side logging of consent actions with IP anonymization.
- **Equal Prominence**: Updated UI to ensure Accept and Reject buttons have identical visual weight.
- **Path Auto-Detection**: Rewrote `banner.php` to handle universal paths, allowing the banner to work from any subdirectory.
- **Security**: Added `.htaccess` log protection and server-side session checks for all admin tools.
- **Generic Blocker**: Added a dynamic script loader for non-GCM scripts.

### v1.1.0
- **Generic Identity**: Removed all project-specific references.
- **Technical Documentation**: Added `technical_compliance.php`.
- **Admin UI Overhaul**: Improved style section and live preview.
- **Installation Guide**: Added internal `install_guide.php`.

### v1.0.0
- Initial release.
- Core cookie consent logic & GCM v2 support.
- JSON-based multi-language support.
