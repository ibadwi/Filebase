# Filebase
small project to create dataase engine based on file system, lite and easy

## How it works

## Create table and insert int table
### SQL
```sql
//CREATE TABLE persons (name varchar(255), age int, gender varchar(255));
//Theen
//INSERT INTO persons SET name='iBadwi', age='43', gender='male';
//or
//INSERT INTO persons (name, age, gender) VALUES ('iBadwi','43','male');
```

### Filebase
```php
//Filebase create table if not exist, File base add index autoincrement id if not exist
$results= FilebaseHelper::write('persons',['name'=>'iBadwi','age'=>43,'gender'=>'male']);
```

