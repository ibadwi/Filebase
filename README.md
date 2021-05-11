# Filebase
small project to create database engine based on file system, lite and easy

## How it works

## Create table and insert into table
### SQL
```sql
CREATE TABLE persons (name varchar(255), age int, gender varchar(255));
#Then
INSERT INTO persons SET name='iBadwi', age='43', gender='male';
#or
INSERT INTO persons (name, age, gender) VALUES ('iBadwi','43','male');
#or
INSERT INTO persons (id,name, age, gender) VALUES (1,'iBadwi','43','male');
```

### Filebase
```php
//Filebase create table if not exist, File base add index autoincrement id if not exist
$results= FilebaseHelper::write('persons',['name'=>'iBadwi','age'=>43,'gender'=>'male']);
```

## Update record
### SQL
```sql
UPDATE persons SET name='iBadwi', age='43', gender='male' WHERE id=1;
```

### Filebase
```php
$results= FilebaseHelper::write('persons',['name'=>'iBadwi','age'=>43,'gender'=>'male'],1);
//or
$results= FilebaseHelper::write('persons',['name'=>'iBadwi','age'=>43,'gender'=>'male','id'=>1]);
```

## Delete record
### SQL
```sql
DELETE FROM persons WHERE id=1;
```

### Filebase
```php
//Filebase way, default soft delete = true
$results= FilebaseHelper::delete('persons',1);
```

## Get all records
### SQL
```sql
SELECT * FROM persons;
#or
SELECT * FROM persons where id=1;
```

### Filebase
```php
//Filebase way, default all records
$results= FilebaseHelper::read('persons');
//or
$results= FilebaseHelper::read('persons',1);
```

## Select records with operators
### SQL
```sql
SELECT * FROM persons where name='iBadwi';
#or
SELECT * FROM persons where age>42;
```

### Filebase
```php
//Filebase way, default operator is =, for multiple conditions you can pass range
$results= FilebaseHelper::select('persons','name','iBadwi');
//or
$results= FilebaseHelper::select('persons','age',42,'>');
```

## Select records with multiple conditions
### SQL
```sql
SELECT * FROM persons where name='iBadwi' and age>43;
```

### Filebase
```php
//Filebase way, default operator is =, for multiple conditions you can pass range
$range= FilebaseHelper::select('persons','name','iBadwi');
$results= FilebaseHelper::select('persons','age',42,'>',$range);
```

## SELECT All tables name
### SQL
```sql
SELECT table_name FROM information_schema.tables;;
```

### Filebase
```php
$results= FilebaseHelper::tables();
```

## Convert Database Table to Filebase table

### Filebase
```php
//Default using application default database connection
$results= FilebaseHelper::tableToFile('persons');
```
