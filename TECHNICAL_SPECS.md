# Compliance & Technical Description

This document describes how the Madness GDPR system manages consent in compliance with current regulations (GDPR, ePrivacy, DMA).

## 1. Regulatory Compliance
The system is designed to comply with GDPR (EU Regulation 2016/679) and the ePrivacy Directive (Cookie Law):

- **Prior Consent:** No non-essential cookies are installed before user action.
- **Granularity:** Users can choose which categories to activate (Necessary, Analytics, Marketing).
- **Revocability:** Consent can be changed at any time via the floating button or preferences modal.
- **Transparency:** Direct link to the full policy and clear details in the banner.

## 2. Google Consent Mode v2 (Basic Mode)
The system natively implements Google Consent Mode v2 in **Basic Mode**:

> The tracking script is completely blocked at the source and only loaded *after* consent. This ensures that no data (not even anonymous pings) is sent before acceptance, meeting the most restrictive compliance requirements.

This ensures compliance with the **Digital Markets Act (DMA)** and guarantees that Google receives the correct authorization signals for `ad_storage`, `analytics_storage`, `ad_user_data`, and `ad_personalization` purposes.

## 3. Implementation Details
- **Storage:** Preferences are saved in a local, first-party cookie (`madness_gdpr_consent`) with a configurable duration (default 180 days).
- **Events:** The custom JavaScript event `gdpr-consent-updated` is triggered whenever the consent state changes, allowing other scripts to react.
- **Script Blocking:** The system blocks the execution of tracking scripts (e.g., GA4) until it receives a positive consent signal.

## 4. Proof of Consent Logging
The system generates a unique **Consent ID** for every action and logs it to secure server-side CSV files (`gdpr/logs/`). 
The log includes the Timestamp and the **Masked IP** (Anonymized), as required by law to demonstrate compliance during an audit.

## 5. Generic Script Blocking
To block third-party scripts (e.g., Facebook Pixel, LinkedIn) that do not support Consent Mode, simply modify their tags:

```html
<script type="text/plain" data-category="marketing">
  // Your tracking code here
</script>
```

These scripts will automatically execute only after the user grants consent for the specific category.

## 6. Security & Reliability
- **Admin Authentication**: Passwords for the administrative dashboard are secured using `password_hash()` (Bcrypt) to prevent exposure in case of file leaks.
- **Log Security**: The `logs/` directory is protected via a multi-version compatible `.htaccess` file, denying all direct web access to the CSV audit trails.
- **System Integrity Tool**: A built-in "System Self-Test" allows administrators to verify PHP requirements, directory permissions, and configuration health in real-time.
- **IP Anonymization**: To adhere to the principle of "Data Minimization", the last octet of IPv4 addresses and the first 3 blocks of IPv6 addresses are masked before being recorded in the proof-of-consent logs.

## 7. Licensing
The project is licensed under the **Apache License 2.0**. This selection provides:
- **Liability Protection**: Explicitly limits the author's liability and provides indemnity.
- **Commercial Use**: Permits free use in commercial and private projects.
- **Grant of Patent Rights**: Includes an explicit grant of patent rights from contributors to users.
- **Redistribution**: Allows for modification and redistribution under specific conditions.
