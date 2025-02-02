# Certbot DNS Plugin for Beget

This is a custom Certbot plugin that allows you to use the Beget DNS API for automatic wildcard certificate issuance and renewal.

## 📌 Features
- Automates DNS challenge (`DNS-01`) for wildcard certificates.
- Uses Beget's API to add and remove TXT records.
- Compatible with **Ubuntu 22.04** and **Certbot**.
- Can be used for **Let's Encrypt** wildcard SSL certificates.

---

## 🚀 Installation

### 1️⃣ Install Certbot and Required Packages
Before setting up the plugin, ensure you have Certbot installed:
```sh
sudo apt update
sudo apt install python3-pip certbot
```
Check Certbot version:
```sh
certbot --version
```

### 2️⃣ Create Plugin Directory
```sh
mkdir -p /etc/letsencrypt/dns-plugins/certbot-dns-beget
cd /etc/letsencrypt/dns-plugins/certbot-dns-beget
```

### 3️⃣ Create Plugin Files
Create the following files inside `/etc/letsencrypt/dns-plugins/certbot-dns-beget/`:

#### `setup.py`
```python
from setuptools import setup

setup(
    name="certbot-dns-beget",
    version="0.1",
    author="Your Name",
    author_email="your@email.com",
    description="Certbot plugin for Beget DNS API",
    license="Apache License 2.0",
    packages=["certbot_dns_beget"],
    install_requires=["certbot", "requests"],
    entry_points={
        "certbot.plugins": [
            "dns-beget = certbot_dns_beget:Authenticator",
        ],
    },
)
```

#### `certbot_dns_beget.py`
```python
import requests
import time
import logging
from certbot.plugins.dns_common import DNSAuthenticator

logger = logging.getLogger(__name__)

class Authenticator(DNSAuthenticator):
    """DNS Authenticator for Beget"""

    description = "Obtain a certificate using a DNS TXT record via Beget API"

    def __init__(self, config, name):
        super().__init__(config, name)
        self.api_user = None
        self.api_password = None

    def more_info(self):
        return "This plugin configures a DNS TXT record to respond to a DNS-01 challenge using Beget API"

    def _setup_credentials(self):
        self.api_user = self.conf("api-user")
        self.api_password = self.conf("api-password")

    def _perform(self, domain, validation_name, validation):
        logger.info(f"Adding TXT record for {domain}: {validation_name} = {validation}")
        url = "https://api.beget.com/api/dns/addTxt"
        payload = {
            "login": self.api_user,
            "passwd": self.api_password,
            "input_format": "json",
            "output_format": "json",
            "fqdn": validation_name,
            "text": validation,
            "ttl": 60
        }
        response = requests.post(url, json=payload)
        response_data = response.json()
        if response_data.get("status") != "success":
            raise Exception(f"Failed to add TXT record: {response_data}")
        logger.info("Waiting for DNS propagation...")
        time.sleep(20)

    def _cleanup(self, domain, validation_name, validation):
        logger.info(f"Removing TXT record for {domain}: {validation_name}")
        url = "https://api.beget.com/api/dns/delTxt"
        payload = {
            "login": self.api_user,
            "passwd": self.api_password,
            "input_format": "json",
            "output_format": "json",
            "fqdn": validation_name
        }
        response = requests.post(url, json=payload)
        response_data = response.json()
        if response_data.get("status") != "success":
            logger.warning(f"Failed to remove TXT record: {response_data}")
```

### 4️⃣ Install the Plugin
```sh
pip install .
```
Verify that the plugin is installed:
```sh
certbot plugins
```
You should see `dns-beget` in the output.

---

## 🌍 Usage

### 1️⃣ Create API Credentials
Save your Beget API credentials in a secure file:
```sh
echo "api-user = your_beget_login" > ~/.beget.ini
echo "api-password = your_beget_password" >> ~/.beget.ini
chmod 600 ~/.beget.ini
```

### 2️⃣ Issue a Wildcard Certificate
Run Certbot with the new DNS plugin:
```sh
sudo certbot certonly \
    --dns-beget \
    --dns-beget-credentials ~/.beget.ini \
    -d "example.com" -d "*.example.com" \
    --non-interactive --agree-tos --email your@email.com
```

After successful execution, Certbot will generate SSL certificates at:
```
/etc/letsencrypt/live/example.com/fullchain.pem
/etc/letsencrypt/live/example.com/privkey.pem
```

---

## 🔄 Automatic Renewal

To renew the certificate automatically, add a cron job:
```sh
sudo crontab -e
```
Add the following line:
```sh
0 3 * * * certbot renew --quiet --post-hook "systemctl reload nginx"
```
This will check for renewal **daily at 3 AM** and reload Nginx if renewal succeeds.

---

## ✅ Done!
Now your Certbot can automatically issue and renew wildcard certificates using Beget API! 🎉

If you encounter issues, check logs:
```sh
sudo journalctl -u certbot --no-pager | tail -n 50
```

Happy securing your domains! 🔐
