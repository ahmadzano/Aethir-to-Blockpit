# Aethir to Blockpit
This script is to prepare airdrop history from Aethir dashboard to Import them to Blockpit.

## Usage
To use the script export Aethir history or fetch it form the API viw curl 
```
curl --location 'https://app.aethir.com/console-api/activity/list?current=1&size={SIZE}' \
--header 'global-token: eyxxx'
```

Response example:
```json
{
    "code": 135000,
    "msg": "OK",
    "data": {
        "records": [
            {
                "key": "123",
                "type": 1,
                "status": 2,
                "licenseId": "123",
                "time": 1736643706,
                "amount": "32.56263604"
            }
        ],
        "total": {TOTAL},
        "size": 1,
        "current": 1,
        "pages": {SIZE}
    },
    "time": "2025-01-12 17:11:35"
}
```

Then add the export into same folder with the `main.php` file then execute the script with passing the year parameter as followings:

```
php main.php year=2024
```

After the scripts ends a new file will be created in same folder with the name Blockpit.csv that can be imported as a manual integration on Blockpit.

Make sure to check teh records afterwards carefully to fix any mistakes and issues. 