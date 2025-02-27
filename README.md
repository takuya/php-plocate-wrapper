# php-plocate-wrapper
php wrapper for plocate and plocate-build command for search files


## find files by plocate db

search files using locate.db(plocate database)
```php
<?php
$locate = new LocateWrap();
$ret = $locate->search('composer.json');
foreach ($ret as $item) {
  var_dump($item);
}
```

## find files from sub-directory ( not from / )

We can find files from only sub-directory by shell.
```shell
## build file database only ~/Documents
subdir=/home/takuya/Documemts
cd $subdir 
find . -type f -printf '%P\n' > find-results.txt
plocate-build -p find-results.txt my-document.db
## find from using custom database
plocate -d my-document.db .docx
```
This project is aimed to build that database. sample code is below.
```php
<?php
$builder = new LocateDbBuilder('~/.Document.db','~/Document');
$builder->build();

$locate = new LocateWrap('~/.Document.db');
$found = $locate->search('.docx');
```

## Installing 

from GitHub. 
```shell
name='php-plocate-wrapper'
composer config repositories.$name \
vcs https://github.com/takuya/$name  
composer require takuya/$name:master
composer install
```

