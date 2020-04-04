##Laravel package - Parser for warehouses

install
```
sudo apt-get install php7.*-soap 
sudo systemctl restart apache2
```

add into config.app in providers array
``` 
Parser\Postal\ParserServiceProvider::class
```

-----
update the .env file next variables
```
INTIME_API_KEY=''
INTIME_URL=''
DELIVERY_URL=''
DELIVERY_PARAM=''
DB_NAME=''
DELIVERY_TABLE_NAME=''
INTIME_TABLE_NAME=''
FIREBASE_AUTH=''
MOYSKLAD_EMAIL=''
MOYSKLAD_PASS=''
MOYSKLAD_DELIVERY_TABLE_ID=''
MOYSKLAD_INTIME_TABLE_ID=''
```
-----