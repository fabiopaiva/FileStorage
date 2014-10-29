# FileStorage


## Install

### Composer

    php composer.phar require "fabiopaiva/file-storage": "dev-master"
### GIT

    cd vendor
    git clone https://github.com/fabiopaiva/FileStorage

## Enable Module

    application.config.php
    <?php
        return array(
            'modules' => array(
            'DoctrineModule',
            'DoctrineORMModule',
            'FileStorage', 
            'Application',
    ),
    ...
    ?>

## Create table in database

    vendor/bin/doctrine-module orm:schema-tool:update --dump-sql
    # copy the generated sql and execute in your database for productions environment
    # or force to execute into your database directly
    vendor/bin/doctrine-module orm:schema-tool:update --force

## Create a writable folder

    mkdir public/fileStorage
    chmod 777 public/fileStorage

You can set another folder in your form construction

### Protect this folder

It's seriously recommended to protect this folder.
If you are using Apache, create a file into this folder called .htaccess with the content:

    php_flag engine off
    Options -indexes

## Usage

In your entity:

    /**
     * @ORM\ManyToOne(targetEntity="\FileStorage\Entity\Document", cascade={"persist", "remove"})
     * @var \FileStorage\Entity\Document
     */
    protected $myFile;

## Helpers

use the route file-storage to manage files
            
    <?php echo $this->url('file-storage');?>

use the method downloadLink to get the filepath

    <?php echo $document->getDownloadLink();?>
