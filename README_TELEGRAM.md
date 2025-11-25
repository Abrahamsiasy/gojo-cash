# Telegram Mini App - Implementation Guide

This document serves as the primary reference for the Telegram Mini App module within the Gojo Finance application. It details the architecture, file structure, and implementation steps.

## 📂 Project Structure & File Reference

Below is the file tree for the Telegram Mini App module.

```text
finance-app/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── Telegram/
│   │           └── TelegramAppController.php  <-- [CORE] Main Controller
│   └── Services/
│       ├── CompanyService.php                 <-- [LOGIC] Reused Business Logic
│       └── AccountService.php                 <-- [LOGIC] Reused Business Logic
├── bootstrap/
│   └── app.php                                <-- [CONFIG] CSRF Exclusions
├── resources/
│   └── views/
│       └── telegram/
│           ├── accounts/
│           │   └── show.blade.php             <-- [VIEW] Account Details
│           ├── companies/
│           │   ├── index.blade.php            <-- [VIEW] Company List
│           │   └── show.blade.php             <-- [VIEW] Company Dashboard
│           ├── layout.blade.php               <-- [VIEW] Master Layout (Tailwind+SDK)
│           └── login.blade.php                <-- [VIEW] Auth Handshake
└── routes/
    └── web.php                                <-- [ROUTE] URL Definitions
```

### File Deep Dive

#### 1. `routes/web.php`
*   **Purpose**: Defines URL endpoints under the `/telegram` prefix.
*   **Key Routes**:
    *   `GET /telegram/login`: The entry point for the Mini App.
    *   `POST /telegram/auth`: The API endpoint that validates Telegram data.
    *   `GET /telegram/companies`: The main dashboard (protected).

#### 2. `app/Http/Controllers/Telegram/TelegramAppController.php`
*   **Purpose**: Handles HTTP requests and orchestrates data fetching.
*   **Key Methods**:
    *   `authenticate()`: Validates `initData` from Telegram and logs the user in.
    *   `companies()`: Uses `CompanyService` to list companies.
    *   `showCompany()`: Uses `CompanyService` to show details + metrics.

#### 3. `resources/views/telegram/login.blade.php`
*   **Purpose**: The invisible handshake page.
*   **Mechanism**: Javascript reads `window.Telegram.WebApp.initData` and POSTs it to `/telegram/auth`. If successful, it redirects to the app.

#### 4. `bootstrap/app.php`
*   **Critical Config**: Excludes `telegram/auth` from CSRF protection to allow the initial POST request from the Telegram Webview.

---

## 1. Architecture Overview

The application uses a **Server-Side Rendering (SSR)** approach with **Laravel Blade**.

*   **Backend**: Laravel Controllers & Services.
*   **Frontend**: Blade Templates + Tailwind CSS + Telegram WebApp SDK.
*   **Authentication**: Custom handshake using Telegram's `initData`.

### Why Blade? (vs React/Vue)
*   **Pros**: Reuses existing Laravel Services (`CompanyService`), no complex JS build tools, no CORS issues, faster development.
*   **Cons**: Page reloads on navigation.
*   **Verdict**: Best for getting a robust MVP up and running quickly using existing PHP logic.

## 2. Prerequisites

1.  **Telegram Bot**: Create via [@BotFather](https://t.me/BotFather).
2.  **HTTPS Tunnel**: Use `ngrok` or `valet share`.
3.  **Telegram App Config**: Link your bot to your HTTPS URL (e.g., `https://xxxx.ngrok.io/telegram/login`).

## 3. Implementation Details

### A. Authentication Flow
1.  User opens Mini App -> hits `/telegram/login`.
2.  JS extracts `initData` -> POSTs to `/telegram/auth`.
3.  Laravel validates data -> Creates Session -> Returns JSON Redirect.
4.  JS redirects browser to `/telegram/companies`.

### B. Services
We strictly reuse existing services to ensure data consistency:
*   **`CompanyService::prepareShowData`**: Fetches company + accounts + metrics.
*   **`AccountService::prepareShowData`**: Fetches account + transactions.

## 4. Common Pitfalls

*   **CSRF 419**: If you see this, check `bootstrap/app.php`.
*   **"User not found"**: The `authenticate` method creates a user if they don't exist.
*   **Dark Mode**: Handled automatically by `layout.blade.php` reading `tg.colorScheme`.

## 5. User Flow & UI Breakdown

This section explains exactly what happens when a user opens the app.

### Step 1: The Entry Point (The "Loading" Screen)
*   **URL**: `/telegram/login`
*   **What User Sees**: A loading spinner (briefly).
*   **Behind the Scenes**: The app grabs your Telegram ID, logs you in, and redirects you.

### Step 2: The "Home" Page (Companies List)
*   **URL**: `/telegram/companies`
*   **What User Sees**: A list of all companies associated with the user.
*   **Sample Data**:
    ```text
    [ Refresh Button ]
    
    1. Gojo Trading PLC
       Status: Active
       Trial: Ends Dec 31, 2025
       
    2. Abraham's Coffee Shop
       Status: Inactive
       Trial: Expired
    ```

### Step 3: Company Dashboard
*   **URL**: `/telegram/companies/{id}`
*   **What User Sees**: High-level metrics and a list of bank accounts.
*   **Sample Data**:
    ```text
    [ < Back ]  Gojo Trading PLC
    
    [ Metrics Card ]
    💰 Total Balance: 1,500,000 ETB
    📈 Total Income:    500,000 ETB
    📉 Total Expense:   200,000 ETB
    
    [ Accounts List ]
    1. Commercial Bank of Ethiopia
       Acct: 1000123456789
       Bal:  1,000,000 ETB
       
    2. Awash Bank
       Acct: 555000111222
       Bal:  500,000 ETB
    ```

### Step 4: Account Details
*   **URL**: `/telegram/accounts/{id}`
*   **What User Sees**: Detailed transaction history for a specific account.
*   **Sample Data**:
    ```text
    [ < Back ]  Commercial Bank of Ethiopia
    
    Current Balance: 1,000,000 ETB
    Type: Checking
    
    [ Recent Transactions ]
    ⬇️ Office Rent
       - 50,000 ETB | Expense | Today, 10:00 AM
       
    ⬆️ Client Payment (ABC Corp)
       + 120,000 ETB | Income | Yesterday, 4:30 PM
       
    ⬇️ Utility Bill
       - 2,500 ETB | Expense | Nov 18, 2025
    ```
