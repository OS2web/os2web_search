//TODO:
1. Can only be installed when EN and DA are present
2. required search_api_attachments
3. change index mode (search index)


# OS2Web Search Drupal module  [![Build Status](https://travis-ci.org/OS2web/os2web_search.svg?branch=master)](https://travis-ci.org/OS2web/os2web_nemlogin)

## Module purpose

The aim of this module is to provide search functionality powered by SOLR.

## How does it work

After enabling search is available on the search page, as well as exposed form is added to 4 regions: **Megamenu - search**, **Header - search**, **Header - below** (front page only), **Content** (search page only).

Search page: ```/s```

## Install

1. Create the SOLR core. [Read manual](https://git.drupalcode.org/project/search_api_solr#setting-up-solr-single-core).

1. Module is available to download via composer.
    ```
    composer require os2web/os2web_search
    drush en os2web_search
    ```

1. After activation finish the set up here: ```admin/config/search/search-api/server/os2web_search_server/edit```

   The following fields need to be manually inserted:
    - Solr host
    - Solr core

## Update
Updating process for OS2Web Nemlogin module is similar to usual Drupal 8 module.
Use Composer's built-in command for listing packages that have updates available:

```
composer outdated os2web/os2web_search
```

## Synonyms activation
In order to use search synonyms for search those actions need to be done:

1. Enable synonyms module:
    ```
    drush en search_api_synonym
    ```
1. Add some synonyms: ```admin/config/search/search-api-synonyms```
1. Make sure your cron is running
1. Copy the script ```os2web_search/scripts/synonyms_deploy.sh.example``` into any directory outside Drupal and execute it each time right after the cron.

   Script requires those variables to be set:
   * ```synonymdir=[path to synonyms]```

        Full path to synonyms export folder location e.g. /var/www/os2web.dk/web/sites/default/files/synonyms/
   * ```solrconfig=[path to SOLR core configuration]```

        Full path to SOLR core config folder e.g. /opt/solr/server/solr/sik/conf/
   * ```[SOLR Core]```

        SOLR Core name

The script will work in the following way:
* It checks the synonyms export location
* If it has some files (matching the pattern), those files are moved to SOLR config location and renamed
* After that the SOLR core is reloaded so that synonyms are active

## Module translation
Module's main language is English but Danish language is fully supported.

When extending this module add your translations to the corresponding **\*.po** files located ```modules/contrib/os2web_search/translations/``` and run the following to update translations:
```
drush locale-check
drush locale-update && drush cr
```

## Automated testing and code quality
See [OS2Web testing and CI information](https://github.com/OS2Web/docs#testing-and-ci)

## Contribution

Project is opened for new features and os course bugfixes.
If you have any suggestion or you found a bug in project, you are very welcome
to create an issue in github repository issue tracker.
For issue description there is expected that you will provide clear and
sufficient information about your feature request or bug report.

### Code review policy
See [OS2Web code review policy](https://github.com/OS2Web/docs#code-review)

### Git name convention
See [OS2Web git name convention](https://github.com/OS2Web/docs#git-guideline)
