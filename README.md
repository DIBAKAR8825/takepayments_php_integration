# TakePayments PHP Integration

This project is a modular, PSR-4 compatible PHP SDK for integrating with the TakePayments gateway. It includes a demo payment form and demonstrates both Hosted and Direct API usage.

---

## 🚀 Features

* Modular PSR-4 autoloading via Composer
* Environment variable configuration via `.env`
* Reads payment data from a JSON file (`public/response.json`)
* Example public pages:

  * `index.php`: Payment form using Hosted API
  * `test_payment.php`: Alternative payment form
  * `info.php`: PHP Info for debugging

---

## 📂 Folder Structure

```
takepayments_php_integration/
├── public/                # Public web root
│   ├── index.php          # Demo payment form (loads data from response.json)
│   ├── test_payment.php   # Alternate payment form
│   ├── info.php           # phpinfo() page
│   ├── response.json      # Example payment data
├── src/                    # Source code
│   └── Domain/PaymentGateway/
│       ├── Service/       # Gateway class
│       └── Support/       # Helper classes
├── vendor/                 # Composer dependencies (ignored in Git)
├── .env                    # Environment variables (ignored in Git)
├── .gitignore
└── composer.json
```

---

## ⚙️ Requirements

* PHP 7.4 or higher
* Composer
* OpenSSL (for hashing)
* cURL enabled in PHP
* XAMPP, MAMP, or any PHP server

---

## 🔧 Installation

### 1. Clone the Repo

```bash
git clone https://github.com/DIBAKAR8825/takepayments_php_integration.git
cd takepayments_php_integration
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure Environment Variables

Copy the example below to a `.env` file in your project root:

```
MERCHANT_ID=119836
MERCHANT_SECRET=9GXwHNVC87VqsqNM
GATEWAY_HOSTED_URL=https://gw1.tponlinepayments.com/paymentform/
GATEWAY_DIRECT_URL=https://gw1.tponlinepayments.com/direct/
DEBUG=true
```

### 4. Prepare Sample Payment Data

Edit `public/response.json` to set up your sample payment values:

```json
{
  "status": "success",
  "code": 200,
  "message": "Payment data retrieved successfully",
  "data": {
    "countryCode": 826,
    "currencyCode": 826,
    "amount": 1001,
    "orderRef": "Test Purchase #1234",
    "transactionUnique": "abc123xyz456"
  },
  "timestamp": "2025-07-06T14:23:55Z"
}
```

---

## ▶️ Running the Project

Run the built-in PHP server from the `public` directory:

```bash
cd public
php -S localhost:8000
```

Then open your browser:

* [http://localhost:8000/index.php](http://localhost:8000/index.php) → Hosted Payment Form (reads from `response.json`)
* [http://localhost:8000/test\_payment.php](http://localhost:8000/test_payment.php) → Manual request
* [http://localhost:8000/info.php](http://localhost:8000/info.php) → PHP Info

---
