# Magento 2 Open Graph Extension by Magefan

This Magento 2 module allows you to configure OG Tags for Magento Products, Categories, CMS Pages, Blog Categories.


## Installation Method 1 - Installing via Composer (prefer)
  * Open command line
  * Using command "cd" navigate to your magento2 root directory
  * Run commands: 
```
  composer config repositories.magefan composer https://magefan.com/repo/
  composer require magefan/module-og-tags
  #Authentication Data can be found in your [Magefan Account](https://magefan.com/downloadable/customer/products/)
  php bin/magento setup:upgrade
  php bin/magento setup:di:compile
  php bin/magento setup:static-content:deploy
```


## Installation Method 2
  * Install Magefan Community Extension first (https://github.com/magefan/module-community)
  * Unzip Magefan Open Graph Extension Archive
  * In your Magento 2 root directory create a folder app/code/Magefan/OgTags
  * Copy files and folders from archive to that folder
  * In command line, using "cd", navigate to your Magento 2 root directory
  * Run commands:
```
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
```

## Support
If you have any issues, please [contact us](mailto:support@magefan.com)

## Need More Features?
Please contact us to get a quote
https://magefan.com/contact

## License
The code is licensed under [EULA](https://magefan.com/end-user-license-agreement).
