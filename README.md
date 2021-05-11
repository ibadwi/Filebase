# Filebase
small project to create dataase engine based on file system, lite and easy

## How it works

```php
//###############################################
//Create table and insert int table
//###############################################
//SQL way
//CREATE TABLE persons (name varchar(255), age int, gender varchar(255));
//Theen
//INSERT INTO persons SET name='iBadwi', age='43', gender='male';
//or
//INSERT INTO persons (name, age, gender) VALUES ('iBadwi','43','male');

//Filebase way
//Filebase create table if not exist, File base add index autoincrement id if not exist
$results= FilebaseHelper::write('persons',['name'=>'iBadwi','age'=>43,'gender'=>'male']);

//###############################################
//Update record
//###############################################
//SQL way
//UPDATE persons SET name='Badwi', age='43', gender='male' WHERE id=1;

//Filebase way
$results= FilebaseHelper::write('persons',['name'=>'iBadwi','age'=>43,'gender'=>'male'],1);
//or
$results= FilebaseHelper::write('persons',['name'=>'iBadwi','age'=>43,'gender'=>'male','id'=>1]);

//###############################################
//Delete record
//###############################################
//SQL way
//DELETE FROM persons WHERE id=1;

//Filebase way, default soft delete = true
$results= FilebaseHelper::delete('persons',1);

//###############################################
//Get all records
//###############################################
//SQL way
//SELECT * FROM persons;
//or
//SELECT * FROM persons where id=1;

//Filebase way, default all records, default a
$results= FilebaseHelper::read('persons');
//or
$results= FilebaseHelper::read('persons',1);

//###############################################
//Select records with operators
//###############################################
//SQL way
//SELECT * FROM persons where name='iBadwi';
//or
//SELECT * FROM persons where age>42;

//Filebase way, default operator is =, for multiple conditions you can pass range
$results= FilebaseHelper::select('persons','name','iBadwi');
//or
$results= FilebaseHelper::select('persons','age',42,'>');

//###############################################
//Select records with multiple conditions
//###############################################
//SQL way
//SELECT * FROM persons where name='iBadwi' and age>43;

//Filebase way, default operator is =, for multiple conditions you can pass range
$range= FilebaseHelper::select('persons','name','iBadwi');
$results= FilebaseHelper::select('persons','age',42,'>',$range);

//###############################################
//SELECT All tables name
//###############################################
//SQL way
//SELECT table_name FROM information_schema.tables;;

//Filebase way, default all records, default a
$results= FilebaseHelper::tables();

//###############################################
//Convert Database Table to Filebase table
//###############################################
//Filebase way, using default database connection
$results= FilebaseHelper::tableToFile('persons');
```

