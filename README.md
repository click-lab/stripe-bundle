# clickeat stripe bundle
---
## Required tools to run this bundle
- PHP (v. 5.5 - 5.7)
- Apache 2
- MySQL
- Composer
- Git

## how to install :
1.
```git clone git@github.com:click-lab/stripe-bundle.git```

2.
Go to the root directory of the project and install all dependencies with :
```composer install```

##### Note : If you don't have composer please read this documentation : [Composer doc](https://getcomposer.org/doc/00-intro.md)

3.
Setup your config.yml file 

4.
Go back to the root folder of the project and populate the database :

```php app/console doctrine:schema:update --force```

##### Note : If you have a SQL dump, it's now the time to import it

### You are ready to go !